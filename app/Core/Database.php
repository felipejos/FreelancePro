<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database - Classe de conexão com o banco de dados
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Obter instância de conexão (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require ROOT_PATH . '/config/database.php';
            $db = self::$config['connection'];
            
            $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
            
            try {
                self::$instance = new PDO($dsn, $db['username'], $db['password'], $db['options']);
            } catch (PDOException $e) {
                throw new \Exception("Erro de conexão com o banco de dados: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }

    /**
     * Obter ambiente atual
     */
    public static function getEnvironment(): string
    {
        if (empty(self::$config)) {
            self::$config = require ROOT_PATH . '/config/database.php';
        }
        
        return self::$config['environment'];
    }

    /**
     * Executar query
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Executar statement (INSERT, UPDATE, DELETE)
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getInstance()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obter último ID inserido
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Iniciar transação
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Reverter transação
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    /**
     * Fechar conexão
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
