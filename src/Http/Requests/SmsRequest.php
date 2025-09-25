<?php

namespace Arafa\Notifications\Http\Requests;


class SmsRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1600'], // SMS typically has character limits
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
            'recipient.regex' => 'The recipient must be a valid phone number in E.164 format (e.g., +12345678901).',
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
