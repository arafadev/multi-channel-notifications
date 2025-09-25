<?php


namespace Arafa\Notifications\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public function __construct(string $message = "Invalid notification configuration")
    {
        parent::__construct($message);
    }
}