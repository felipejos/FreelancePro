<?php

namespace App\Middlewares;

/**
 * ProfessionalMiddleware - Verificar se é profissional/freelancer
 */
class ProfessionalMiddleware
{
    /**
     * Verificar se usuário é profissional
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        if (($_SESSION['user']['user_type'] ?? '') !== 'professional') {
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}
