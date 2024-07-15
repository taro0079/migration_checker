<?php

use MigrationChecker\DbConnector;
use MigrationChecker\EntityParser;
use MigrationChecker\FileReader;
use MigrationChecker\Verification;

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
        $mysqli     = new mysqli('127.0.0.1', 'root', '!ChangeMe!', 'app_db');
        foreach ($file_paths as $file_path) {
            $file_reader        = new FileReader($file_path);
            $entity_parser      = new EntityParser($file_reader);
            $database_connector = new DbConnector($mysqli);
            $verify             = new Verification($database_connector, $entity_parser);
            $errorMessages      = $verify->columnCheck();
        }
    } catch(Exception $e) {
        fwrite(STDERR, $e->getMessage());
        exit(1);
    }
}

main();
