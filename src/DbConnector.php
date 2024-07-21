<?php

declare(strict_types=1);

namespace MigrationChecker;

use MigrationChecker\Dto\DbColumnDto;
use mysqli;

class DbConnector
{
    private string $length_pattern = '/varchar\((.*)\)/';

    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getDesc($table_name)
    {
        $query  = sprintf('DESC %s', $table_name);
        $result = $this->mysqli->query($query)->fetch_all();
        $typed  = [];
        foreach ($result as $row) {
            $typed[] = new DbColumnDto(
                field: $row[0],
                type: $this->getType($row[1]),
                nullable: $row[2] === 'YES',
                length: $this->getLength($row[1]),
                key: $row[3],
                default: $row[4] === 'NULL' ? null : $row[4]
            );
        }

        return $typed;
    }

    private function getLength(string $column_type): ?int
    {
        preg_match($this->length_pattern, $column_type, $matches);
        if (null === $matches) {
            return null;
        }
        if (!isset($matches[1])) {
            return null;
        }

        return (int) $matches[1];
    }

    private function getType(string $raw_type_string): ?DbType
    {
        var_dump($raw_type_string);
        switch ($raw_type_string) {
            case false !== strpos($raw_type_string, 'varchar'):

                return DbType::VARCHAR;

            case 'bigint':
                return DbType::BIGINT;

            case 'int':
                return DbType::INT;

            case false !== strpos($raw_type_string, 'tinyint'):
                return DbType::TINY_INT;

            case 'datetime':
                return DbType::DATETIME;

            case 'longtext':
                return DbType::TEXT;

            case 'date':
                return DbType::DATE;
        }

        return null;
    }

    public function getColumnName(string $table_name)
    {
        $table_info   = $this->getDesc($table_name);
        $column_names = [];
        foreach ($table_info as $table) {
            $column_names[] = $table['field'];
        }

        return $column_names;
    }
}
