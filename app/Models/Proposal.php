<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Proposal (proposals)
 */
class Proposal extends Model
{
    protected string $table = 'proposals';
    
    protected array $fillable = [
        'project_id',
        'professional_id',
        'cover_letter',
        'proposed_value',
        'estimated_days',
        'status',
        'negotiation_status'
    ];

    /**
     * Buscar propostas do projeto
     */
    public function getByProject(int $projectId): array
    {
        $sql = "SELECT p.*, u.name as professional_name, u.email as professional_email,
                       (SELECT AVG(rating) FROM reviews WHERE reviewed_id = p.professional_id) as avg_rating
                FROM {$this->table} p
                JOIN user_profiles u ON p.professional_id = u.id
                WHERE p.project_id = :project_id
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, ['project_id' => $projectId]);
    }

    /**
     * Buscar propostas do profissional
     */
    public function getByProfessional(int $professionalId): array
    {
        $sql = "SELECT p.*, pr.title as project_title, pr.status as project_status,
                       u.name as company_name
                FROM {$this->table} p
                JOIN projects pr ON p.project_id = pr.id
                JOIN user_profiles u ON pr.company_id = u.id
                WHERE p.professional_id = :professional_id
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, ['professional_id' => $professionalId]);
    }

    /**
     * Verificar se profissional já enviou proposta
     */
    public function hasSubmitted(int $projectId, int $professionalId): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE project_id = :project_id AND professional_id = :professional_id";
        
        $result = $this->query($sql, [
            'project_id' => $projectId,
            'professional_id' => $professionalId
        ]);
        
        return ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * Buscar proposta de um profissional em um projeto específico
     */
    public function getForProfessionalInProject(int $projectId, int $professionalId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE project_id = :project_id AND professional_id = :professional_id
                LIMIT 1";

        $result = $this->query($sql, [
            'project_id' => $projectId,
            'professional_id' => $professionalId,
        ]);

        return $result[0] ?? null;
    }

    /**
     * Aceitar proposta
     */
    public function accept(int $proposalId): bool
    {
        $proposal = $this->find($proposalId);
        
        if (!$proposal) {
            return false;
        }
        
        // Atualizar status da proposta
        $this->update($proposalId, ['status' => 'accepted_pending_payment']);
        
        // Rejeitar outras propostas do projeto
        $this->execute(
            "UPDATE {$this->table} SET status = 'rejected' 
             WHERE project_id = :project_id AND id != :id",
            ['project_id' => $proposal['project_id'], 'id' => $proposalId]
        );
        
        return true;
    }

    /**
     * Rejeitar proposta
     */
    public function reject(int $proposalId): bool
    {
        return $this->update($proposalId, ['status' => 'rejected']);
    }

    public function updateNegotiationStatus(int $proposalId, string $status): bool
    {
        return $this->update($proposalId, ['negotiation_status' => $status]);
    }

    public function markPaid(int $proposalId): bool
    {
        return $this->update($proposalId, [
            'status' => 'paid',
            'negotiation_status' => 'accepted'
        ]);
    }
}
