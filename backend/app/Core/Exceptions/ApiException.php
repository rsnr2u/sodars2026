<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $statusCode;

    protected mixed $errors;

    public function __construct(
        string $message = 'API error occurred.',
        int $statusCode = 500,
        mixed $errors = null
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }
}
