<?php

declare(strict_types=1);

namespace Oopsy\Laravel\StackTrace;

class Frame
{
    public function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly ?string $function,
        public readonly ?string $class,
        public readonly bool $inApp,
        public readonly ?array $context = null,
    ) {}

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'function' => $this->function,
            'class' => $this->class,
            'in_app' => $this->inApp,
            'context' => $this->context,
        ];
    }
}
