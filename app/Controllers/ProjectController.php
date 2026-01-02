<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Contract;
use App\Models\Review;
use App\Models\ProjectMessage;
use App\Models\ProposalCounteroffer;
use App\Models\Payment;
use App\Services\ContentMonitorService;
use App\Services\FileUploadService;

/**
 * ProjectController - Gerenciamento de Projetos Freelancer
 */
class ProjectController extends Controller
{
    protected Project $projectModel;
    protected Proposal $proposalModel;
    protected Contract $contractModel;
    protected ProposalCounteroffer $counterofferModel;
    protected Payment $paymentModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->proposalModel = new Proposal();
        $this->contractModel = new Contract();
        $this->counterofferModel = new ProposalCounteroffer();
        $this->paymentModel = new Payment();
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

        $counteroffersByProposal = [];
        if ($isOwner && !empty($proposals)) {
            foreach ($proposals as $proposal) {
                $counteroffersByProposal[$proposal['id']] = $this->counterofferModel->getByProposal($proposal['id']);
            }
        }

        $contractsByProposal = [];
        if (!empty($proposals)) {
            foreach ($proposals as $proposal) {
                $contract = $this->contractModel->getByProposal($proposal['id']);
                if ($contract) {
                    $contractsByProposal[$proposal['id']] = $contract;
                }
                // Carregar último pagamento da proposta para exibir recibo/invoice
                $payment = $this->paymentModel->findProposalPayment((int)$proposal['id']);
                if ($payment) {
                    $proposalsPayments[$proposal['id']] = $payment;
                }
            }
        }

        $selectedContract = null;
        if (!empty($project['selected_proposal_id'])) {
            $selectedContract = $contractsByProposal[$project['selected_proposal_id']] ?? null;
        }

