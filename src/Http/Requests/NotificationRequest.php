<?php

namespace Arafa\Notifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class NotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    abstract public function rules(): array;

    /**
     * Get the title for the notification.
     */
    public function getTitle(): string
    {
        return $this->input('title');
    }

    /**
     * Get the body for the notification.
     */
    public function getBody(): string
    {
        return $this->input('body');
    }

    /**
     * Get the additional data for the notification.
     */
    public function getData(): array
    {
        return $this->input('data', []);
    }

    /**
     * Get the attachments for the notification.
     */
    public function getAttachments(): array
    {
        return $this->input('attachments', []);
    }

    /**
     * Get the options for the notification.
     */
    public function getOptions(): array
    {
        return $this->input('options', []);
    }

    /**
     * Get the recipient for the notification.
     */
    abstract public function getRecipient(): string;
}
