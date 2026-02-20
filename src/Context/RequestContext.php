<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Context;

use Illuminate\Http\Request;

class RequestContext
{
    private static array $sensitiveHeaders = [
        'authorization',
        'cookie',
        'x-csrf-token',
        'x-xsrf-token',
    ];

    public function collect(Request $request): array
    {
        return [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'query_string' => $request->query() ?: null,
            'body_size' => $request->header('Content-Length'),
        ];
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $key => $values) {
            if (in_array(strtolower($key), self::$sensitiveHeaders)) {
                $sanitized[$key] = ['[filtered]'];
            } else {
                $sanitized[$key] = $values;
            }
        }

        return $sanitized;
    }
}
