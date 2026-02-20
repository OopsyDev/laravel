<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Oopsy\Laravel\Context\ContextCollector;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestData
{
    public function handle(Request $request, Closure $next): Response
    {
        $collector = new ContextCollector;

        // Store request data in the container for later use by the exception handler
        app()->instance('oopsy.request_context', $collector->collectRequest($request));

        return $next($request);
    }
}
