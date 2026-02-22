<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Oopsy\Laravel\Breadcrumbs\BreadcrumbRecorder;
use Oopsy\Laravel\Commands\SetupCommand;
use Oopsy\Laravel\Commands\TestCommand;
use Oopsy\Laravel\Context\ContextCollector;
use Oopsy\Laravel\StackTrace\StackTraceBuilder;

class OopsyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/oopsy.php', 'oopsy');

        $this->app->singleton(BreadcrumbRecorder::class, fn () => new BreadcrumbRecorder);

        $this->app->singleton(OopsyClient::class, function () {
            $key = config('oopsy.key');

            if (! $key) {
                return null;
            }

            return new OopsyClient($key, config('oopsy.url', 'https://oopsy.dev'));
        });

        $this->app->singleton(EventBuilder::class, function () {
            return new EventBuilder(
                new StackTraceBuilder((int) config('oopsy.context_lines', 5)),
                new ContextCollector,
                $this->app->make(BreadcrumbRecorder::class),
            );
        });

        $this->app->singleton(ExceptionHandler::class, function () {
            $client = $this->app->make(OopsyClient::class);

            if (! $client) {
                return null;
            }

            return new ExceptionHandler(
                $client,
                $this->app->make(EventBuilder::class),
            );
        });

        if (! config('oopsy.enabled', true) || ! config('oopsy.key')) {
            return;
        }

        $this->registerExceptionReporter();
        $this->registerFlushHooks();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/oopsy.php' => config_path('oopsy.php'),
        ], 'oopsy-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupCommand::class,
                TestCommand::class,
            ]);
        }

        if (! config('oopsy.enabled', true) || ! config('oopsy.key')) {
            return;
        }

        $this->registerBreadcrumbs();
    }

    private function registerExceptionReporter(): void
    {
        $this->callAfterResolving(
            ExceptionHandlerContract::class,
            function (ExceptionHandlerContract $handler) {
                if (! $handler instanceof Handler) {
                    return;
                }

                $handler->reportable(function (\Throwable $e) {
                    $oopsyHandler = $this->app->make(ExceptionHandler::class);

                    if ($oopsyHandler) {
                        $oopsyHandler->handle($e);
                    }
                })->stop(false);
            }
        );
    }

    private function registerFlushHooks(): void
    {
        // Flush after HTTP response is sent
        $this->callAfterResolving(
            HttpKernelContract::class,
            function (HttpKernelContract $kernel) {
                if ($kernel instanceof HttpKernel) {
                    $kernel->whenRequestLifecycleIsLongerThan(-1, fn () => $this->flushClient());
                }
            }
        );

        // Flush after CLI command completes
        $this->callAfterResolving(
            ConsoleKernelContract::class,
            function (ConsoleKernelContract $kernel) {
                if ($kernel instanceof ConsoleKernel) {
                    $kernel->whenCommandLifecycleIsLongerThan(-1, fn () => $this->flushClient());
                }
            }
        );

        // Flush between queue jobs and on worker shutdown
        Event::listen([Looping::class, WorkerStopping::class], fn () => $this->flushClient());
    }

    private function flushClient(): void
    {
        try {
            $client = $this->app->make(OopsyClient::class);

            if ($client) {
                $client->flush();
            }
        } catch (\Throwable) {
            // Never crash the monitored app
        }
    }

    private function registerBreadcrumbs(): void
    {
        $recorder = $this->app->make(BreadcrumbRecorder::class);

        // Log breadcrumbs
        if (config('oopsy.breadcrumbs.logs', true)) {
            Event::listen(MessageLogged::class, function (MessageLogged $event) use ($recorder) {
                $recorder->recordLog($event->level, $event->message, $event->context ?? []);
            });
        }

        // Query breadcrumbs
        if (config('oopsy.breadcrumbs.queries', true)) {
            DB::listen(function ($query) use ($recorder) {
                $recorder->recordQuery($query->sql, $query->time, $query->connectionName);
            });
        }
    }
}
