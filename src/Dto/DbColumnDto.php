<?php

declare(strict_types=1);

namespace MigrationChecker\Dto;

use MigrationChecker\DbType;

class DbColumnDto
{
    public function __construct(
        public ?string $field = null,
        public ?DbType $type = null,
        public ?bool $nullable = null,
        public ?int $length = null,
        public ?string $key = null,
        public ?string $default = null,
    ) {
    }
}
