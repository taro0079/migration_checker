<?php

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
