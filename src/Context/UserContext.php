<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Context;

use Illuminate\Support\Facades\Auth;

class UserContext
{
    public function collect(bool $sendPii): array
    {
        if (! Auth::check()) {
            return [];
        }

        $user = Auth::user();

        $context = [
            'id' => $user->getAuthIdentifier(),
        ];

        if ($sendPii) {
            $context['email'] = $user->email ?? null;
            $context['name'] = $user->name ?? null;
        }

        return $context;
    }
}
