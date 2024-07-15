<?php

// class FileReader
// {
//     public function __construct(
//         private string $file_path
//     ) {
//     }
//
//     public function readFile(): array
//     {
//         if (!file_exists($this->file_path)) {
//             throw new Exception('file not found');
//         }
//
//         return file($this->file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     }
// }

/**
 * 他のエンティティと関連付いているカラムについては型判定をしない。なぜなら、型情報が他の関連付いているエンティティに定義されており実装が大変だから。
 */
// class EntityParser
// {
//     //    private $file;
//     private string $name_pattern        = '/name: \'(.*?)\'/';
//     private string $column_pattern      = '/#\[ORM.*(?:Column|JoinColumn)(.*)/';
//     private string $join_column_pattern = '/#\[ORM\\\\JoinColumn\(name:\s[\'"](.*?)[\'"],/';
//
//     private string $attribute_pattern = '/#\[ORM.*(?:Column|JoinColumn|OneToMany|OneToOne|ManyToOne).*/';
//     private string $table_pattern     = '/#\[ORM\\\\Table\(name:\s[\'"](.*?)[\'"],/';
//
//     private string $relation_pattern = '/#\[ORM\\\\(?:OneToMany|ManyToOne|OneToOne|JoinColumn).*/';
//
//     private string $nullable_pattern = '/nullable:\s(.*?)[,|\)]/';
//
//     private string $length_pattern = '/length:\s(.*?)[,|\)]/';
//
//     private array $file_lines;
//
//     public function __construct(
//         FileReader $fileReader
//     ) {
//         $this->file_lines = $fileReader->readFile();
//     }
//
//     public function readFile(string $file_path): array
//     {
//         if (!file_exists($file_path)) {
//             throw new Exception('file not found');
//         }
//
//         return file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     }
//
//     public function getTableName(): string
//     {
//         $pattern = $this->table_pattern;
//
//         foreach ($this->file_lines as $line) {
//             if (preg_match($pattern, $line, $m)) {
//                 $matches = $m[1];
//             }
//         }
//
//         return $matches;
//     }
//
//     /*
//      * @return array{line: int, match: string}[]
//      */
//     public function getAttribute(): array
//     {
//         $pattern = $this->attribute_pattern;
//         $matches = [];
//
//         foreach ($this->file_lines as $line_number => $line) {
//             if (preg_match($pattern, $line, $m)) {
//                 $matches[] = [
//                     'line' => $line_number,
//                     'match' => $m[0],
//                 ];
//             }
//         }
//
//         return $matches;
//     }
//
//     /**
//      * @return array{line: int, match: string, property: string}[]
//      */
//     public function getProperties(): array
//     {
//         $pattern = '/private.*\$(.*)[,|;]/';
//         $matches = [];
//
//         foreach ($this->file_lines as $line_number => $line) {
//             if (preg_match($pattern, $line, $m)) {
//                 $matches[] = [
//                     'line' => $line_number,
//                     'match' => $m[0],
//                     'property' => $m[1],
//                 ];
//             }
//         }
//
//         return $matches;
//     }
//
//     private function getType($attribute_text): ?DbType
//     {
//         preg_match($this->relation_pattern, $attribute_text, $relation_match);
//         if (isset($relation_match[0])) {
//             return DbType::RELATION;
//         }
//
//         $pattern = '/type:\s(.*?)[,|\)]/';
//         preg_match($pattern, $attribute_text, $matches);
//         $row_type = $matches[1];
//
//         return $this->typeMap($row_type);
//     }
//
//     private function getNullable(string $attributre_text): ?string
//     {
//         preg_match($this->nullable_pattern, $attributre_text, $matched);
//         if (null === $matched) {
//             return null;
//         }
//         if (!isset($matched[1])) {
//             return null;
//         }
//
//         return $matched[1];
//     }
//
//     private function getLength(string $attribute_text): ?int
//     {
//         preg_match($this->length_pattern, $attribute_text, $matched);
//         if (null === $matched) {
//             return null;
//         }
//         if (!isset($matched[1])) {
//             return null;
//         }
//
//         return (int) $matched[1];
//     }
//
//     public function getColumnName($set)
//     {
//         $attributes          = $set['attrs'];
//         $column_name_pattern = $this->name_pattern;
//         foreach ($attributes as $attribute) {
//             preg_match($column_name_pattern, $attribute['match'], $matches);
//             if (!isset($matches[1])) {
//                 continue;
//             }
//
//             return $matches[1];
//         }
//         $property          = $set['prop'];
//         $camelPropertyName = $property['property'];
//
//         return $this->camelToSnakeCase($camelPropertyName);
//     }
//
//     /**
//      * @return DbColumnDto[]
//      */
//     public function getDbColumn(): array
//     {
//         $sets   = $this->extractOnlyColumn();
//         $sets   = $this->changeNameForDbColumn($sets);
//         $result = [];
//         foreach ($sets as $set) {
//             $attributes  = $set['attrs'];
//             $types       = array_map(fn ($element) => $this->getType($element['match']), $attributes);
//             $type        = count($types) === 0 ? null : $types[0];
//             $lengths     = array_map(fn ($element) => $this->getLength($element['match']), $attributes);
//             $length      = count($lengths) === 0 ? null : $lengths[0];
//             $nullables   = array_map(fn ($element) => $this->getNullable($element['match']), $attributes);
//             $nullable    = count($nullables) === 0 ? null : $nullables[0];
//             $column_name = $this->getColumnName($set);
//             $result[]    = new DbColumnDto(field: $column_name, type: $type, nullable: $nullable, length: $length);
//         }
//
//         return $this->addDefaultColumn($result);
//     }
//
//     private function addDefaultColumn(array $columns): array
//     {
//         $default_columns = $this->getDefaultColumn();
//
//         return array_merge($columns, $default_columns);
//     }
//
//     private function camelToSnakeCase($string): string
//     {
//         $pattern     = '/[A-Z]/';
//         $replacement = '_$0';
//         $snakeCase   = strtolower(preg_replace($pattern, $replacement, $string));
//
//         return ltrim($snakeCase, '_');
//     }
//
//     private function typeMap($row_type): DbType
//     {
//         return match ($row_type) {
//             'Types::BIGINT' => DbType::BIGINT,
//             'Types::STRING' => DbType::VARCHAR,
//             'Types::INTEGER' => DbType::INT,
//             'Types::BOOLEAN' => DbType::TINY_INT,
//         };
//     }
//
//     public function createSets()
//     {
//         $set = [];
//         $a   = $this->getAttribute();
//         $p   = $this->getProperties();
//         foreach ($a as $aa) {
//             $larger = array_filter($p, fn ($element) => $element['line'] > $aa['line']);
//             $acm    = 100000;
//             $target = null;
//             foreach ($larger as $l) {
//                 if ($l['line'] < $acm) {
//                     $acm    = $l['line'];
//                     $target = $l;
//                 }
//             }
//             $set[] = [
//                 'attribute' => $aa,
//                 'property' => $target,
//             ];
//         }
//
//         return $set;
//     }
//
//     public function createComplete()
//     {
//         $temp = [];
//         foreach ($this->createSets() as $s) {
//             if (isset($temp[$s['property']['property']])) {
//                 continue;
//             }
//             $temp[$s['property']['property']] = $s['property']['property'];
//         }
//         $complete = [];
//         foreach ($temp as $t => $tv) {
//             $attr = [];
//
//             foreach ($this->createSets() as $s) {
//                 if ($s['property']['property'] == $tv) {
//                     $attr[] = $s['attribute'];
//                     $ppp    = $s['property'];
//                 }
//             }
//             $attttt     = $attr;
//             $ppppp      = $ppp;
//             $complete[] = [
//                 'prop' => $ppppp,
//                 'attrs' => $attttt,
//             ];
//         }
//
//         return $complete;
//     }
//
//     /**
//      * attributeにColumn, JoinColumnと含まれているpropertyのみを抽出します
//      */
//     private function extractOnlyColumn(): array
//     {
//         $sets   = $this->createComplete();
//         $result = [];
//         foreach ($sets as $set) {
//             $attributes = $set['attrs'];
//             foreach ($attributes as $attribute) {
//                 preg_match($this->column_pattern, $attribute['match'], $match);
//                 if (isset($match[0])) {
//                     $result[] = $set;
//                     continue;
//                 }
//             }
//         }
//
//         return $result;
//     }
//
//     public function changeNameForDbColumn(array $sets): array
//     {
//         foreach ($sets as $key => $set) {
//             $attributes = $set['attrs'];
//             foreach ($attributes as $attribute) {
//                 preg_match($this->join_column_pattern, $attribute['match'], $match);
//                 if (isset($match[1])) {
//                     $sets[$key]['prop']['property'] = $match[1];
//                 }
//             }
//         }
//
//         return $sets;
//     }
//
//     /**
//      * @return string[]
//      */
//     public function getColumnNames(): array
//     {
//         $result                   = $this->createComplete();
//         $column_names_from_entity = [];
//         foreach ($result as $row) {
//             $column_names_from_entity[] = $this->getColumnName($row);
//         }
//
//         return $column_names_from_entity;
//     }
//
//     /**
//      * @return DbColumnDto[]
//      */
//     public function getDefaultColumn(): array
//     {
//         return [
//             new DbColumnDto(
//                 field: 'created_at',
//                 type: DbType::DATETIME,
//                 nullable: false,
//                 key: null,
//                 default: null
//             ),
//
//             new DbColumnDto(
//                 field: 'updated_at',
//                 type: DbType::DATETIME,
//                 nullable: false,
//                 key: null,
//                 default: null
//             ),
//         ];
//     }
// }

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

