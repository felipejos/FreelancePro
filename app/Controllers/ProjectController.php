<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Contract;
use App\Models\Review;
use App\Models\ProjectMessage;

/**
 * ProjectController - Gerenciamento de Projetos Freelancer
 */
class ProjectController extends Controller
{
    protected Project $projectModel;
    protected Proposal $proposalModel;
    protected Contract $contractModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->proposalModel = new Proposal();
        $this->contractModel = new Contract();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $projects = $this->projectModel->getByCompany($user['id']);

        $this->setLayout('dashboard');
        $this->view('projects/index', [
            'title' => 'Projetos',
            'projects' => $projects,
        ]);
    }

    public function create(): void
    {
        $this->setLayout('dashboard');
        $this->view('projects/create', [
            'title' => 'Novo Projeto',
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('projects/create');
        }

        $user = $this->currentUser();
        $skills = array_map('trim', explode(',', $this->input('skills', '')));

        $projectId = $this->projectModel->create([
            'company_id' => $user['id'],
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'category' => $this->input('category'),
            'skills_required' => json_encode($skills),
            'budget_min' => $this->input('budget_min'),
            'budget_max' => $this->input('budget_max'),
            'deadline' => $this->input('deadline') ?: null,
            'status' => 'open',
        ]);

        $this->flash('success', 'Projeto criado com sucesso!');
        $this->redirect("projects/{$projectId}");
    }

    public function show(int $id): void
    {
        $user = $this->currentUser();
        $project = $this->projectModel->getWithDetails($id);

        if (!$project) {
            $this->flash('error', 'Projeto não encontrado.');
            $this->redirect('projects');
        }

        $isOwner = $project['company_id'] == $user['id'];
        $proposals = $isOwner ? $this->proposalModel->getByProject($id) : [];

        $messagesByProposal = [];
        if ($isOwner && !empty($proposals)) {
            $messageModel = new ProjectMessage();
            foreach ($proposals as $proposal) {
                $messagesByProposal[$proposal['id']] = $messageModel->getByProjectAndProposal($id, $proposal['id']);
            }
        }

        $this->setLayout('dashboard');
        $this->view('projects/show', [
            'title' => $project['title'],
            'project' => $project,
            'proposals' => $proposals,
            'isOwner' => $isOwner,
            'messagesByProposal' => $messagesByProposal,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Enviar mensagem no chat do projeto (empresa → profissional)
     */
    public function sendMessage(int $projectId, int $proposalId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("projects/{$projectId}");
        }

        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);

        if (!$project || $project['company_id'] != $user['id']) {
            $this->flash('error', 'Projeto não encontrado.');
            $this->redirect('projects');
        }

        $proposal = $this->proposalModel->find($proposalId);
        if (!$proposal || $proposal['project_id'] != $projectId) {
            $this->flash('error', 'Proposta não encontrada para este projeto.');
            $this->redirect("projects/{$projectId}");
        }

        $text = trim($this->input('message'));
        if ($text === '') {
            $this->flash('error', 'Digite uma mensagem antes de enviar.');
            $this->redirect("projects/{$projectId}");
        }

        $messageModel = new ProjectMessage();
        $messageModel->create([
            'project_id' => $projectId,
            'proposal_id' => $proposalId,
            'sender_id' => $user['id'],
            'message' => $text,
        ]);

        $this->flash('success', 'Mensagem enviada.');
        $this->redirect("projects/{$projectId}");
    }

    public function acceptProposal(int $projectId, int $proposalId): void
    {
        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);

        if (!$project || $project['company_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
        }

        $this->proposalModel->accept($proposalId);
        $this->projectModel->selectProposal($projectId, $proposalId);
        $contractId = $this->contractModel->createFromProposal($proposalId);

        $this->json(['success' => true, 'contract_id' => $contractId]);
    }

    public function contracts(): void
    {
        $user = $this->currentUser();
        $contracts = $this->contractModel->getByCompany($user['id']);

        $this->setLayout('dashboard');
        $this->view('projects/contracts', [
            'title' => 'Contratos',
            'contracts' => $contracts,
        ]);
    }

    public function viewContract(int $id): void
    {
        $user = $this->currentUser();
        $contract = $this->contractModel->getWithDetails($id);

        if (!$contract) {
            $this->flash('error', 'Contrato não encontrado.');
            $this->redirect('contracts');
        }

        $reviewModel = new Review();
        $canReview = $contract['status'] === 'completed' && !$reviewModel->hasReviewed($id, $user['id']);
        $myReview = $reviewModel->getByContractAndReviewer($id, $user['id']);

        $this->setLayout('dashboard');
        $this->view('projects/contract-view', [
            'title' => 'Contrato #' . $id,
            'contract' => $contract,
            'canReview' => $canReview,
            'myReview' => $myReview,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function completeContract(int $id): void
    {
        $user = $this->currentUser();
        $contract = $this->contractModel->find($id);

        if (!$contract || $contract['company_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
        }

        // Finalizar contrato
        $this->contractModel->complete($id);

        // Atualizar status do projeto relacionado para "completed"
        if (!empty($contract['project_id'])) {
            $this->projectModel->update($contract['project_id'], ['status' => 'completed']);
        }

        $this->json(['success' => true, 'message' => 'Contrato finalizado!']);
    }

    public function submitReview(int $contractId): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
        }

        $user = $this->currentUser();
        $contract = $this->contractModel->find($contractId);

        if (!$contract) {
            $this->json(['error' => 'Contrato não encontrado'], 404);
        }

        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($contractId, $user['id'])) {
            $this->json(['error' => 'Você já avaliou este contrato.'], 400);
        }

        $reviewedId = $contract['company_id'] == $user['id'] 
            ? $contract['professional_id'] 
            : $contract['company_id'];

        $reviewModel->create([
            'contract_id' => $contractId,
            'reviewer_id' => $user['id'],
            'reviewed_id' => $reviewedId,
            'rating' => (int) $this->input('rating'),
            'comment' => trim($this->input('comment')),
        ]);

        $this->json(['success' => true, 'message' => 'Avaliação enviada!']);
    }
}
