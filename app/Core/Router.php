<?php

namespace App\Core;

/**
 * Router - Sistema de rotas
 */
class Router
{
    protected array $routes = [];
    protected array $middlewares = [];

    /**
     * Adicionar rota GET
     */
    public function get(string $path, $callback, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $callback, $middlewares);
    }

    /**
     * Adicionar rota POST
     */
    public function post(string $path, $callback, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $callback, $middlewares);
    }

    /**
     * Adicionar rota PUT
     */
    public function put(string $path, $callback, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $path, $callback, $middlewares);
    }

    /**
     * Adicionar rota DELETE
     */
    public function delete(string $path, $callback, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $path, $callback, $middlewares);
    }

    /**
     * Adicionar rota
     */
    protected function addRoute(string $method, string $path, $callback, array $middlewares = []): self
    {
        $this->routes[$method][$path] = [
            'callback'    => $callback,
            'middlewares' => $middlewares,
        ];
        
        return $this;
    }

    /**
     * Agrupar rotas com prefixo
     */
    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousRoutes = $this->routes;
        $this->routes = [];
        
        $callback($this);
        
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $path => $route) {
                $newPath = $prefix . '/' . ltrim($path, '/');
                $route['middlewares'] = array_merge($middlewares, $route['middlewares']);
                $previousRoutes[$method][$newPath] = $route;
            }
        }
        
        $this->routes = $previousRoutes;
    }

    /**
     * Despachar rota
     */
    public function dispatch(string $url): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Suporte para método PUT/DELETE via POST
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Procurar rota correspondente
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $path => $route) {
                $pattern = $this->convertToRegex($path);
                
                if (preg_match($pattern, $url, $matches)) {
                    // Remover o match completo
                    array_shift($matches);
                    
                    // Executar middlewares
                    foreach ($route['middlewares'] as $middleware) {
                        $middlewareClass = "App\\Middlewares\\{$middleware}";
                        if (class_exists($middlewareClass)) {
                            $middlewareInstance = new $middlewareClass();
                            if (!$middlewareInstance->handle()) {
                                return;
                            }
                        }
                    }
                    
                    // Executar callback
                    $this->executeCallback($route['callback'], $matches);
                    return;
                }
            }
        }

        // Rota não encontrada
        $this->notFound();
    }

    /**
     * Converter path para regex
     */
    protected function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Executar callback da rota
     */
    protected function executeCallback($callback, array $params): void
    {
        if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        } elseif (is_string($callback)) {
            // Formato: Controller@method
            [$controller, $method] = explode('@', $callback);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                throw new \Exception("Controller {$controllerClass} não encontrado");
            }
        }
    }

    /**
     * Página não encontrada
     */
    protected function notFound(): void
    {
        http_response_code(404);
        include ROOT_PATH . '/app/Views/errors/404.php';
    }
}
