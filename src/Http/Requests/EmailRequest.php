<?php

namespace Arafa\Notifications\Http\Requests;

use Arafa\Notifications\Http\Requests\NotificationRequest;

class EmailRequest extends NotificationRequest
{
 
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'email'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
            'data.*' => ['sometimes'],
            'attachments' => ['sometimes', 'array'],
            'attachments.*' => ['sometimes', 'string', 'exists:file'],
            'options' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get the recipient for the notification.
     */
    public function getRecipient(): string
    {
        return $this->input('recipient');
    }
}
