<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: AILog (ai_logs)
 */
class AILog extends Model
{
    protected string $table = 'ai_logs';
    
    protected array $fillable = [
        'user_id',
        'action',
        'input_text',
        'output_text',
        'tokens_used',
        'model',
        'cost',
        'status',
        'error_message'
    ];

    /**
     * Registrar log de sucesso
     */
    public function logSuccess(int $userId, string $action, string $input, string $output, int $tokens = 0, string $model = 'gpt-4'): int
    {
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'input_text' => $input,
            'output_text' => $output,
            'tokens_used' => $tokens,
            'model' => $model,
            'cost' => $this->calculateCost($tokens, $model),
            'status' => 'success'
        ]);
    }

    /**
     * Registrar log de erro
     */
    public function logError(int $userId, string $action, string $input, string $errorMessage): int
    {
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'input_text' => $input,
            'status' => 'error',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Buscar logs do usuário
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT {$limit}";
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Buscar logs por ação
     */
    public function getByAction(string $action): array
    {
        return $this->where('action', $action);
    }

    /**
     * Estatísticas de uso
     */
    public function getStats(int $userId = null): array
    {
        $where = $userId ? "WHERE user_id = :user_id" : "";
        $params = $userId ? ['user_id' => $userId] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(tokens_used) as total_tokens,
                    SUM(cost) as total_cost,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count
                FROM {$this->table} {$where}";
        
        $result = $this->query($sql, $params);
        return $result[0] ?? [];
    }

    /**
     * Calcular custo estimado
     */
    protected function calculateCost(int $tokens, string $model): float
    {
        // Preços aproximados por 1000 tokens
        $prices = [
            'gpt-4' => 0.03,
            'gpt-4-turbo' => 0.01,
            'gpt-3.5-turbo' => 0.002,
        ];
        
        $pricePerK = $prices[$model] ?? 0.01;
        return ($tokens / 1000) * $pricePerK;
    }
}
