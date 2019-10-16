<?php

declare(strict_types=1);

namespace JStormes\dbTool\Adapter;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use dbType\mysqlDb;
use dbType\mariaDb;
use JStormes\dbTool\Lib\parseDatabaseURL;


class AdapterFactory
{

    public function __invoke(ContainerInterface $container)
    {
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);

        $config = $container->get('config');
        $databaseUrl = $config['doctrine']['connection']['orm_default']['params']['url'];

        $urlParser = new parseDatabaseURL();

        $scheme = $urlParser->getDbScheme($databaseUrl);

        switch ($scheme) {
            case "mysql":
                $adapter = new \JStormes\dbTool\Adapter\dbType\mysqlDb();
                break;
            case "maria":
                $adapter = new mariaDb();
                break;
            default:
                $logger->critical("Adapter $scheme not found.");
                throw new DatabaseException("Adapter $scheme not found.");
        }

        return $adapter;

    }

}