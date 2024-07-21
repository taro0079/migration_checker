<?php

declare(strict_types=1);

namespace MigrationChecker;

use MigrationChecker\Checker\ColumnNameChecker;
use MigrationChecker\Checker\ColumnTypeChecker;

/**
 * EntityParserのほうでDbType::RELATIONとなっているカラムについては判定対象に含めない
 */
class Verification
{
    private DbConnector $dbConnector;

    private EntityParser $parser;

    /**
     * @var Checker[]
     */
    private array $checkers;

    public function __construct(
        DbConnector $dbConnector,
        EntityParser $parser
    ) {
        $this->dbConnector = $dbConnector;
        $this->parser      = $parser;
        $this->checkers    = [
            new ColumnNameChecker(),
            new ColumnTypeChecker(),
        ];
    }

    public function columnCheck(): array
    {
        $errors         = [];
        $entity_columns = $this->parser->getDbColumn();
        // var_dump($entity_columns);
        $db_columns = $this->dbConnector->getDesc($this->parser->getTableName());
        // var_dump($db_columns);

        return $this->getErrorMessages($entity_columns, $db_columns);
    }

    private function getErrorMessages(array $entity_columns, array $db_columns): array
    {
        $error_messages = [];

        foreach ($this->checkers as $checker) {
            $error_messages = [...$error_messages, ...$checker->createErrorMessage($checker->columnCheck($entity_columns, $db_columns))];
        }

        return $error_messages;
    }
}
