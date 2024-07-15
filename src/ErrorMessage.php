<?php

declare(strict_types=1);

namespace MigrationChecker;

use MigrationChecker\Dto\ErrorDto;

class ErrorMessage
{
    public function __construct(
        public string $message
    ) {
    }

    public static function fromError(ErrorDto $error): self
    {
        $message = 'Column name: ' . $error->column . ' is only found in ' . $error->where->text();

        return new self($message);
    }
}
