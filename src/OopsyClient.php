<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OopsyClient
{
    private string $key;

    private string $ingestUrl;

    private array $buffer = [];

    public function __construct(string $key, string $baseUrl)
    {
        $this->key = $key;
        $this->ingestUrl = rtrim($baseUrl, '/') . '/api/v1/envelope';
    }

    public function send(array $payload): void
    {
        $this->buffer[] = $payload;
    }

    public function flush(): void
    {
        $payloads = $this->buffer;
        $this->buffer = [];

        foreach ($payloads as $payload) {
            try {
                Http::withHeaders([
                    'X-Oopsy-Auth' => "Oopsy oopsy_key={$this->key}",
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(5)
                    ->post($this->ingestUrl, $payload);
            } catch (\Throwable $e) {
                Log::debug("Oopsy SDK: Failed to send event - {$e->getMessage()}");
            }
        }
    }

    public function sendSync(array $payload): void
    {
        $response = Http::withHeaders([
            'X-Oopsy-Auth' => "Oopsy oopsy_key={$this->key}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(10)
            ->post($this->ingestUrl, $payload);

        $response->throw();
    }
}
