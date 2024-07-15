<?php

declare(strict_types=1);

namespace MigrationChecker\Checker;

use MigrationChecker\CheckType;
use MigrationChecker\DbType;
use MigrationChecker\Dto\ErrorDto;
use MigrationChecker\Where;

class ColumnTypeChecker implements CheckerInterface
{
    public function columnCheck(array $entity_columns, array $database_columns): array
    {
        $error_dtos = [];
        $check_list = array_filter($entity_columns, fn ($element) => $element->type !== DbType::RELATION); // 他のエンティティと関連があるカラムは型判定をしない
        foreach ($check_list as $entity_column) {
            $entity_column_name = $entity_column->field;
            $db_column          = array_filter($database_columns, fn ($element) => $element->field === $entity_column_name);
            if (count($db_column) === 0) {
                continue;
            }
            $db_column = array_values($db_column)[0];
            if ($entity_column->type !== $db_column->type) {
                $error_dtos[] = new ErrorDto(column: $entity_column_name, where: Where::ENTITY, checkType: CheckType::TYPE);
            }
        }

        return $error_dtos;
    }

    public function createErrorMessage(array $error_dtos): array
    {
        $error_messages = [];
        foreach ($error_dtos as $dto) {
            $template         = 'Type of column defined in entity class is different from database. Column name: %s';
            $error_messages[] = sprintf($template, $dto->column, $dto->where->text());
        }

        return $error_messages;
    }
}
