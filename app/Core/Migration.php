<?php

namespace App\Core;

/**
 * Migration - Sistema de migrações de banco de dados
 */
class Migration
{
    protected string $migrationsPath;
    protected string $migrationsTable = 'migrations';

    public function __construct()
    {
        $config = require ROOT_PATH . '/config/database.php';
        $this->migrationsPath = $config['migrations']['path'];
        $this->migrationsTable = $config['migrations']['table'];
    }

    /**
     * Executar todas as migrations pendentes
     */
    public function runPending(): array
    {
        $executed = [];
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            return ['message' => 'Nenhuma migration pendente.', 'executed' => []];
        }

        $batch = $this->getNextBatch();

        foreach ($pending as $migration) {
            $this->runMigration($migration, $batch);
            $executed[] = $migration;
        }

        return [
            'message' => count($executed) . ' migration(s) executada(s).',
            'executed' => $executed
        ];
    }

    /**
     * Reverter última batch de migrations
     */
    public function rollback(): array
    {
        $reverted = [];
        $lastBatch = $this->getLastBatch();
        
        if ($lastBatch === 0) {
            return ['message' => 'Nenhuma migration para reverter.', 'reverted' => []];
        }

        $migrations = $this->getMigrationsByBatch($lastBatch);
        
        foreach (array_reverse($migrations) as $migration) {
            $this->revertMigration($migration);
            $reverted[] = $migration;
        }

        return [
            'message' => count($reverted) . ' migration(s) revertida(s).',
            'reverted' => $reverted
        ];
    }

    /**
     * Criar nova migration
     */
    public function create(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_' . $this->snakeCase($name) . '.sql';
        $filepath = $this->migrationsPath . '/' . $filename;

        // Criar diretório se não existir
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }

        // Template da migration
        $template = "-- ============================================\n";
        $template .= "-- Migration: {$name}\n";
        $template .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
        $template .= "-- ============================================\n\n";
        $template .= "-- UP: Alterações a serem aplicadas\n";
        $template .= "-- Cole seus comandos SQL aqui\n\n";
        $template .= "-- Exemplo:\n";
        $template .= "-- ALTER TABLE `tabela` ADD COLUMN `nova_coluna` VARCHAR(255) NULL;\n\n";
        $template .= "-- ============================================\n";
        $template .= "-- IMPORTANTE: Este arquivo deve ser executado manualmente no banco\n";
        $template .= "-- Após executar, rode: php migrate.php mark {$filename}\n";
        $template .= "-- ============================================\n";

        file_put_contents($filepath, $template);

        return $filename;
    }

    /**
     * Marcar migration como executada (após rodar manualmente)
     */
    public function mark(string $migration): bool
    {
        $batch = $this->getNextBatch();
        
        $sql = "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (:migration, :batch)";
        return Database::execute($sql, [
            'migration' => $migration,
            'batch' => $batch
        ]);
    }

    /**
     * Obter migrations pendentes
     */
    protected function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Obter todos os arquivos de migration
     */
    protected function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.sql');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file);
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Obter migrations já executadas
     */
    protected function getExecutedMigrations(): array
    {
        try {
            $sql = "SELECT migration FROM {$this->migrationsTable}";
            $results = Database::query($sql);
            return array_column($results, 'migration');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter próximo número de batch
     */
    protected function getNextBatch(): int
    {
        try {
            $sql = "SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}";
            $result = Database::query($sql);
            return ($result[0]['max_batch'] ?? 0) + 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Obter último batch
     */
    protected function getLastBatch(): int
    {
        try {
            $sql = "SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}";
            $result = Database::query($sql);
            return $result[0]['max_batch'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obter migrations por batch
     */
    protected function getMigrationsByBatch(int $batch): array
    {
        $sql = "SELECT migration FROM {$this->migrationsTable} WHERE batch = :batch";
        $results = Database::query($sql, ['batch' => $batch]);
        return array_column($results, 'migration');
    }

    /**
     * Executar migration
     */
    protected function runMigration(string $migration, int $batch): void
    {
        $filepath = $this->migrationsPath . '/' . $migration;
        
        if (!file_exists($filepath)) {
            throw new \Exception("Arquivo de migration não encontrado: {$migration}");
        }

        // Registrar migration como executada
        $sql = "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (:migration, :batch)";
        Database::execute($sql, [
            'migration' => $migration,
            'batch' => $batch
        ]);
    }

    /**
     * Reverter migration
     */
    protected function revertMigration(string $migration): void
    {
        $sql = "DELETE FROM {$this->migrationsTable} WHERE migration = :migration";
        Database::execute($sql, ['migration' => $migration]);
    }

    /**
     * Converter para snake_case
     */
    protected function snakeCase(string $string): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $string));
    }

    /**
     * Listar todas as migrations
     */
    public function status(): array
    {
        $all = $this->getAllMigrationFiles();
        $executed = $this->getExecutedMigrations();
        
        $status = [];
        foreach ($all as $migration) {
            $status[] = [
                'migration' => $migration,
                'status' => in_array($migration, $executed) ? 'Executada' : 'Pendente'
            ];
        }
        
        return $status;
    }
}
