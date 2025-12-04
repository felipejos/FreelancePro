<?php
/**
 * Autoloader - Carregamento automático de classes
 */

class Autoloader
{
    /**
     * Registrar autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Carregar classe
     */
    public static function load(string $className): void
    {
        // Converter namespace para caminho
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        
        // Tentar carregar do diretório app
        $file = ROOT_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $className . '.php';
        
        // Remover 'App/' do início se existir
        if (strpos($className, 'App' . DIRECTORY_SEPARATOR) === 0) {
            $file = ROOT_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . substr($className, 4) . '.php';
        }
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
