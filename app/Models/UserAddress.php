<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: UserAddress (user_addresses)
 */
class UserAddress extends Model
{
    protected string $table = 'user_addresses';
    
    protected array $fillable = [
        'user_id',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'is_default'
    ];

    /**
     * Buscar endereços do usuário
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Buscar endereço padrão do usuário
     */
    public function getDefaultByUser(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_default = 1 LIMIT 1";
        $result = $this->query($sql, ['user_id' => $userId]);
        return $result[0] ?? null;
    }

    /**
     * Definir como endereço padrão
     */
    public function setAsDefault(int $addressId, int $userId): bool
    {
        // Remover padrão de outros
        $this->execute(
            "UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        // Definir novo padrão
        return $this->update($addressId, ['is_default' => true]);
    }
}
