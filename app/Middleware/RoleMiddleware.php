<?php
namespace App\Middleware;

class RoleMiddleware
{
    public function __construct(private string $roleAttendu)
    {
    }

    public function handle(): void
    {
        if (!isConnected()) {
            redirectTo('auth', 'login');
        }

        if (!hasRole($this->roleAttendu)) {
            if ($this->roleAttendu === ROLE_GESTIONNAIRE) {
                redirectTo('espace', 'index');
            } else {
                redirectTo('utilisateur', 'index');
            }
        }
    }
}