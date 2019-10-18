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

        $databaseAdapterFactory = $container->get(AdapterFactory::class);

        return new VerifyDbCommand($logger, $databaseAdapterFactory);
    }

}