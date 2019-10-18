<?php

declare(strict_types=1);

namespace JStormes\dbTool;


use Psr\Log\LoggerInterface;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'console' => $this->getConsole()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                LoggerInterface::class => Log\Factory\LogFactory::class,
                Adapter\AdapterFactory::class => Adapter\AdapterFactory::class,
                CommandLine\Command\VerifyDbCommand::class => CommandLine\Command\VerifyDbCommandFactory::class,
                CommandLine\Command\CreateDbCommand::class => CommandLine\Command\CreateDbCommandFactory::class,
                CommandLine\Command\CreateHistoryTableCommand::class => CommandLine\Command\CreateHistoryTableCommandFactory::class
            ],
        ];
    }

    public function getConsole() : array
    {
        return [
            'commands' => [
                CommandLine\Command\VerifyDbCommand::class,
                CommandLine\Command\CreateDbCommand::class,
                CommandLine\Command\CreateHistoryTableCommand::class
            ],
        ];
    }

}
