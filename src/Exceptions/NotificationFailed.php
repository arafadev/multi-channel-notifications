<?php


namespace Arafa\Notifications\Exceptions;

use Exception;

class NotificationFailed extends Exception
{
    public function __construct(string $message = "Failed to send notification")
    {
        parent::__construct($message);
    }
}