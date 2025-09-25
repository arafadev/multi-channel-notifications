<?php

namespace Arafa\Notifications\Http\Requests;


class TeamsRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
            'data.*' => ['sometimes'],
            'options' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get the recipient for the notification.
     *
     * Teams uses a webhook URL configured in the .env file,
     * so we don't need a recipient in the request.
     */
    public function getRecipient(): string
    {
        return '';
    }
}
