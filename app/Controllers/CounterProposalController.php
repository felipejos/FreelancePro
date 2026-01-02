<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalCounteroffer;
use App\Services\NotificationService;

class CounterProposalController extends Controller
{
    protected Project $projectModel;
    protected Proposal $proposalModel;
    protected ProposalCounteroffer $counterofferModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->proposalModel = new Proposal();
        $this->counterofferModel = new ProposalCounteroffer();
    }

    public function sendByCompany(int $projectId, int $proposalId): void
    {
        $this->handleSend($projectId, $proposalId, 'company');
    }

    public function sendByProfessional(int $projectId, int $proposalId): void
    {
        $this->handleSend($projectId, $proposalId, 'professional');
    }

    public function acceptAsCompany(int $projectId, int $proposalId, int $counterId): void
    {
        $this->handleResponse($projectId, $proposalId, $counterId, 'company', 'accept');
    }

    public function rejectAsCompany(int $projectId, int $proposalId, int $counterId): void
    {
        $this->handleResponse($projectId, $proposalId, $counterId, 'company', 'reject');
    }

    public function acceptAsProfessional(int $projectId, int $proposalId, int $counterId): void
    {
        $this->handleResponse($projectId, $proposalId, $counterId, 'professional', 'accept');
    }

    public function rejectAsProfessional(int $projectId, int $proposalId, int $counterId): void
    {
        $this->handleResponse($projectId, $proposalId, $counterId, 'professional', 'reject');
    }

    protected function handleSend(int $projectId, int $proposalId, string $senderType): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);
        $proposal = $this->proposalModel->find($proposalId);

        if (!$project || !$proposal || (int) $proposal['project_id'] !== $projectId) {
            $this->flash('error', 'Projeto ou proposta não encontrados.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        if ($proposal['status'] !== 'pending') {
            $this->flash('error', 'Não é possível negociar uma proposta que não está pendente.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        if ($senderType === 'company' && (int) $project['company_id'] !== (int) $user['id']) {
            $this->flash('error', 'Você não pode negociar este projeto.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        if ($senderType === 'professional' && (int) $proposal['professional_id'] !== (int) $user['id']) {
            $this->flash('error', 'Você não pode negociar esta proposta.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        $pending = $this->counterofferModel->getPendingForProposal($proposalId);
        if ($pending) {
            $this->flash('error', 'Já existe uma contraproposta pendente aguardando resposta.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        $amount = (float) $this->input('amount');
        $days = (int) $this->input('estimated_days');
        $message = trim((string) $this->input('message', ''));

        if ($amount <= 0 || $days < 1) {
            $this->flash('error', 'Informe um valor e prazo válidos.');
            $this->redirect($this->redirectPath($senderType, $projectId));
        }

        $this->counterofferModel->createCounter([
            'proposal_id' => $proposalId,
            'sender_id' => $user['id'],
            'sender_type' => $senderType,
            'amount' => $amount,
            'estimated_days' => $days,
            'message' => $message,
        ]);

        $newStatus = $senderType === 'company' ? 'awaiting_professional' : 'awaiting_company';
        $this->proposalModel->updateNegotiationStatus($proposalId, $newStatus);

        $notificationService = new NotificationService();
        $recipientId = $senderType === 'company'
            ? (int) $proposal['professional_id']
            : (int) $project['company_id'];

        $notificationService->notifyCounterProposalCreated(
            $recipientId,
            (int) $projectId,
            $project['title'],
            $user['name'],
            $amount,
            $days
        );

        $this->flash('success', 'Contraproposta enviada!');
        $this->redirect($this->redirectPath($senderType, $projectId));
    }

    protected function handleResponse(int $projectId, int $proposalId, int $counterId, string $actorType, string $action): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);
        $proposal = $this->proposalModel->find($proposalId);
        $counter = $this->counterofferModel->find($counterId);

        if (
            !$project ||
            !$proposal ||
            !$counter ||
            (int) $proposal['project_id'] !== $projectId ||
            (int) $counter['proposal_id'] !== $proposalId
        ) {
            $this->flash('error', 'Dados da contraproposta não encontrados.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        if ($counter['status'] !== 'pending') {
            $this->flash('error', 'Esta contraproposta já foi respondida.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        if ($actorType === 'company' && (int) $project['company_id'] !== (int) $user['id']) {
            $this->flash('error', 'Você não pode responder esta contraproposta.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        if ($actorType === 'professional' && (int) $proposal['professional_id'] !== (int) $user['id']) {
            $this->flash('error', 'Você não pode responder esta contraproposta.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        if ($counter['sender_type'] === $actorType) {
            $this->flash('error', 'Você não pode responder a uma contraproposta que você mesmo enviou.');
            $this->redirect($this->redirectPath($actorType, $projectId));
        }

        $newStatus = $action === 'accept' ? 'accepted' : 'rejected';
        $this->counterofferModel->markStatus($counterId, $newStatus, $user['id']);

        $notificationService = new NotificationService();
        $targetUser = $counter['sender_type'] === 'company' ? (int) $project['company_id'] : (int) $proposal['professional_id'];

        if ($newStatus === 'accepted') {
            $this->proposalModel->update($proposalId, [
                'proposed_value' => $counter['amount'],
                'estimated_days' => $counter['estimated_days'],
                'negotiation_status' => 'accepted',
            ]);

            $notificationService->notifyCounterProposalAccepted(
                $targetUser,
                (int) $projectId,
                $project['title'],
                $user['name']
            );

            $this->flash('success', 'Contraproposta aceita! Os valores foram atualizados na proposta.');
        } else {
            $this->proposalModel->updateNegotiationStatus($proposalId, 'idle');

            $notificationService->notifyCounterProposalRejected(
                $targetUser,
                (int) $projectId,
                $project['title'],
                $user['name']
            );

            $this->flash('success', 'Contraproposta rejeitada.');
        }

        $this->redirect($this->redirectPath($actorType, $projectId));
    }

    protected function redirectPath(string $actorType, int $projectId): string
    {
        if ($actorType === 'company') {
            return "projects/{$projectId}";
        }

        return "professional/projects/{$projectId}";
    }
}
