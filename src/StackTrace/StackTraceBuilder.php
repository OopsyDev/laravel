<?php

declare(strict_types=1);

namespace Oopsy\Laravel\StackTrace;

use Throwable;

class StackTraceBuilder
{
    private int $contextLines;

    public function __construct(int $contextLines = 5)
    {
        $this->contextLines = $contextLines;
    }

    /**
     * @return Frame[]
     */
    public function build(Throwable $exception): array
    {
        $frames = [];

        foreach ($exception->getTrace() as $trace) {
            $file = $trace['file'] ?? '[internal]';
            $line = $trace['line'] ?? 0;
            $function = $trace['function'] ?? null;
            $class = $trace['class'] ?? null;

            $inApp = $this->isInApp($file);
            $context = $this->getCodeContext($file, $line);

            $frames[] = new Frame(
                file: $file,
                line: $line,
                function: $function,
                class: $class,
                inApp: $inApp,
                context: $context,
            );
        }

        // Add the exception origin as the first frame
        array_unshift($frames, new Frame(
            file: $exception->getFile(),
            line: $exception->getLine(),
            function: null,
            class: null,
            inApp: $this->isInApp($exception->getFile()),
            context: $this->getCodeContext($exception->getFile(), $exception->getLine()),
        ));

        return $frames;
    }

    private function isInApp(string $file): bool
    {
        if ($file === '[internal]') {
            return false;
        }

        // Not in app if in vendor directory
        if (str_contains($file, '/vendor/')) {
            return false;
        }

        return true;
    }

    private function getCodeContext(string $file, int $line): ?array
    {
        if ($file === '[internal]' || $line <= 0 || ! is_file($file) || ! is_readable($file)) {
            return null;
        }

        try {
            $lines = file($file);

            if ($lines === false) {
                return null;
            }

            $start = max(0, $line - $this->contextLines - 1);
            $end = min(count($lines), $line + $this->contextLines);

            $context = [];

            for ($i = $start; $i < $end; $i++) {
                $context[$i + 1] = rtrim($lines[$i]);
            }

            return $context;
        } catch (Throwable) {
            return null;
        }
    }
}
