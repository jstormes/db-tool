<?php

/**
 * For the sake of the example this file can be used as-is. Simply rename this file
 * to doctrine.local.php
 *
 * Use this command to gain CLI usage of Doctrine and Doctrine-dbal, and change hash to be
 * the ID of the web container.
 *
 * $ docker exec -i -t 938657fa9b5e bash
 *
 * Then we now have access to:
 * $ php vendor/bin/doctrine list
 * $ php vendor/bin/doctrine-dbal list
 *
 */

declare(strict_types=1);

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'url' => getenv('DB'),  // Get doctrine Database connection from Environment variable.
                    'charset'  => 'utf8mb4',
                    'driverOptions' => array(
                        1002 => 'SET NAMES utf8mb4'
                    )
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'app_entity',
                ],
            ],
            'app_entity' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../../src/App/Entity'],
            ],
        ],
    ],
];
