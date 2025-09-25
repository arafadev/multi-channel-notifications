<?php

namespace Arafa\Notifications\Http\Requests;


class SlackRequest extends NotificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string'],
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
        return $this->input('recipient');
    }

    /**
     * Prepare the data for validation.
     */
    // protected function prepareForValidation()
    // {
    //     // Ensure channel names start with # if they don't already
    //     if ($this->has('recipient') && is_string($this->input('recipient')) &&
    //         !str_starts_with($this->input('recipient'), '#') &&
    //         !str_starts_with($this->input('recipient'), 'C') &&
    //         !str_starts_with($this->input('recipient'), 'U')) {
    //         $this->merge([
    //             'recipient' => '#' . $this->input('recipient')
    //         ]);
    //     }
    // }
}
