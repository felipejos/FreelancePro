<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: EmailConfig (email_configs)
 */
class EmailConfig extends Model
{
    protected string $table = 'email_configs';
    
    protected array $fillable = [
        'mail_driver',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_address',
        'from_name',
        'is_active'
    ];

    protected array $hidden = ['smtp_password'];

    /**
     * Buscar configuração ativa
     */
    public function getActive(): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 LIMIT 1";
        $result = $this->query($sql);
        return $result[0] ?? null;
    }

    /**
     * Buscar configuração completa (incluindo senha)
     */
    public function getActiveWithPassword(): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 LIMIT 1";
        $stmt = self::getConnection()->query($sql);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ativar configuração
     */
    public function activate(int $configId): bool
    {
        // Desativar todas
        $this->execute("UPDATE {$this->table} SET is_active = 0");
        
        // Ativar a selecionada
        return $this->update($configId, ['is_active' => true]);
    }

    /**
     * Salvar ou atualizar configuração única
     */
    public function saveConfig(array $data): bool
    {
        $existing = $this->getActive();
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        
        $data['is_active'] = true;
        return $this->create($data) !== null;
    }
}
