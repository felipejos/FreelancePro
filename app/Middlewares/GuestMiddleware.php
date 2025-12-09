<?php

namespace App\Middlewares;

/**
 * GuestMiddleware - Verificar se usuário NÃO está autenticado
 */
class GuestMiddleware
{
    /**
     * Verificar se é visitante (não logado)
     */
    public function handle(): bool
    {
        // Só considera logado se existir $_SESSION['user']['id']
        if (isset($_SESSION['user']['id'])) {
            $userType = $_SESSION['user']['user_type'] ?? 'company';
            
            // Redirecionar para dashboard apropriado
            $redirects = [
                'admin'        => '/admin/dashboard',
                'company'      => '/dashboard',
                'professional' => '/professional/dashboard',
                'employee'     => '/employee/dashboard',
            ];
            
            $redirect = $redirects[$userType] ?? '/dashboard';
            header('Location: ' . $redirect);
            exit;
        }
        
        return true;
    }
}
