<?php

declare(strict_types=1);

namespace JStormes\dbTool\Adapter\dbType;

class mariaDb extends mysqlDb
{

    function getAdapterTypeString(): string
    {
        return "maria";
    }

}