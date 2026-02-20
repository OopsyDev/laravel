<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OopsyClient
{
    private Dsn $dsn;

    public function __construct(Dsn $dsn)
    {
        $this->dsn = $dsn;
    }

    public function send(array $payload): void
    {
        try {
            Http::async()
                ->withHeaders([
                    'X-Oopsy-Auth' => "Oopsy oopsy_key={$this->dsn->key}",
                    'Content-Type' => 'application/json',
                ])
                ->timeout(5)
                ->post($this->dsn->getIngestUrl(), $payload);
        } catch (\Throwable $e) {
            // Never let the SDK crash the monitored app
            Log::debug("Oopsy SDK: Failed to send event - {$e->getMessage()}");
        }
    }

    public function sendSync(array $payload): void
    {
        $response = Http::withHeaders([
            'X-Oopsy-Auth' => "Oopsy oopsy_key={$this->dsn->key}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(10)
            ->post($this->dsn->getIngestUrl(), $payload);

        $response->throw();
    }
}
