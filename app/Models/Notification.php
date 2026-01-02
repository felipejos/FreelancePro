<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Notification (notifications)
 */
class Notification extends Model
{
    protected string $table = 'notifications';
    
    protected array $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'reference_id',
        'is_read'
    ];

    /**
     * Buscar notificações não lidas do usuário
     */
    public function getUnreadByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT 50";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Buscar todas as notificações do usuário
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Contar não lidas
     */
    public function countUnread(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0";
        
        $result = $this->query($sql, ['user_id' => $userId]);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Marcar todas como lidas
     */
    public function markAllRead(int $userId): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0",
            ['user_id' => $userId]
        );
    }
}
