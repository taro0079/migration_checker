<?php

declare(strict_types=1);

namespace MigrationChecker;

enum DbType
{
    case VARCHAR;
    case INT;
    case BIGINT;
    case TINY_INT;
    case DATETIME;
    case TEXT;
    case RELATION;
}
