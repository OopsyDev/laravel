<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Context;

use Illuminate\Http\Request;

class ContextCollector
{
    private RequestContext $requestContext;

    private UserContext $userContext;

    private EnvironmentContext $environmentContext;

    public function __construct()
    {
        $this->requestContext = new RequestContext;
        $this->userContext = new UserContext;
        $this->environmentContext = new EnvironmentContext;
    }

    public function collectRequest(Request $request): array
    {
        return $this->requestContext->collect($request);
    }

    public function collectUser(bool $sendPii): array
    {
        return $this->userContext->collect($sendPii);
    }

    public function collectEnvironment(): array
    {
        return $this->environmentContext->collect();
    }
}
