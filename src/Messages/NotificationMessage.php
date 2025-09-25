<?php

namespace Arafa\Notifications\Messages;

class NotificationMessage
{
    public function __construct(
        public string $title,
        public string $body,
        public array $data = [],
        public array $attachments = [],
        public array $options = []
    ) {}
    
    public static function create(string $title, string $body): self
    {
        return new self($title, $body);
    }
    
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function withAttachments(array $attachments): self
    {
        $this->attachments = array_merge($this->attachments, $attachments);
        return $this;
    }
    
    public function withOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }
    
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'attachments' => $this->attachments,
            'options' => $this->options,
        ];
    }
}