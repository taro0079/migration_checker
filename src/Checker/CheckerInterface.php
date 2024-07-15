<?php

declare(strict_types=1);

namespace MigrationChecker\Checker;

interface CheckerInterface
{
    public function columnCheck(array $entity_columns, array $database_columns): array;

    public function createErrorMessage(array $error_dtos): array;
}
