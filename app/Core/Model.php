<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Model - Classe base para models
 */
abstract class Model
{
    protected static ?PDO $connection = null;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];
    protected static array $columnsCache = [];

    /**
     * Obter conexão com banco de dados
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $config = require ROOT_PATH . '/config/database.php';
            $db = $config['connection'];
            
            $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
            
            try {
                self::$connection = new PDO($dsn, $db['username'], $db['password'], $db['options']);
            } catch (PDOException $e) {
                throw new \Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }

    /**
     * Buscar todos os registros
     */
    public function all(array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";
        
        $stmt = self::getConnection()->query($sql);
        return $this->hideFields($stmt->fetchAll());
    }

    /**
     * Buscar por ID
     */
    public function find($id, array $columns = ['*']): ?array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        return $result ? $this->hideFields([$result])[0] : null;
    }

    /**
     * Buscar por condição
     */
    public function where(string $column, $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['value' => $value]);
        
        return $this->hideFields($stmt->fetchAll());
    }

    /**
     * Buscar primeiro por condição
     */
    public function firstWhere(string $column, $value, string $operator = '='): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value LIMIT 1";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['value' => $value]);
        
        $result = $stmt->fetch();
        return $result ? $this->hideFields([$result])[0] : null;
    }

    /**
     * Inserir registro
     */
    public function create(array $data): ?int
    {
        // Filtrar apenas campos permitidos
        $data = $this->filterFillable($data);
        
        // Adicionar timestamps apenas se as colunas existirem
        $now = date('Y-m-d H:i:s');
        if ($this->hasColumn('created_at') && !isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if ($this->hasColumn('updated_at') && !isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = self::getConnection()->prepare($sql);
        
        if ($stmt->execute($data)) {
            return (int) self::getConnection()->lastInsertId();
        }
        
        return null;
    }

    /**
     * Atualizar registro
     */
    public function update($id, array $data): bool
    {
        // Filtrar apenas campos permitidos
        $data = $this->filterFillable($data);
        
        // Atualizar timestamp apenas se a coluna existir
        if ($this->hasColumn('updated_at') && !isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        $stmt = self::getConnection()->prepare($sql);
        
        return $stmt->execute($data);
    }

    /**
     * Deletar registro
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar registros
     */
    public function count(string $column = '*'): int
    {
        $sql = "SELECT COUNT({$column}) as total FROM {$this->table}";
        
        $stmt = self::getConnection()->query($sql);
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }

    /**
     * Contar com condição
     */
    public function countWhere(string $column, $value, string $operator = '='): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} {$operator} :value";
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['value' => $value]);
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }

    /**
     * Query personalizada
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Execute personalizado
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Filtrar campos permitidos
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Verificar se coluna existe na tabela (com cache)
     */
    protected function hasColumn(string $column): bool
    {
        if (!isset(self::$columnsCache[$this->table])) {
            $stmt = self::getConnection()->query("DESCRIBE {$this->table}");
            $fields = $stmt->fetchAll();
            self::$columnsCache[$this->table] = array_column($fields, 'Field');
        }
        
        return in_array($column, self::$columnsCache[$this->table], true);
    }

    /**
     * Esconder campos sensíveis
     */
    protected function hideFields(array $results): array
    {
        foreach ($results as &$row) {
            foreach ($this->hidden as $field) {
                unset($row[$field]);
            }
        }
        
        return $results;
    }

    /**
     * Iniciar transação
     */
    public function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Reverter transação
     */
    public function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
}
