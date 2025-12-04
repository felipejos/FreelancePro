<?php

namespace App\Core;

/**
 * Controller - Classe base para controllers
 */
abstract class Controller
{
    protected array $data = [];

    /**
     * Renderizar view
     */
    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        // Extrair dados para variáveis
        extract($this->data);
        
        // Caminho da view
        $viewPath = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            // Buffer de saída para layout
            ob_start();
            include $viewPath;
            $content = ob_get_clean();
            
            // Se tiver layout definido
            if (isset($this->data['layout'])) {
                $layoutPath = ROOT_PATH . '/app/Views/layouts/' . $this->data['layout'] . '.php';
                if (file_exists($layoutPath)) {
                    include $layoutPath;
                } else {
                    echo $content;
                }
            } else {
                echo $content;
            }
        } else {
            throw new \Exception("View {$view} não encontrada");
        }
    }

    /**
     * Definir layout
     */
    protected function setLayout(string $layout): void
    {
        $this->data['layout'] = $layout;
    }

    /**
     * Redirecionar
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $this->url($url));
        exit;
    }

    /**
     * Gerar URL
     */
    protected function url(string $path = ''): string
    {
        $config = require ROOT_PATH . '/config/app.php';
        return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
    }

    /**
     * Retornar JSON
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Obter dados POST
     */
    protected function input(string $key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        
        // Verificar JSON no body
        $jsonData = json_decode(file_get_contents('php://input'), true);
        if (is_array($jsonData)) {
            $data = array_merge($data, $jsonData);
        }
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }

    /**
     * Validar CSRF Token
     */
    protected function validateCsrf(): bool
    {
        $token = $this->input('_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Gerar CSRF Token
     */
    protected function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verificar se usuário está autenticado
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Obter usuário atual
     */
    protected function currentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $_SESSION['user'] ?? null;
    }

    /**
     * Verificar tipo de usuário
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->currentUser();
        return $user && ($user['user_type'] ?? '') === $role;
    }

    /**
     * Definir mensagem flash
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obter mensagem flash
     */
    protected function getFlash(string $type = null): ?string
    {
        if ($type === null) {
            $flash = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
