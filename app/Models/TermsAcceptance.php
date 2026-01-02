<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: TermsAcceptance (terms_acceptances)
 */
class TermsAcceptance extends Model
{
    protected string $table = 'terms_acceptances';
    
    protected array $fillable = [
        'user_id',
        'terms_version',
        'ip_address',
        'user_agent'
    ];

    /**
     * Registrar aceite de termos
     */
    public function recordAcceptance(int $userId, string $version = '1.0'): ?int
    {
        return $this->create([
            'user_id' => $userId,
            'terms_version' => $version,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * Verificar se usuário aceitou versão atual
     */
    public function hasAccepted(int $userId, string $version = '1.0'): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id AND terms_version = :version";
        
        $result = $this->query($sql, [
            'user_id' => $userId,
            'version' => $version,
        ]);
        
        return ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * Buscar último aceite do usuário
     */
    public function getLatest(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY accepted_at DESC 
                LIMIT 1";
        
        $result = $this->query($sql, ['user_id' => $userId]);
        return $result[0] ?? null;
    }
}
