<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Contract;
use App\Models\Review;
use App\Models\User;
use App\Models\ProjectMessage;

/**
 * ProfessionalController - Área do Profissional/Freelancer
 */
class ProfessionalController extends Controller
{
    public function dashboard(): void
    {
        $user = $this->currentUser();
        
        $proposalModel = new Proposal();
        $contractModel = new Contract();
        $reviewModel = new Review();

        $proposals = $proposalModel->getByProfessional($user['id']);
        $contracts = $contractModel->getByProfessional($user['id']);
        $avgRating = $reviewModel->getAverageRating($user['id']);

        $this->setLayout('professional');
        $this->view('professional/dashboard', [
            'title' => 'Dashboard',
            'proposals' => array_slice($proposals, 0, 5),
            'contracts' => array_slice($contracts, 0, 5),
            'avgRating' => $avgRating,
            'stats' => [
                'proposals' => count($proposals),
                'active_contracts' => count(array_filter($contracts, fn($c) => $c['status'] === 'active')),
                'completed' => count(array_filter($contracts, fn($c) => $c['status'] === 'completed')),
            ],
        ]);
    }

    public function projects(): void
    {
        $projectModel = new Project();
        $filters = [
            'search' => trim((string) $this->input('search', '')),
            'budget_min' => $this->input('budget_min') !== '' ? $this->input('budget_min') : null,
            'budget_max' => $this->input('budget_max') !== '' ? $this->input('budget_max') : null,
            'deadline_from' => $this->input('deadline_from') ?: null,
            'deadline_to' => $this->input('deadline_to') ?: null,
            'min_proposals' => $this->input('min_proposals') !== '' ? $this->input('min_proposals') : null,
            'max_proposals' => $this->input('max_proposals') !== '' ? $this->input('max_proposals') : null,
        ];

        $projects = $projectModel->getOpen($filters);

        $hasFilters = !empty(array_filter($filters, fn($v) => $v !== null && $v !== ''));

        $this->setLayout('professional');
        $this->view('professional/projects', [
            'title' => 'Projetos Disponíveis',
            'projects' => $projects,
            'filters' => $filters,
            'hasFilters' => $hasFilters,
        ]);
    }

