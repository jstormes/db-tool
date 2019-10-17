<?php

declare(strict_types=1);

namespace JStormes\dbTool\Adapter;

use Interop\Container\ContainerInterface;
use JStormes\dbTool\Adapter\dbType\mysqlDb;
use JStormes\dbTool\Adapter\dbType\mariaDb;
use JStormes\dbTool\Lib\parseDatabaseURL;
use JStormes\dbTool\Exception\DatabaseException;


class AdapterFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return $this;
    }

    public function getAdapter($databaseUrl)
    {
        $urlParser = new parseDatabaseURL();

        $scheme = $urlParser->getDbScheme($databaseUrl);

        switch ($scheme) {
            case "mysql":
                $adapter = new mysqlDb();
                break;
            case "maria":
                $adapter = new mariaDb();
                break;
            default:
                throw new DatabaseException("Adapter $scheme not found.");
        }

        return $adapter;
    }

}