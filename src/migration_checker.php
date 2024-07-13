<?php

//require '../vendor/autoload.php';
class EntityParser
{
    //    private $file;
    private string $file_path = '../test.php';
    private string $name_pattern = '/name: \'(.*?)\'/';
    private string $column_pattern = '/#\[ORM.*(?:Column|JoinColumn)(.*)/';

    private string $attribute_pattern = '/#\[ORM.*(?:Column|JoinColumn|OneToMany|OneToOne|ManyToOne).*/';
    private string $table_pattern = '/#\[ORM\\\\Table\(name:\s\'(.*?)\',/';

    private string $relation_pattern = '/#\[ORM\\\\(?:OneToMany|ManyToOne|OneToOne|JoinColumn).*/';

    private string $nullable_pattern = '/nullable:\s(.*?)[,|\)]/';

    private string $length_pattern = '/length:\s(.*?)[,|\)]/';

    //    public function __construct(
    //         string $file_path,
    //    )
    //    {
    //        if (!file_exists($file_path)) {
    //            throw new Exception("File not found: $file_path");
    //        }
    //        $this->file = new SplFileObject($file_path);
    //
    //    }


    public function getTableName(): string
    {
        $pattern = $this->table_pattern;
        $file = new SplFileObject($this->file_path);

        while (!$file->eof()) {
            $line = $file->fgets();

            if (preg_match($pattern, $line, $m)) {
                $matches = $m[1];
            }
        }
        return $matches;
    }

    /*
     * @return array{line: int, match: string}[]
     */
    public function getAttribute(): array
    {
        $pattern = $this->attribute_pattern;
        $file = new SplFileObject($this->file_path);
        $matches = [];

        while (!$file->eof()) {
            $line = $file->fgets();
            $line_number = $file->key() + 1;

            if (preg_match($pattern, $line, $m)) {
                $matches[] = [
                    'line' => $line_number,
                    'match' => $m[0],
                ];
            }
        }
        return $matches;
    }

    /**
     * @return array{line: int, match: string, property: string}[]
     */
    public function getProperties(): array
    {
        $pattern = '/private.*\$(.*)[,|;]/';
        $file = new SplFileObject($this->file_path);
        $matches = [];

        while (!$file->eof()) {
            $line = $file->fgets();
            $line_number = $file->key() + 1;

            if (preg_match($pattern, $line, $m)) {
                $matches[] = [
                    'line' => $line_number,
                    'match' => $m[0],
                    'property' => $m[1],
                ];
            }
        }
        return $matches;
    }

    private function getType($attribute_text): ?DbType
    {
        preg_match($this->relation_pattern, $attribute_text, $relation_match);
        if (isset($relation_match[0])) {
            return DbType::RELATION;
        }


        $pattern = '/type:\s(.*?)[,|\)]/';
        preg_match($pattern, $attribute_text, $matches);
        $row_type = $matches[1];
        return $this->typeMap($row_type);

    }

    private function getNullable(string $attributre_text): ?string
    {
        preg_match($this->nullable_pattern, $attributre_text, $matched);
        if (null === $matched) {
            return null;
        }
        if (!isset($matched[1])) {
            return null;
        }

        return $matched[1];

    }

    private function getLength(string $attribute_text): ?int
    {
        preg_match($this->length_pattern, $attribute_text, $matched);
        if (null === $matched) {
            return null;
        }
        if (!isset($matched[1])) {
            return null;
        }

        return (int)$matched[1];

    }

    public function getColumnName($set)
    {
        $attrubites = $set['attrs'];
        $column_name_pattern = $this->name_pattern;
        foreach ($attrubites as $attrubite) {
            preg_match($column_name_pattern, $attrubite['match'], $matches);
            if (!isset($matches[1])) {
                continue;
            }
            return $matches[1];
        }
        $property = $set['prop'];
        $camelPropertyName = $property['property'];
        return $this->camelToSnakeCase($camelPropertyName);
    }

    /**
     * @return DbColumnDto[]
     */
    public function getDbColumn(): array
    {
        $sets = $this->formattedCompleteSet();
        $result = [];
        foreach ($sets as $set) {
            $attributes = $set['attrs'];
            $types = array_map(fn ($element) => $this->getType($element['match']), $attributes);
            $type = count($types) === 0 ? null : $types[0];
            $lengths = array_map(fn ($element) => $this->getLength($element['match']), $attributes);
            $length = count($lengths) === 0 ? null : $lengths[0];
            $nullables = array_map(fn ($element) => $this->getNullable($element['match']), $attributes);
            $nullable = count($nullables) === 0 ? null : $nullables[0];
            $column_name = $this->getColumnName($set);
            $result[] = new DbColumnDto(field: $column_name, type: $type, nullable: $nullable, length: $length);
        }


        return $result;

    }

