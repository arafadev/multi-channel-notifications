<?php

namespace Arafa\Notifications\Http\Requests;


class MessengerRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'regex:/^\d+$/'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
            'data.*' => ['sometimes'],
            'options' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'recipient.regex' => 'The recipient must be a valid Facebook user ID (numeric).',
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
