<?php

namespace App\Exceptions;

use Exception;

class SlotUnavailableException extends Exception
{
    public function __construct(string $message = 'Horário não disponível.')
    {
        parent::__construct($message);
    }
}
