<?php

namespace App\Services\Gestion;

use Exception;

class GestionAuthException extends Exception
{
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
