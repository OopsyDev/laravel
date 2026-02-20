<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Commands;

use Illuminate\Console\Command;
use Oopsy\Laravel\EventBuilder;
use Oopsy\Laravel\OopsyClient;

class TestCommand extends Command
{
    protected $signature = 'oopsy:test';

    protected $description = 'Send a test exception to verify your Oopsy integration';

    public function handle(EventBuilder $eventBuilder): int
    {
        $client = app(OopsyClient::class);

        if (! $client) {
            $this->components->error('Oopsy is not configured. Run php artisan oopsy:setup first.');

            return self::FAILURE;
        }

        $this->info('Sending test exception to Oopsy...');
        $this->newLine();

        try {
            $exception = new \RuntimeException('This is a test exception from oopsy:test');
            $payload = $eventBuilder->build($exception);

            $client->sendSync($payload);

            $this->components->info('Test exception sent successfully!');
            $this->line('Check your Oopsy dashboard — the error should appear within a few seconds.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->error("Failed to send test exception: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
