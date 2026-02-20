<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use InvalidArgumentException;

class Dsn
{
    public function __construct(
        public readonly string $scheme,
        public readonly string $key,
        public readonly string $host,
        public readonly ?int $port,
        public readonly string $projectId,
    ) {}

    public static function parse(string $dsn): self
    {
        $parsed = parse_url($dsn);

        if (! $parsed || ! isset($parsed['scheme'], $parsed['user'], $parsed['host'], $parsed['path'])) {
            throw new InvalidArgumentException("Invalid Oopsy DSN: {$dsn}");
        }

        // Extract project ID from path: /api/v1/projects/{id}
        if (! preg_match('#/api/v1/projects/(\d+)$#', $parsed['path'], $matches)) {
            throw new InvalidArgumentException("Invalid Oopsy DSN path: {$parsed['path']}");
        }

        return new self(
            scheme: $parsed['scheme'],
            key: $parsed['user'],
            host: $parsed['host'],
            port: $parsed['port'] ?? null,
            projectId: $matches[1],
        );
    }

    public function getIngestUrl(): string
    {
        $port = $this->port ? ":{$this->port}" : '';

        return "{$this->scheme}://{$this->host}{$port}/api/v1/envelope";
    }
}
