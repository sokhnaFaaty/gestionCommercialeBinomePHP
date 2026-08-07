<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!isConnected()) {
            redirectTo('auth', 'login');
        }
    }
}