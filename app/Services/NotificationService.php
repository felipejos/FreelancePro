<?php

namespace App\Services;

use App\Models\Notification;
use App\Services\EmailService;

/**
 * NotificationService - Sistema de notificações
 */
class NotificationService
{
    protected Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Criar notificação
     */
    public function create(int $userId, string $type, string $title, string $message, ?int $referenceId = null, array $options = []): ?int
    {
        $sendEmail = $options['send_email'] ?? true;
        $sendPush = $options['send_push'] ?? false;

        $notificationId = $this->notificationModel->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);

        // Enviar email (melhor esforço)
        if ($sendEmail) {
            try {
                $userModel = new \App\Models\User();
                $recipient = $userModel->find($userId);
                if ($recipient && !empty($recipient['email'])) {
                    $email = new EmailService();
                    $email->sendTemplate(
                        $recipient['email'],
                        $title,
                        'notification_generic',
                        [
                            'title' => $title,
                            'message' => $message,
                            'referenceId' => $referenceId,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Ignorar erros de email para não impactar a notificação in-app
            }
        }

        // Push opcional (se existir serviço disponível)
        if ($sendPush && class_exists('\\App\\Services\\PushService')) {
            try {
                $push = new \App\Services\PushService();
                $push->send($userId, $title, $message, $type, $referenceId);
            } catch (\Throwable $e) {
                // Ignorar falhas de push
            }
        }

        return $notificationId;
    }

    /**
     * Notificar nova proposta
     */
    public function notifyNewProposal(int $companyId, int $projectId, string $projectTitle, string $professionalName): void
    {
        $this->create(
            $companyId,
            'proposal_new',
            'Nova Proposta Recebida',
            "O profissional {$professionalName} enviou uma proposta para o projeto \"{$projectTitle}\".",
            $projectId
        );
    }

    /**
     * Notificar proposta aceita
     */
    public function notifyProposalAccepted(int $professionalId, int $projectId, string $projectTitle): void
    {
        $this->create(
            $professionalId,
            'proposal_accepted',
            'Proposta Aceita!',
            "Sua proposta para o projeto \"{$projectTitle}\" foi aceita! Um contrato foi gerado.",
            $projectId
        );
    }

    /**
     * Notificar proposta rejeitada
     */
    public function notifyProposalRejected(int $professionalId, int $projectId, string $projectTitle): void
    {
        $this->create(
            $professionalId,
            'proposal_rejected',
            'Proposta Rejeitada',
            "Sua proposta para o projeto \"{$projectTitle}\" foi rejeitada.",
            $projectId
        );
    }

    /**
     * Notificar pagamento confirmado
     */
    public function notifyPaymentConfirmed(int $userId, float $amount, string $description): void
    {
        $this->create(
            $userId,
            'payment_confirmed',
            'Pagamento Confirmado',
            "Pagamento de R$ " . number_format($amount, 2, ',', '.') . " confirmado: {$description}."
        );
    }

    /**
     * Notificar pendência gerada por IA (monitoramento / revisão)
     */
    public function notifyAiPendingReview(int $adminId, int $userId, string $reason, ?int $referenceId = null, bool $sendPush = false): void
    {
        $this->create(
            $adminId,
            'ai_pending_review',
            'Pendência de IA para revisão',
            "Usuário #{$userId} gerou uma pendência automatizada: {$reason}",
            $referenceId,
            ['send_push' => $sendPush]
        );
    }

    /**
     * Notificar contestação aberta (admin)
     */
    public function notifyContestPending(int $adminId, int $userId, string $reason, ?int $referenceId = null, bool $sendPush = false): void
    {
        $this->create(
            $adminId,
            'contest_pending',
            'Contestação aberta para análise',
            "Usuário #{$userId} abriu uma contestação: {$reason}",
            $referenceId,
            ['send_push' => $sendPush]
        );
    }

    /**
     * Notificar pendência criada (monitoramento IA)
     */
    public function notifyPendingReview(int $adminId, int $userId, string $reason, ?int $referenceId = null): void
    {
        $this->create(
            $adminId,
            'pending_review',
            'Pendência para Revisão',
            "Usuário #{$userId} criou uma pendência: {$reason}",
            $referenceId
        );
    }

    /**
     * Buscar notificações não lidas do usuário
     */
    public function getUnread(int $userId): array
    {
        return $this->notificationModel->getUnreadByUser($userId);
    }

    /**
     * Marcar como lida
     */
    public function markAsRead(int $notificationId): bool
    {
        return $this->notificationModel->update($notificationId, ['is_read' => true]);
    }

    /**
     * Marcar todas como lidas
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->notificationModel->markAllRead($userId);
    }

    public function notifyCounterProposalCreated(
        int $recipientId,
        int $projectId,
        string $projectTitle,
        string $senderName,
        float $amount,
        int $days
    ): void {
        $this->create(
            $recipientId,
            'counterproposal_created',
            'Nova contraproposta recebida',
            "{$senderName} enviou uma contraproposta de R$ " . number_format($amount, 2, ',', '.') .
            " e prazo de {$days} dia(s) no projeto \"{$projectTitle}\".",
            $projectId
        );
    }

    public function notifyCounterProposalAccepted(
        int $recipientId,
        int $projectId,
        string $projectTitle,
        string $actorName
    ): void {
        $this->create(
            $recipientId,
            'counterproposal_accepted',
            'Contraproposta aceita',
            "{$actorName} aceitou a contraproposta no projeto \"{$projectTitle}\". A proposta foi atualizada com os novos termos.",
            $projectId
        );
    }

    public function notifyCounterProposalRejected(
        int $recipientId,
        int $projectId,
        string $projectTitle,
        string $actorName
    ): void {
        $this->create(
            $recipientId,
            'counterproposal_rejected',
            'Contraproposta rejeitada',
            "{$actorName} rejeitou a contraproposta no projeto \"{$projectTitle}\". Você pode enviar uma nova contraproposta ou ajustar sua proposta.",
            $projectId
        );
    }

    public function notifyProposalPayment(int $recipientId, int $projectId, string $projectTitle, float $amount): void
    {
        $this->create(
            $recipientId,
            'proposal_payment',
            'Pagamento confirmado',
            "O pagamento de R$ " . number_format($amount, 2, ',', '.') . " da proposta do projeto \"{$projectTitle}\" foi confirmado.",
            $projectId
        );
    }
}
