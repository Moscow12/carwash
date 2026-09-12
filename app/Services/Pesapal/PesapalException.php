<?php

namespace App\Services\Pesapal;

class PesapalException extends \RuntimeException
{
    public function __construct(string $message, private readonly array $payload = [])
    {
        parent::__construct($message);
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
