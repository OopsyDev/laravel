<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Throwable;

class ExceptionHandler
{
    private OopsyClient $client;

    private EventBuilder $eventBuilder;

    public ?string $reservedMemory;

    public function __construct(OopsyClient $client, EventBuilder $eventBuilder)
    {
        $this->client = $client;
        $this->eventBuilder = $eventBuilder;
        $this->reservedMemory = str_repeat('x', 32768);
    }

    public function handle(Throwable $exception): void
    {
        // Free reserved memory if Laravel's is already freed (OOM scenario)
        if (HandleExceptions::$reservedMemory === null) {
            $this->reservedMemory = null;
        }

        try {
            // Check if this exception should be ignored
            if ($this->shouldIgnore($exception)) {
                return;
            }

            // Check sample rate
            if (! $this->shouldSample()) {
                return;
            }

            $payload = $this->eventBuilder->build($exception);

            // Apply before_send callback
            $beforeSend = config('oopsy.before_send');

            if (is_callable($beforeSend)) {
                $payload = $beforeSend($payload);

                if ($payload === null) {
                    return;
                }
            }

            $this->client->send($payload);
        } catch (Throwable) {
            // Never crash the monitored app
        }
    }

    private function shouldIgnore(Throwable $exception): bool
    {
        $ignoredExceptions = config('oopsy.ignored_exceptions', []);

        foreach ($ignoredExceptions as $ignoredClass) {
            if ($exception instanceof $ignoredClass) {
                return true;
            }
        }

        return false;
    }

    private function shouldSample(): bool
    {
        $sampleRate = (float) config('oopsy.sample_rate', 1.0);

        if ($sampleRate >= 1.0) {
            return true;
        }

        if ($sampleRate <= 0.0) {
            return false;
        }

        return mt_rand(0, 100) / 100 <= $sampleRate;
    }
}
