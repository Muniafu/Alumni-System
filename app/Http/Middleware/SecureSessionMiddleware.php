<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\StartSession;

class SecureSessionMiddleware extends StartSession
{
    protected function configureSecureCookie($config)
    {
        return [
            'path' => '/',
            'domain' => config('session.domain'),
            'secure' => true,
            'http_only' => true,
            'same_site' => 'strict',
        ];
    }
}