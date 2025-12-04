<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: ProjectMessage (project_messages)
 */
class ProjectMessage extends Model
{
    protected string $table = 'project_messages';

    protected array $fillable = [
        'project_id',
        'proposal_id',
        'sender_id',
        'message',
    ];

    /**
     * Buscar mensagens de um projeto/proposta (empresa x freelancer)
     */
    public function getByProjectAndProposal(int $projectId, int $proposalId): array
    {
        $sql = "SELECT m.*, u.name as sender_name
                FROM {$this->table} m
                JOIN user_profiles u ON m.sender_id = u.id
                WHERE m.project_id = :project_id AND m.proposal_id = :proposal_id
                ORDER BY m.created_at ASC";

        return $this->query($sql, [
            'project_id' => $projectId,
            'proposal_id' => $proposalId,
        ]);
    }
}
