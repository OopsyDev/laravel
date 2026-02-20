<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Breadcrumbs;

class Breadcrumb
{
    public function __construct(
        public readonly string $type,
        public readonly string $category,
        public readonly string $message,
        public readonly ?array $data = null,
        public readonly ?string $timestamp = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'category' => $this->category,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => $this->timestamp ?? now()->toIso8601String(),
        ];
    }
}
