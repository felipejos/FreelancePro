<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

/**
 * NotificationController - Gerenciamento de notificações
 */
class NotificationController extends Controller
{
    protected Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Listar notificações do usuário
     */
    public function index(): void
    {
        $user = $this->currentUser();
        $notifications = $this->notificationModel->getByUser($user['id']);

        $layout = match ($user['user_type']) {
            'admin' => 'admin',
            'company' => 'dashboard',
            'professional' => 'professional',
            'employee' => 'employee',
            default => 'dashboard',
        };

        $this->setLayout($layout);
        $this->view('notifications/index', [
            'title' => 'Notificações',
            'notifications' => $notifications,
            'userType' => $user['user_type'] ?? 'company',
        ]);
    }

    /**
     * Marcar notificação como lida
     */
    public function markRead(int $id): void
    {
        $user = $this->currentUser();
        $notification = $this->notificationModel->find($id);

        if (!$notification || $notification['user_id'] != $user['id']) {
            $this->json(['error' => 'Notificação não encontrada'], 404);
            return;
        }

        $this->notificationModel->update($id, ['is_read' => true]);
        $this->json(['success' => true]);
    }

    /**
     * Marcar todas como lidas
     */
    public function markAllRead(): void
    {
        $user = $this->currentUser();
        $this->notificationModel->markAllRead($user['id']);
        $this->json(['success' => true]);
    }

    /**
     * Buscar notificações não lidas (API)
     */
    public function getUnread(): void
    {
        $user = $this->currentUser();
        $notifications = $this->notificationModel->getUnreadByUser($user['id']);
        $count = $this->notificationModel->countUnread($user['id']);

        $this->json([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }
}
