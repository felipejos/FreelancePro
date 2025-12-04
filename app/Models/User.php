<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: User (user_profiles)
 */
class User extends Model
{
    protected string $table = 'user_profiles';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'cpf',
        'birth_date',
        'avatar',
        'company_id',
        'status',
        'email_verified_at',
        'remember_token',
        'reset_token',
        'reset_token_expires',
        'last_login'
    ];
    
    protected array $hidden = ['password', 'remember_token', 'reset_token'];

    /**
     * Buscar por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Criar usuário com senha hash
     */
    public function createUser(array $data): ?int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }

    /**
     * Verificar senha
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Atualizar senha
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hash]);
    }

    /**
     * Gerar token de reset de senha
     */
    public function generateResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->execute(
            "UPDATE {$this->table} SET reset_token = :token, reset_token_expires = :expires WHERE id = :id",
            ['token' => $token, 'expires' => $expires, 'id' => $userId]
        );
        
        return $token;
    }

    /**
     * Verificar token de reset
     */
    public function verifyResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Limpar token de reset
     */
    public function clearResetToken(int $userId): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id",
            ['id' => $userId]
        );
    }

    /**
     * Atualizar último login
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id",
            ['id' => $userId]
        );
    }

    /**
     * Buscar funcionários de uma empresa
     */
    public function getEmployeesByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE company_id = :company_id AND user_type = 'employee'";
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Buscar por tipo
     */
    public function getByType(string $type): array
    {
        return $this->where('user_type', $type);
    }

    /**
     * Contar por tipo
     */
    public function countByType(string $type): int
    {
        return $this->countWhere('user_type', $type);
    }

    /**
     * Buscar usuários ativos
     */
    public function getActive(): array
    {
        return $this->where('status', 'active');
    }
}
