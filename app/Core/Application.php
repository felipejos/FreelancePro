<?php

namespace App\Core;

/**
 * Application - Classe principal da aplicação
 */
class Application
{
    protected Router $router;
    protected static ?Application $instance = null;
    protected array $config;

    public function __construct()
    {
        self::$instance = $this;
        
        // Carregar configurações
        $this->config = require ROOT_PATH . '/config/app.php';
        
        // Configurar timezone
        date_default_timezone_set($this->config['timezone']);
        
        // Configurar exibição de erros
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        // Inicializar router
        $this->router = new Router();
    }

    /**
     * Obter instância da aplicação
     */
    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    /**
     * Obter router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Obter configuração
     */
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? null;
    }

    /**
     * Executar aplicação
     */
    public function run(): void
    {
        try {
            $url = $_GET['url'] ?? '';
            $url = rtrim($url, '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            
            $this->router->dispatch($url);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Tratar exceções
     */
    protected function handleException(\Exception $e): void
    {
        if ($this->config['debug']) {
            echo '<h1>Erro</h1>';
            echo '<p><strong>Mensagem:</strong> ' . $e->getMessage() . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . $e->getFile() . '</p>';
            echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            // Em produção, mostrar página de erro genérica
            http_response_code(500);
            include ROOT_PATH . '/app/Views/errors/500.php';
        }
    }
}
