<?php

declare(strict_types=1);

namespace MigrationChecker;

enum Where
{
    case ENTITY;
    case DB;

    public function text(): string
    {
        return match ($this) {
            self::ENTITY => 'Entity',
            self::DB     => 'DataBase',
        };
    }
}
