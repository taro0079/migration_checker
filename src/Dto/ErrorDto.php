<?php

declare(strict_types=1);

namespace MigrationChecker\Dto;

use MigrationChecker\CheckType;
use MigrationChecker\Where;

class ErrorDto
{
    public function __construct(
        public string $column,
        public Where $where,
        public CheckType $checkType
    ) {
    }
}
