<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Oopsy\Laravel\Breadcrumbs\BreadcrumbRecorder;
use Oopsy\Laravel\Context\ContextCollector;
use Oopsy\Laravel\StackTrace\StackTraceBuilder;
use Throwable;

class EventBuilder
{
    private StackTraceBuilder $stackTraceBuilder;

    private ContextCollector $contextCollector;

    private BreadcrumbRecorder $breadcrumbRecorder;

    public function __construct(
        StackTraceBuilder $stackTraceBuilder,
        ContextCollector $contextCollector,
        BreadcrumbRecorder $breadcrumbRecorder,
    ) {
        $this->stackTraceBuilder = $stackTraceBuilder;
        $this->contextCollector = $contextCollector;
        $this->breadcrumbRecorder = $breadcrumbRecorder;
    }

    public function build(Throwable $exception): array
    {
        $frames = $this->stackTraceBuilder->build($exception);
        $sendPii = config('oopsy.send_default_pii', false);

        $payload = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'severity' => $this->determineSeverity($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => array_map(fn ($frame) => $frame->toArray(), $frames),
            'environment_data' => $this->contextCollector->collectEnvironment(),
            'user_context' => $this->contextCollector->collectUser($sendPii),
            'breadcrumbs' => $this->breadcrumbRecorder->flush(),
            'environment' => config('oopsy.environment', 'production'),
            'occurred_at' => now()->toIso8601String(),
        ];

        // Add request context if available
        if (app()->bound('oopsy.request_context')) {
            $payload['request_data'] = app('oopsy.request_context');
        }

        return $payload;
    }

    private function determineSeverity(Throwable $exception): string
    {
        if ($exception instanceof \Error) {
            return 'fatal';
        }

        return 'error';
    }
}
