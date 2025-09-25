<?php


namespace Arafa\Notifications\Exceptions;

use Exception;

class ChannelNotFound extends Exception
{
    public function __construct(string $message = "The specified notification channel was not found")
    {
        parent::__construct($message);
    }
}