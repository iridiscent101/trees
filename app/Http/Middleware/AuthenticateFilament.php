<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate;

class AuthenticateFilament extends Authenticate
{
    protected function redirectTo(mixed $request): ?string
    {
        return route('login');
    }
}
