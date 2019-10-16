#!/usr/bin/env bash

############################################################################
# set host.docker.internal if it is not set
############################################################################
if ! ping -c 1 -W 1 "host.docker.internal"; then
  echo "Adding host host.docker.internal"
  ip -4 route list match 0/0 | awk '{print $3 " host.docker.internal"}' >> /etc/hosts
  ping -c 1 -W 1 "host.docker.internal"
fi

cd /var/www
php -S 0.0.0.0:80 index.php