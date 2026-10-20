<?php

namespace App\Services\Gestion;

use Exception;

class GestionApiException extends Exception
{
    public function __construct(
        string $message,
        public readonly array $payload = [],
    ) {
        parent::__construct($message);
    }
}