    private function camelToSnakeCase($string): string
    {
        $pattern = '/[A-Z]/';
        $replacement = '_$0';
        $snakeCase = strtolower(preg_replace($pattern, $replacement, $string));
        return ltrim($snakeCase, '_');
    }

    private function typeMap($row_type): DbType
    {
        return match ($row_type) {
            'Types::BIGINT' => DbType::BIGINT,
            'Types::STRING' => DbType::VARCHAR,
            'Types::INTEGER' => DbType::INT,
            'Types::BOOLEAN' => DbType::TINY_INT,

        };
    }

    public function createSets()
    {
        $set = [];
        $a = $this->getAttribute();
        $p = $this->getProperties();
        foreach ($a as $aa) {
            $larger = array_filter($p, fn ($element) => $element['line'] > $aa['line']);
            $acm = 100000;
            $target = null;
            foreach ($larger as $l) {
                if ($l['line'] < $acm) {
                    $acm = $l['line'];
                    $target = $l;
                }
            }
            $set[] = [
                'attribute' => $aa,
                'property' => $target,
            ];
        }
        return $set;
    }

    public function createComplete()
    {
        $temp = [];
        foreach ($this->createSets() as $s) {
            if (isset($temp[$s['property']['property']])) {
                continue;
            }
            $temp[$s['property']['property']] = $s['property']['property'];
        }
        $complete = [];
        foreach ($temp as $t => $tv) {
            $attr = [];

            foreach ($this->createSets() as $s) {
                if ($s['property']['property'] == $tv) {
                    $attr[] = $s['attribute'];
                    $ppp = $s['property'];
                }
            }
            $attttt = $attr;
            $ppppp = $ppp;
            $complete[] = [
                'prop' => $ppppp,
                'attrs' => $attttt
            ];

        }
        return $complete;
    }

    private function formattedCompleteSet()
    {
        $sets = $this->createComplete();
        $result = [];
        foreach ($sets as $set) {
            $attributes = $set['attrs'];
            foreach ($attributes as $attribute) {
                preg_match($this->column_pattern, $attribute['match'], $match);
                if (isset($match[0])) {
                    $result[] = $set;
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        $result = $this->createComplete();
        $column_names_from_entity = [];
        foreach ($result as $row) {
            $column_names_from_entity[] = $this->getColumnName($row);
        }
        return $column_names_from_entity;
    }


    /**
     * @return DbColumnDto[]
     */
    public function getDefaultColumn(): array
    {
        return [
            new DbColumnDto(field: 'created_at', type: DbType::DATETIME, nullable: false, key: null, default: null),
            new DbColumnDto(field: 'updated_at', type: DbType::DATETIME, nullable: false, key: null, default: null)
        ];
    }
}

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
class DbConnector
{
    private string $length_pattern = '/varchar\((.*)\)/';

    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new mysqli('127.0.0.1', 'root', '!ChangeMe!', 'app_db');
    }

    public function getDesc($table_name)
    {
        $query = sprintf('DESC %s', $table_name);
        $result = $this->mysqli->query($query)->fetch_all();
        $typed = [];
        foreach ($result as $row) {
            $typed[] = new DbColumnDto(
                field: $row[0],
                type: $this->getType($row[1]),
                nullable: $row[2] === "YES",
                length: $this->getLength($row[1]),
                key: $row[3],
                default: $row[4] === "NULL" ? null : $row[4]
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
        if (null === $matches[1]) {
            return null;
        }

        return (int)$matches[1];


    }

    private function getType(string $raw_type_string): ?DbType
    {
        switch ($raw_type_string) {
            case strpos($raw_type_string, 'varchar'):
                return DbType::VARCHAR;

            case 'bigint':
                return DbType::BIGINT;

            case 'int':
                return DbType::INT;

            case 'tiny_int':
                return DbType::TINY_INT;

            case 'datetime':
                return DbType::DATETIME;
        }

        return null;

    }

    public function getColumnName(string $table_name)
    {
        $table_info = $this->getDesc($table_name);
        $column_names = [];
        foreach ($table_info as $table) {
            $column_names[] = $table['field'];
        }
        return $column_names;
    }

}

class DbColumnDto
{
    public function __construct(
        public ?string $field = null,
        public ?DbType $type = null,
        public ?string $nullable = null,
        public ?int    $length = null,
        public ?string $key = null,
        public ?string $default = null,
    ) {

    }

}

class Shougo
{
    private DbConnector $dbConnector;
    
    private EntityParser $parser;

    public function __construct(
        DbConnector  $dbConnector,
        EntityParser $parser
    ) {
        $this->dbConnector = $dbConnector;
        $this->parser = $parser;
    }

}


$c = new EntityParser('../test.php');
$result = $c->getDbColumn();
var_dump($result);
