<?php

class EntityParser
{
    public const filepath = 'test.php';
    private string $name_pattern = '/name: \'(.*?)\'/';
    private string $column_pattern = '/#\[ORM.*(?:Column|JoinColumn)(.*)/';

    private string $attribute_pattern = '/#\[ORM.*(?:Column|JoinColumn|OneToMany|OneToOne|ManyToOne).*/';
    private string $table_pattern = '/#\[ORM\\\\Table\(name:\s\'(.*?)\',/';

    private string $relation_pattern = '/#\[ORM\\\\(?:OneToMany|ManyToOne|OneToOne|JoinColumn).*/';

    public function getTableName()
    {
        $pattern = $this->table_pattern;
        $file = new SplFileObject(self::filepath);

        while (!$file->eof()) {
            $line = $file->fgets();

            if (preg_match($pattern, $line, $m)) {
                $matches =  $m[1];
            }
        }
        return $matches;
    }
    public function getAttribute()
    {
        $file_path = self::filepath;
        $pattern = $this->attribute_pattern;
        $file = new SplFileObject($file_path);
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

    public function getProperties()
    {
        $file_path = self::filepath;
        $pattern = '/private.*\$(.*)[,|;]/';
        $file = new SplFileObject($file_path);
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

    private function getType($attribute_text)
    {
        preg_match($this->relation_pattern, $attribute_text, $relation_match);
        if (null!==$relation_match[0]) { return 'relation'; }


        $pattern = '/type:\s(.*?)[,|\)]/';
        preg_match($pattern, $attribute_text, $matches);
        $row_type = $matches[1];
        return $this->typeMap($row_type);

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

    public function getDbColumn()
    {
        $sets = $this->formattedCompleteSet();
        $result=[];
        foreach ($sets as $set) {
            $attributes = $set['attrs'];
            $types = array_map(fn ($element) =>$this->getType($element['match']), $attributes);
            $column_name = $this->getColumnName($set);
            $type = count($types) === 0 ? null : $types[0];
            $result[] = new DbColumnDto(field: $column_name, type: $type);
        }


        return $result;

    }

    private function camelToSnakeCase($string)
    {
        $pattern = '/[A-Z]/';
        $replacement = '_$0';
        $snakeCase = strtolower(preg_replace($pattern, $replacement, $string));
        return ltrim($snakeCase, '_');
    }

    private function typeMap($row_type)
    {
        return match ($row_type) {
            'Types::BIGINT' => 'bigint',
            'Types::STRING' => 'varchar',
            'Types::INTEGER' => 'int',
            'Types::BOOLEAN' => 'bool',

        };
    }

    public function createSets()
    {
        $set =[];
        $a = $this->getAttribute();
        $p = $this->getProperties();
        foreach ($a as $aa) {
            $larger = array_filter($p, fn ($element) =>$element['line'] > $aa['line']);
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
        $temp=[];
        foreach ($this->createSets() as $s) {
            if(isset($temp[$s['property']['property']])) {
                continue;
            }
            $temp[$s['property']['property']] = $s['property']['property'];
        }
        $complete = [];
        foreach($temp as $t =>$tv) {
            $attr = [];

            foreach($this->createSets() as $s) {
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
        foreach($sets as $set) {
            $attributes = $set['attrs'];
            foreach ($attributes as $attribute) {
                preg_match($this->column_pattern, $attribute['match'], $match);
                if (null!==$match[0]) { 
                    $result[] = $set;
                    continue;
                }
            }
        }

        return $result;
    }

    public function getColumnNames()
    {
        $result = $this->createComplete();
        $column_names_from_entity= [];
        foreach ($result as $row) {
            $column_names_from_entity[] = $this->getColumnName($row);
        }
        return $column_names_from_entity;
    }


    public function getDefaultColumn()
    {
        return [
            [
                'field' => 'created_at',
            ],
            [
                'field' => 'updated_at',
            ]
        ];
    }
}

// mysql management class
class DbConnector
{
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
            $typed[] = [
                'field' => $row[0],
                'type' => $row[1],
                'nullable' => $row[2] === "YES",
                'key' => $row[3],
                'default' => $row[4] === "NULL" ? null : $row[4],
            ];
        }
        return $typed;
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
        public ?string $type = null,
        public ?bool $nullable = null,
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
        DbConnector $dbConnector,
        EntityParser $parser
    ) {
        $this->dbConnector = $dbConnector;
        $this->parser = $parser;
    }

    public function getDiffForColumnName()
    {
        $names_from_entity = $this->parser->getColumnNames();
        $names_from_db = $this->dbConnector->getColumnName($this->parser->getTableName());
        $row_diff = array_diff($names_from_db, $names_from_entity);
        $default_column_names = array_map(fn ($e) =>$e['field'], $this->parser->getDefaultColumn());
        return array_diff($row_diff, $default_column_names);


    }

}


$c = new EntityParser();
$result = $c->getDbColumn();
var_dump($result);
// $db_connector = new DbConnector();
// $shougo = new Shougo($db_connector, $c);
// $res = $shougo->getDiffForColumnName();
//
// var_dump($res);
