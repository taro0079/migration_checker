<?php

declare(strict_types=1);

namespace MigrationChecker;

use Exception;

class FileReader
{
    public function __construct(
        private string $file_path
    ) {
    }

    public function readFile(): array
    {
        if (!file_exists($this->file_path)) {
            throw new Exception('file not found');
        }

        return file($this->file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
