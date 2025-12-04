<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Review (reviews)
 */
class Review extends Model
{
    protected string $table = 'reviews';
    
    protected array $fillable = [
        'contract_id',
        'reviewer_id',
        'reviewed_id',
        'rating',
        'comment'
    ];

    /**
     * Buscar avaliações recebidas
     */
    public function getReceivedByUser(int $userId): array
    {
        $sql = "SELECT r.*, u.name as reviewer_name, pr.title as project_title
                FROM {$this->table} r
                JOIN user_profiles u ON r.reviewer_id = u.id
                JOIN contracts c ON r.contract_id = c.id
                JOIN projects pr ON c.project_id = pr.id
                WHERE r.reviewed_id = :user_id
                ORDER BY r.created_at DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Buscar avaliações feitas
     */
    public function getGivenByUser(int $userId): array
    {
        $sql = "SELECT r.*, u.name as reviewed_name, pr.title as project_title
                FROM {$this->table} r
                JOIN user_profiles u ON r.reviewed_id = u.id
                JOIN contracts c ON r.contract_id = c.id
                JOIN projects pr ON c.project_id = pr.id
                WHERE r.reviewer_id = :user_id
                ORDER BY r.created_at DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Calcular média de avaliações
     */
    public function getAverageRating(int $userId): float
    {
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE reviewed_id = :user_id";
        $result = $this->query($sql, ['user_id' => $userId]);
        return round($result[0]['avg_rating'] ?? 0, 1);
    }

    /**
     * Contar avaliações
     */
    public function countByUser(int $userId): int
    {
        return $this->countWhere('reviewed_id', $userId);
    }

    /**
     * Verificar se já avaliou o contrato
     */
    public function hasReviewed(int $contractId, int $reviewerId): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE contract_id = :contract_id AND reviewer_id = :reviewer_id";
        
        $result = $this->query($sql, [
            'contract_id' => $contractId,
            'reviewer_id' => $reviewerId
        ]);
        
        return ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * Buscar avaliação de um contrato feita por um usuário específico
     */
    public function getByContractAndReviewer(int $contractId, int $reviewerId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE contract_id = :contract_id AND reviewer_id = :reviewer_id
                LIMIT 1";

        $result = $this->query($sql, [
            'contract_id' => $contractId,
            'reviewer_id' => $reviewerId,
        ]);

        return $result[0] ?? null;
    }
}
