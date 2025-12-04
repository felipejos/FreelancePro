<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Project (projects)
 */
class Project extends Model
{
    protected string $table = 'projects';
    
    protected array $fillable = [
        'company_id',
        'title',
        'description',
        'category',
        'skills_required',
        'budget_min',
        'budget_max',
        'deadline',
        'status',
        'selected_proposal_id'
    ];

    /**
     * Buscar projetos da empresa
     */
    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM proposals WHERE project_id = p.id) as proposals_count
                FROM {$this->table} p
                WHERE p.company_id = :company_id
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Buscar projetos abertos (com filtros opcionais)
     * Filtros aceitos:
     *  - search         => texto (title/description)
     *  - budget_min     => valor mínimo
     *  - budget_max     => valor máximo
     *  - deadline_from  => data mínima de prazo (YYYY-MM-DD)
     *  - deadline_to    => data máxima de prazo (YYYY-MM-DD)
     *  - min_proposals  => mínimo de propostas
     *  - max_proposals  => máximo de propostas
     */
    public function getOpen(array $filters = []): array
    {
        $sql = "SELECT p.*, u.name as company_name,
                       (SELECT COUNT(*) FROM proposals WHERE project_id = p.id) as proposals_count
                FROM {$this->table} p
                JOIN user_profiles u ON p.company_id = u.id";

        $where = ["p.status = 'open'"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE :search_title OR p.description LIKE :search_desc)';
            $value = '%' . $filters['search'] . '%';
            $params['search_title'] = $value;
            $params['search_desc'] = $value;
        }

        if (isset($filters['budget_min']) && $filters['budget_min'] !== null && $filters['budget_min'] !== '') {
            $where[] = 'p.budget_min >= :budget_min';
            $params['budget_min'] = (float) $filters['budget_min'];
        }

        if (isset($filters['budget_max']) && $filters['budget_max'] !== null && $filters['budget_max'] !== '') {
            $where[] = 'p.budget_max <= :budget_max';
            $params['budget_max'] = (float) $filters['budget_max'];
        }

        if (!empty($filters['deadline_from'])) {
            $where[] = 'p.deadline >= :deadline_from';
            $params['deadline_from'] = $filters['deadline_from'];
        }

        if (!empty($filters['deadline_to'])) {
            $where[] = 'p.deadline <= :deadline_to';
            $params['deadline_to'] = $filters['deadline_to'];
        }

        if (isset($filters['min_proposals']) && $filters['min_proposals'] !== null && $filters['min_proposals'] !== '') {
            $where[] = '(SELECT COUNT(*) FROM proposals WHERE project_id = p.id) >= :min_proposals';
            $params['min_proposals'] = (int) $filters['min_proposals'];
        }

        if (isset($filters['max_proposals']) && $filters['max_proposals'] !== null && $filters['max_proposals'] !== '') {
            $where[] = '(SELECT COUNT(*) FROM proposals WHERE project_id = p.id) <= :max_proposals';
            $params['max_proposals'] = (int) $filters['max_proposals'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.created_at DESC';

        return $this->query($sql, $params);
    }

    /**
     * Buscar projeto com detalhes
     */
    public function getWithDetails(int $projectId): ?array
    {
        $project = $this->find($projectId);
        
        if (!$project) {
            return null;
        }
        
        // Decodificar skills
        if (isset($project['skills_required'])) {
            $project['skills_required'] = json_decode($project['skills_required'], true);
        }
        
        // Buscar empresa
        $sql = "SELECT name, email FROM user_profiles WHERE id = :id";
        $result = $this->query($sql, ['id' => $project['company_id']]);
        $project['company'] = $result[0] ?? null;
        
        // Contar propostas
        $sql = "SELECT COUNT(*) as total FROM proposals WHERE project_id = :id";
        $result = $this->query($sql, ['id' => $projectId]);
        $project['proposals_count'] = $result[0]['total'] ?? 0;
        
        return $project;
    }

    /**
     * Buscar projetos por categoria
     */
    public function getByCategory(string $category): array
    {
        $sql = "SELECT p.*, u.name as company_name
                FROM {$this->table} p
                JOIN user_profiles u ON p.company_id = u.id
                WHERE p.category = :category AND p.status = 'open'
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, ['category' => $category]);
    }

    /**
     * Selecionar proposta
     */
    public function selectProposal(int $projectId, int $proposalId): bool
    {
        return $this->update($projectId, [
            'selected_proposal_id' => $proposalId,
            'status' => 'in_progress'
        ]);
    }
}