// mysql management class
// class DbConnector
// {
//     private string $length_pattern = '/varchar\((.*)\)/';
//
//     private $mysqli;
//
//     public function __construct(mysqli $mysqli)
//     {
//         $this->mysqli = $mysqli;
//     }
//
//     public function getDesc($table_name)
//     {
//         $query  = sprintf('DESC %s', $table_name);
//         $result = $this->mysqli->query($query)->fetch_all();
//         $typed  = [];
//         foreach ($result as $row) {
//             $typed[] = new DbColumnDto(
//                 field: $row[0],
//                 type: $this->getType($row[1]),
//                 nullable: $row[2] === 'YES',
//                 length: $this->getLength($row[1]),
//                 key: $row[3],
//                 default: $row[4] === 'NULL' ? null : $row[4]
//             );
//         }
//
//         return $typed;
//     }
//
//     private function getLength(string $column_type): ?int
//     {
//         preg_match($this->length_pattern, $column_type, $matches);
//         if (null === $matches) {
//             return null;
//         }
//         if (!isset($matches[1])) {
//             return null;
//         }
//
//         return (int) $matches[1];
//     }
//
//     private function getType(string $raw_type_string): ?DbType
//     {
//         switch ($raw_type_string) {
//             case strpos($raw_type_string, 'varchar'):
//                 return DbType::VARCHAR;
//
//             case 'bigint':
//                 return DbType::BIGINT;
//
//             case 'int':
//                 return DbType::INT;
//
//             case 'tiny_int':
//                 return DbType::TINY_INT;
//
//             case 'datetime':
//                 return DbType::DATETIME;
//         }
//
//         return null;
//     }
//
//     public function getColumnName(string $table_name)
//     {
//         $table_info   = $this->getDesc($table_name);
//         $column_names = [];
//         foreach ($table_info as $table) {
//             $column_names[] = $table['field'];
//         }
//
//         return $column_names;
//     }
// }

