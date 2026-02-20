<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Context;

class EnvironmentContext
{
    public function collect(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => PHP_OS,
            'server_name' => gethostname() ?: null,
            'sapi' => PHP_SAPI,
        ];
    }
}
