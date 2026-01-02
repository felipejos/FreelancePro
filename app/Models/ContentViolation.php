<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: ContentViolation (content_violations)
 */
class ContentViolation extends Model
{
    protected string $table = 'content_violations';
    
    protected array $fillable = [
        'user_id',
        'context',
        'content',
        'violations_json',
        'status',
        'reviewed_by',
        'reviewed_at',
        'action_taken'
    ];

    /**
     * Buscar violações pendentes
     */
    public function getPending(): array
    {
        $sql = "SELECT cv.*, u.name as user_name, u.email as user_email
                FROM {$this->table} cv
                JOIN user_profiles u ON cv.user_id = u.id
                WHERE cv.status = 'pending'
                ORDER BY cv.created_at DESC";
        
        return $this->query($sql, []);
    }

    /**
     * Buscar por usuário
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Contar violações do usuário
     */
    public function countByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->query($sql, ['user_id' => $userId]);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Aprovar (descartar violação)
     */
    public function approve(int $id, int $adminId): bool
    {
        return $this->update($id, [
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'action_taken' => 'approved',
        ]);
    }

    /**
     * Rejeitar e penalizar
     */
    public function reject(int $id, int $adminId, string $action = 'warning'): bool
    {
        return $this->update($id, [
            'status' => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'action_taken' => $action,
        ]);
    }
}
