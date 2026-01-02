<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Payment (payment_transactions)
 */
class Payment extends Model
{
    protected string $table = 'payment_transactions';
    
    protected array $fillable = [
        'user_id',
        'type',
        'reference_id',
        'amount',
        'description',
        'assas_payment_id',
        'assas_invoice_url',
        'payment_method',
        'status',
        'paid_at'
    ];

    /**
     * Buscar pagamentos do usuário
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Buscar por tipo
     */
    public function getByType(string $type): array
    {
        return $this->where('type', $type);
    }

    /**
     * Buscar por ID do ASSAS
     */
    public function findByAssasId(string $assasPaymentId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE assas_payment_id = :assas_id LIMIT 1";
        $result = $this->query($sql, ['assas_id' => $assasPaymentId]);
        return $result[0] ?? null;
    }

    /**
     * Atualizar status do pagamento
     */
    public function updateStatus(int $paymentId, string $status): bool
    {
        $data = ['status' => $status];
        
        if (in_array($status, ['confirmed', 'received'])) {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($paymentId, $data);
    }

    /**
     * Somar pagamentos por período
     */
    public function sumByPeriod(string $startDate, string $endDate, string $status = 'confirmed'): float
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} 
                WHERE status = :status 
                AND created_at BETWEEN :start AND :end";
        
        $result = $this->query($sql, [
            'status' => $status,
            'start' => $startDate,
            'end' => $endDate
        ]);
        
        return $result[0]['total'] ?? 0;
    }

    /**
     * Estatísticas de pagamentos
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'confirmed' OR status = 'received' THEN amount ELSE 0 END) as total_received,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
                    SUM(CASE WHEN type = 'subscription' THEN amount ELSE 0 END) as total_subscriptions,
                    SUM(CASE WHEN type = 'playbook' THEN amount ELSE 0 END) as total_playbooks,
                    SUM(CASE WHEN type = 'contract' THEN amount ELSE 0 END) as total_contracts
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? [];
    }

    public function findProposalPayment(int $proposalId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE type = 'proposal' AND reference_id = :reference_id
                ORDER BY created_at DESC
                LIMIT 1";

        $result = $this->query($sql, ['reference_id' => $proposalId]);
        return $result[0] ?? null;
    }
}