        $this->setLayout('dashboard');
        $this->view('projects/show', [
            'title' => $project['title'],
            'project' => $project,
            'proposals' => $proposals,
            'isOwner' => $isOwner,
            'messagesByProposal' => $messagesByProposal,
            'counteroffersByProposal' => $counteroffersByProposal,
            'contractsByProposal' => $contractsByProposal,
            'selectedContract' => $selectedContract,
            'proposalsPayments' => $proposalsPayments ?? [],
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

        // Monitoramento de IA: verificar conteúdo
        $monitor = new ContentMonitorService();
        $result = $monitor->process($text, $user['id'], 'project_message');

        if (!$result['allowed']) {
            $this->flash('error', $result['message'] ?? 'Conteúdo não permitido.');
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

    public function cancel(int $id): void
    {
        $user = $this->currentUser();
        $project = $this->projectModel->find($id);

        if (!$project || $project['company_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        if ($project['status'] === 'completed') {
            $this->json(['error' => 'Projeto já finalizado não pode ser cancelado'], 400);
            return;
        }

        $this->projectModel->update($id, ['status' => 'cancelled']);
        $this->json(['success' => true, 'message' => 'Projeto cancelado com sucesso!']);
    }

    public function acceptProposal(int $projectId, int $proposalId): void
    {
        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);

        if (!$project || $project['company_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        $proposal = $this->proposalModel->find($proposalId);
        if (!$proposal) {
            $this->json(['error' => 'Proposta não encontrada'], 404);
            return;
        }

        $this->proposalModel->accept($proposalId);
        $this->projectModel->selectProposal($projectId, $proposalId);
        $contractId = $this->contractModel->createFromProposal($proposalId);

        // Notificar profissional que proposta foi aceita
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifyProposalAccepted($proposal['professional_id'], $projectId, $project['title']);

        $this->json(['success' => true, 'contract_id' => $contractId]);
    }

    public function rejectProposal(int $projectId, int $proposalId): void
    {
        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);

        if (!$project || $project['company_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        $proposal = $this->proposalModel->find($proposalId);
        if (!$proposal || $proposal['project_id'] != $projectId) {
            $this->json(['error' => 'Proposta não encontrada'], 404);
            return;
        }

        // Impedir rejeição se já foi selecionada, aceita ou paga
        $selectedId = $project['selected_proposal_id'] ?? null;
        if ($selectedId && (int)$selectedId === (int)$proposalId) {
            $this->json(['error' => 'Não é possível rejeitar uma proposta já selecionada.'], 400);
            return;
        }
        if (in_array($proposal['status'], ['accepted_pending_payment', 'paid'], true)) {
            $this->json(['error' => 'Não é possível rejeitar uma proposta já aceita ou paga.'], 400);
            return;
        }
        if ($proposal['status'] === 'rejected') {
            $this->json(['error' => 'Proposta já está rejeitada.'], 400);
            return;
        }

        $this->proposalModel->reject($proposalId);

        // Notificar profissional que proposta foi rejeitada
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifyProposalRejected($proposal['professional_id'], $projectId, $project['title']);

        $this->json(['success' => true, 'message' => 'Proposta rejeitada com sucesso!']);
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
            return;
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
            return;
        }

        $user = $this->currentUser();
        $contract = $this->contractModel->find($contractId);

        if (!$contract) {
            $this->json(['error' => 'Contrato não encontrado'], 404);
            return;
        }

        $reviewModel = new Review();
        if ($reviewModel->hasReviewed($contractId, $user['id'])) {
            $this->json(['error' => 'Você já avaliou este contrato.'], 400);
            return;
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

    public function uploadContractDocument(int $projectId, int $contractId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("projects/{$projectId}");
        }

        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);
        $contract = $this->contractModel->getWithDetails($contractId);

        if (
            !$project ||
            !$contract ||
            (int) $project['company_id'] !== (int) ($user['id'] ?? 0) ||
            (int) $contract['project_id'] !== $projectId
        ) {
            $this->flash('error', 'Contrato não encontrado para este projeto.');
            $this->redirect("projects/{$projectId}");
        }

        if (empty($_FILES['service_contract']) || $_FILES['service_contract']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('error', 'Selecione um arquivo para enviar.');
            $this->redirect("projects/{$projectId}");
        }

        $extension = strtolower(pathinfo($_FILES['service_contract']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $this->flash('error', 'Envie o contrato em formato PDF.');
            $this->redirect("projects/{$projectId}");
        }

        try {
            $uploader = new FileUploadService();
            $fileData = $uploader->upload($_FILES['service_contract'], 'contracts/' . $contractId);

            if (!empty($contract['service_contract_path']) && $contract['service_contract_path'] !== $fileData['relative_path']) {
                $uploader->delete($contract['service_contract_path']);
            }

            $this->contractModel->update($contractId, [
                'service_contract_path' => $fileData['relative_path'],
                'service_contract_original_name' => $fileData['original_name'],
            ]);

            $this->flash('success', 'Contrato enviado com sucesso!');
        } catch (\Throwable $e) {
            $this->flash('error', 'Erro ao enviar contrato: ' . $e->getMessage());
        }

        $this->redirect("projects/{$projectId}");
    }

    public function downloadContractDocument(int $contractId): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->redirect('login');
        }

        $contract = $this->contractModel->getWithDetails($contractId);
        if (!$contract) {
            $this->flash('error', 'Contrato não encontrado.');
            $this->redirect($this->hasRole('professional') ? 'professional/contracts' : 'contracts');
        }

        $isCompany = (int) $contract['company_id'] === (int) $user['id'];
        $isProfessional = (int) $contract['professional_id'] === (int) $user['id'];

        if (!$isCompany && !$isProfessional) {
            $this->flash('error', 'Você não tem permissão para acessar este contrato.');
            $this->redirect('dashboard');
        }

        if (empty($contract['service_contract_path'])) {
            $this->flash('error', 'Nenhum contrato foi enviado ainda.');
            $redirectPath = $isProfessional
                ? "professional/projects/{$contract['project_id']}"
                : "projects/{$contract['project_id']}";
            $this->redirect($redirectPath);
        }

        $config = require ROOT_PATH . '/config/app.php';
        $basePath = rtrim($config['upload']['path'], DIRECTORY_SEPARATOR);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $contract['service_contract_path'];

        if (!file_exists($filePath)) {
            $this->flash('error', 'Arquivo do contrato não foi encontrado.');
            $redirectPath = $isProfessional
                ? "professional/projects/{$contract['project_id']}"
                : "projects/{$contract['project_id']}";
            $this->redirect($redirectPath);
        }

        $fileName = $contract['service_contract_original_name'] ?? basename($filePath);
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