// class DbColumnDto
// {
//     public function __construct(
//         public ?string $field = null,
//         public ?DbType $type = null,
//         public ?bool $nullable = null,
//         public ?int $length = null,
//         public ?string $key = null,
//         public ?string $default = null,
//     ) {
//     }
// }

// enum Where
// {
//     case ENTITY;
//     case DB;
//
//     public function text(): string
//     {
//         return match ($this) {
//             self::ENTITY => 'Entity',
//             self::DB     => 'DataBase',
//         };
//     }
// }

// class ErrorDto
// {
//     public function __construct(
//         public string $column,
//         public Where $where,
//         public CheckType $checkType
//     ) {
//     }
// }

// enum CheckType
// {
//     case COLUMN;
//     case TYPE;
// }

// class ErrorMessage
// {
//     public function __construct(
//         public string $message
//     ) {
//     }
//
//     public static function fromError(ErrorDto $error): self
//     {
//         $message = 'Column name: ' . $error->column . ' is only found in ' . $error->where->text();
//
//         return new self($message);
//     }
// }

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
        $db_columns     = $this->dbConnector->getDesc($this->parser->getTableName());

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

interface Checker
{
    public function columnCheck(array $entity_columns, array $database_columns): array;

    public function createErrorMessage(array $error_dtos): array;
}

class ColumnNameChecker implements Checker
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

class ColumnTypeChecker implements Checker
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

function getFilePathsFrom(array $argv): array
{
    $argv       = array_slice($argv, 1);
    $file_paths = [];

    foreach ($argv as $arg) {
        if (!file_exists($arg)) {
            throw new Exception('file not found');
        }
        $file_paths[] = $arg;
    }

    return $file_paths;
}

function main(): void
{
    global $argv;
    try {
        $file_paths = getFilePathsFrom($argv);
    } catch(Exception $e) {
        fwrite(STDERR, $e->getMessage());
    }
}

main();
