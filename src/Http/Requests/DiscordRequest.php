<?php

namespace Arafa\Notifications\Http\Requests;


class DiscordRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipients' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
            'data.*' => ['sometimes'],
            'options' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get the recipient for the notification.
     */
    public function getRecipient(): string
    {
        $recipients = $this->input('recipients');

        if (is_array($recipients)) {
            return implode(',', $recipients);
        }

        return $recipients ?? '';
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure channel names start with # if they don't already and aren't numeric IDs
        if (
            $this->has('recipient') && is_string($this->input('recipient')) &&
            !str_starts_with($this->input('recipient'), '#') &&
            !preg_match('/^\d+$/', $this->input('recipient'))
        ) {
            $this->merge([
                'recipient' => '#' . $this->input('recipient')
            ]);
        }
    }
}
