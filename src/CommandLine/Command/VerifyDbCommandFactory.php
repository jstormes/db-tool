<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use JStormes\dbTool\Adapter\AdapterFactory;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Exception;

class VerifyDbCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);

        $config = $container->get('config');
        $databaseUrl = $config['doctrine']['connection']['orm_default']['params']['url'];
        if (empty($databaseUrl)) {
            $logger->critical('Config option [\'doctrine\'][\'connection\'][\'orm_default\'][\'params\'][\'url\'] is empty.');
            throw new Exception('Config option [\'doctrine\'][\'connection\'][\'orm_default\'][\'params\'][\'url\'] is empty.');
        }

        $databaseAdapterFactory = $container->get(AdapterFactory::class);

        return new VerifyDbCommand($logger, $databaseUrl, $databaseAdapterFactory);
    }

}