    public function viewProject(int $id): void
    {
        $user = $this->currentUser();
        $projectModel = new Project();
        $proposalModel = new Proposal();

        $project = $projectModel->getWithDetails($id);

        if (!$project) {
            $this->flash('error', 'Projeto não encontrado.');
            $this->redirect('professional/projects');
        }

        $hasProposal = $proposalModel->hasSubmitted($id, $user['id']);

        $proposal = null;
        $messages = [];

        if ($hasProposal) {
            $proposal = $proposalModel->getForProfessionalInProject($id, $user['id']);

            if ($proposal) {
                $messageModel = new ProjectMessage();
                $messages = $messageModel->getByProjectAndProposal($id, $proposal['id']);
            }
        }

        $this->setLayout('professional');
        $this->view('professional/project-detail', [
            'title' => $project['title'],
            'project' => $project,
            'hasProposal' => $hasProposal,
            'proposal' => $proposal,
            'messages' => $messages,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Aceitar projeto sem preencher proposta manualmente
     * Cria uma proposta padrão usando orçamento/prazo do projeto
     */
    public function acceptProject(int $projectId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("professional/projects/{$projectId}");
        }

        $user = $this->currentUser();

        $projectModel = new Project();
        $proposalModel = new Proposal();

        $project = $projectModel->getWithDetails($projectId);
        if (!$project) {
            $this->flash('error', 'Projeto não encontrado.');
            $this->redirect('professional/projects');
        }

        if (($project['status'] ?? 'open') !== 'open') {
            $this->flash('error', 'Este projeto não está mais aberto para novos profissionais.');
            $this->redirect("professional/projects/{$projectId}");
        }

        if ($proposalModel->hasSubmitted($projectId, $user['id'])) {
            $this->flash('error', 'Você já possui uma proposta para este projeto.');
            $this->redirect("professional/projects/{$projectId}");
        }

        // Definir valor e prazo padrão com base nas informações do projeto
        $value = 0.0;
        if (!empty($project['budget_max'])) {
            $value = (float) $project['budget_max'];
        } elseif (!empty($project['budget_min'])) {
            $value = (float) $project['budget_min'];
        }

        $days = 7;
        if (!empty($project['deadline'])) {
            $diff = (strtotime($project['deadline']) - time()) / 86400;
            $days = max(1, (int) ceil($diff));
        }

        $proposalModel->create([
            'project_id' => $projectId,
            'professional_id' => $user['id'],
            'cover_letter' => 'Aceitei este projeto sem enviar uma proposta detalhada.',
            'proposed_value' => $value,
            'estimated_days' => $days,
        ]);

        $this->flash('success', 'Você aceitou este projeto. Aguardando confirmação da empresa.');
        $this->redirect("professional/projects/{$projectId}");
    }

    /**
     * Enviar mensagem no chat do projeto (profissional → empresa)
     */
    public function sendMessage(int $projectId, int $proposalId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("professional/projects/{$projectId}");
        }

        $user = $this->currentUser();

        $projectModel = new Project();
        $proposalModel = new Proposal();

        $project = $projectModel->getWithDetails($projectId);
        if (!$project) {
            $this->flash('error', 'Projeto não encontrado.');
            $this->redirect('professional/projects');
        }

        $proposal = $proposalModel->find($proposalId);
        if (!$proposal || $proposal['project_id'] != $projectId || $proposal['professional_id'] != $user['id']) {
            $this->flash('error', 'Proposta não encontrada para este projeto.');
            $this->redirect("professional/projects/{$projectId}");
        }

        $text = trim($this->input('message'));
        if ($text === '') {
            $this->flash('error', 'Digite uma mensagem antes de enviar.');
            $this->redirect("professional/projects/{$projectId}");
        }

        $messageModel = new ProjectMessage();
        $messageModel->create([
            'project_id' => $projectId,
            'proposal_id' => $proposalId,
            'sender_id' => $user['id'],
            'message' => $text,
        ]);

        $this->flash('success', 'Mensagem enviada.');
        $this->redirect("professional/projects/{$projectId}");
    }

    public function submitProposal(int $projectId): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
        }

        $user = $this->currentUser();
        $proposalModel = new Proposal();

        if ($proposalModel->hasSubmitted($projectId, $user['id'])) {
            $this->json(['error' => 'Você já enviou uma proposta'], 400);
        }

        $proposalModel->create([
            'project_id' => $projectId,
            'professional_id' => $user['id'],
            'cover_letter' => trim($this->input('cover_letter')),
            'proposed_value' => (float) $this->input('proposed_value'),
            'estimated_days' => (int) $this->input('estimated_days'),
        ]);

        $this->json(['success' => true, 'message' => 'Proposta enviada!']);
    }

    public function myProposals(): void
    {
        $user = $this->currentUser();
        $proposalModel = new Proposal();
        $proposals = $proposalModel->getByProfessional($user['id']);

        $this->setLayout('professional');
        $this->view('professional/proposals', [
            'title' => 'Minhas Propostas',
            'proposals' => $proposals,
        ]);
    }

    public function myContracts(): void
    {
        $user = $this->currentUser();
        $contractModel = new Contract();
        $contracts = $contractModel->getByProfessional($user['id']);

        $this->setLayout('professional');
        $this->view('professional/contracts', [
            'title' => 'Meus Contratos',
            'contracts' => $contracts,
        ]);
    }

    public function reviews(): void
    {
        $user = $this->currentUser();
        $reviewModel = new Review();

        $received = $reviewModel->getReceivedByUser($user['id']);
        $avgRating = $reviewModel->getAverageRating($user['id']);

        $this->setLayout('professional');
        $this->view('professional/reviews', [
            'title' => 'Minhas Avaliações',
            'reviews' => $received,
            'avgRating' => $avgRating,
        ]);
    }
}
