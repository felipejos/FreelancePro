<?php

namespace App\Models;

use App\Core\Model;

class ProposalCounteroffer extends Model
{
    protected string $table = 'proposal_counteroffers';

    protected array $fillable = [
        'proposal_id',
        'sender_id',
        'sender_type',
        'amount',
        'estimated_days',
        'message',
        'status',
        'responded_by',
        'responded_at',
    ];

    public function getByProposal(int $proposalId): array
    {
        $sql = "SELECT pc.*, u.name as sender_name, u2.name as responder_name
                FROM {$this->table} pc
                LEFT JOIN user_profiles u ON pc.sender_id = u.id
                LEFT JOIN user_profiles u2 ON pc.responded_by = u2.id
                WHERE pc.proposal_id = :proposal_id
                ORDER BY pc.created_at DESC";

        return $this->query($sql, ['proposal_id' => $proposalId]);
    }

    public function getPendingForProposal(int $proposalId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE proposal_id = :proposal_id AND status = 'pending'
                ORDER BY created_at DESC
                LIMIT 1";

        $result = $this->query($sql, ['proposal_id' => $proposalId]);
        return $result[0] ?? null;
    }

    public function createCounter(array $data): ?int
    {
        return $this->create($data);
    }

    public function markStatus(int $id, string $status, ?int $responderId = null): bool
    {
        $payload = [
            'status' => $status,
            'responded_at' => date('Y-m-d H:i:s'),
        ];

        if ($responderId) {
            $payload['responded_by'] = $responderId;
        }

        return $this->update($id, $payload);
    }
}
