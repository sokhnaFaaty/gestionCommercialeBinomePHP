<?php
namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $tokenEnvoye  = $_POST['csrf_token'] ?? '';
        $tokenAttendu = $_SESSION['csrf_token'] ?? '';

        if ($tokenAttendu === '' || !hash_equals($tokenAttendu, $tokenEnvoye)) {
            http_response_code(419);
            exit('<h1>419</h1><p>Session expirée ou formulaire invalide. Merci de réessayer.</p>');
        }
    }
}