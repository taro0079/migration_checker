<?php

declare(strict_types=1);

namespace MigrationChecker\Checker;

use MigrationChecker\CheckType;
use MigrationChecker\Dto\ErrorDto;
use MigrationChecker\Where;

class ColumnNameChecker implements CheckerInterface
{
    public function columnCheck(array $entity_columns, array $database_columns): array
    {
        $entity_column_names = array_map(fn ($element) => $element->field, $entity_columns);
        $db_column_names     = array_map(fn ($element) => $element->field, $database_columns);
        $unique_in_entity    = array_diff($entity_column_names, $db_column_names);
        $unique_in_db        = array_diff($db_column_names, $entity_column_names);

        $error_dtos = [];
        foreach ($unique_in_entity as $column) {
            $error_dtos[] = new ErrorDto(column: $column, where: Where::ENTITY, checkType: CheckType::COLUMN);
        }

        foreach ($unique_in_db as $column) {
            $error_dtos[] = new ErrorDto(column: $column, where: Where::DB, checkType: CheckType::COLUMN);
        }

        return $error_dtos;
    }

    public function createErrorMessage(array $error_dtos): array
    {
        $error_messages = [];
        foreach ($error_dtos as $dto) {
            $template         = 'Column name: %s is only found in %s';
            $error_messages[] = sprintf($template, $dto->column, $dto->where->text());
        }

        return $error_messages;
    }
}
