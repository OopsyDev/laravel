<?php

declare(strict_types=1);

namespace Oopsy\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Log\Events\MessageLogged;
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
            $dsnString = config('oopsy.dsn');

            if (! $dsnString) {
                return null;
            }

            return new OopsyClient(Dsn::parse($dsnString));
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

        if (! config('oopsy.enabled', true) || ! config('oopsy.dsn')) {
            return;
        }

        $this->registerExceptionReporter();
        $this->registerBreadcrumbs();
    }

    private function registerExceptionReporter(): void
    {
        $handler = $this->app->make(ExceptionHandlerContract::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable(function (\Throwable $e) {
                $oopsyHandler = $this->app->make(ExceptionHandler::class);

                if ($oopsyHandler) {
                    $oopsyHandler->handle($e);
                }
            })->stop(false);
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
