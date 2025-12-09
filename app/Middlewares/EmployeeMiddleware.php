<?php

namespace App\Middlewares;

/**
 * EmployeeMiddleware - Verificar se é funcionário
 */
class EmployeeMiddleware
{
    /**
     * Verificar se usuário é funcionário
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /employee/login');
            exit;
        }
        
        if (($_SESSION['user']['user_type'] ?? '') !== 'employee') {
            header('Location: /employee/login');
            exit;
        }
        
        return true;
    }
}
