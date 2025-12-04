<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Contract (contracts)
 */
class Contract extends Model
{
    protected string $table = 'contracts';
    
    protected array $fillable = [
        'project_id',
        'proposal_id',
        'company_id',
        'professional_id',
        'contract_value',
        'platform_fee',
        'professional_amount',
        'status',
        'started_at',
        'completed_at'
    ];

    /**
     * Criar contrato a partir da proposta
     */
    public function createFromProposal(int $proposalId): ?int
    {
        // Buscar proposta e projeto
        $sql = "SELECT p.*, pr.company_id
                FROM proposals p
                JOIN projects pr ON p.project_id = pr.id
                WHERE p.id = :id";
        
        $result = $this->query($sql, ['id' => $proposalId]);
        $proposal = $result[0] ?? null;
        
        if (!$proposal) {
            return null;
        }
        
        // Calcular valores
        $contractValue = $proposal['proposed_value'];
        $platformFee = $contractValue * 0.07; // 7%
        $professionalAmount = $contractValue - $platformFee;
        
        return $this->create([
            'project_id' => $proposal['project_id'],
            'proposal_id' => $proposalId,
            'company_id' => $proposal['company_id'],
            'professional_id' => $proposal['professional_id'],
            'contract_value' => $contractValue,
            'platform_fee' => $platformFee,
            'professional_amount' => $professionalAmount,
            'status' => 'active'
        ]);
    }

    /**
     * Buscar contratos da empresa
     */
    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT c.*, pr.title as project_title, u.name as professional_name
                FROM {$this->table} c
                JOIN projects pr ON c.project_id = pr.id
                JOIN user_profiles u ON c.professional_id = u.id
                WHERE c.company_id = :company_id
                ORDER BY c.created_at DESC";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Buscar contratos do profissional
     */
    public function getByProfessional(int $professionalId): array
    {
        $sql = "SELECT c.*, pr.title as project_title, u.name as company_name
                FROM {$this->table} c
                JOIN projects pr ON c.project_id = pr.id
                JOIN user_profiles u ON c.company_id = u.id
                WHERE c.professional_id = :professional_id
                ORDER BY c.created_at DESC";
        
        return $this->query($sql, ['professional_id' => $professionalId]);
    }

    /**
     * Completar contrato
     */
    public function complete(int $contractId): bool
    {
        return $this->update($contractId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Buscar contrato com detalhes
     */
    public function getWithDetails(int $contractId): ?array
    {
        $sql = "SELECT c.*, 
                       pr.title as project_title, pr.description as project_description,
                       comp.name as company_name, comp.email as company_email,
                       prof.name as professional_name, prof.email as professional_email
                FROM {$this->table} c
                JOIN projects pr ON c.project_id = pr.id
                JOIN user_profiles comp ON c.company_id = comp.id
                JOIN user_profiles prof ON c.professional_id = prof.id
                WHERE c.id = :id";
        
        $result = $this->query($sql, ['id' => $contractId]);
        return $result[0] ?? null;
    }
}
