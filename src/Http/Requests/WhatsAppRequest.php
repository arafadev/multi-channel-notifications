<?php

namespace Arafa\Notifications\Http\Requests;


class WhatsAppRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'regex:/^whatsapp:\+?[1-9]\d{1,14}$/'],
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
            'recipient.regex' => 'The recipient must be a valid WhatsApp number in the format "whatsapp:+12345678901".',
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
