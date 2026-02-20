<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OopsyClient
{
    private string $key;

    private string $ingestUrl;

    public function __construct(string $key, string $baseUrl)
    {
        $this->key = $key;
        $this->ingestUrl = rtrim($baseUrl, '/') . '/api/v1/envelope';
    }

    public function send(array $payload): void
    {
        try {
            Http::async()
                ->withHeaders([
                    'X-Oopsy-Auth' => "Oopsy oopsy_key={$this->key}",
                    'Content-Type' => 'application/json',
                ])
                ->timeout(5)
                ->post($this->ingestUrl, $payload);
        } catch (\Throwable $e) {
            // Never let the SDK crash the monitored app
            Log::debug("Oopsy SDK: Failed to send event - {$e->getMessage()}");
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
