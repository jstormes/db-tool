<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use JStormes\dbTool\Adapter\AdapterFactory;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Exception;

use Doctrine\DBAL\Exception\ConnectionException;

class CreateHistoryTableCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);

        $databaseAdapterFactory = $container->get(AdapterFactory::class);

        $rootDbUser = getenv('PMA_USER');
        $rootDbPassword = getenv('PMA_PASSWORD');

        return new CreateHistoryTableCommand($logger, $databaseAdapterFactory, $rootDbUser, $rootDbPassword);
    }

}