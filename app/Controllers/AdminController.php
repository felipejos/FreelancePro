<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\AdminConfig;
use App\Models\EmailConfig;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\AILog;
use App\Models\ContentViolation;

/**
 * AdminController - Painel Administrativo
 */
class AdminController extends Controller
{
    public function dashboard(): void
    {
        $userModel = new User();
        $paymentModel = new Payment();
        $subscriptionModel = new Subscription();

        $stats = [
            'total_users' => $userModel->count(),
            'companies' => $userModel->countByType('company'),
            'professionals' => $userModel->countByType('professional'),
            'employees' => $userModel->countByType('employee'),
        ];

        $paymentStats = $paymentModel->getStats();

        $this->setLayout('admin');
        $this->view('admin/dashboard', [
            'title' => 'Painel Admin',
            'stats' => $stats,
            'paymentStats' => $paymentStats,
        ]);
    }

    public function users(): void
    {
        $userModel = new User();
        $users = $userModel->all();

        $this->setLayout('admin');
        $this->view('admin/users', [
            'title' => 'Usuários',
            'users' => $users,
        ]);
    }

    public function showUser(int $id): void
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('admin/users');
        }

        $this->setLayout('admin');
        $this->view('admin/user-show', [
            'title' => 'Usuário: ' . ($user['name'] ?? $user['email']),
            'user' => $user,
        ]);
    }

    public function payments(): void
    {
        $paymentModel = new Payment();
        $payments = $paymentModel->all();

        $this->setLayout('admin');
        $this->view('admin/payments', [
            'title' => 'Pagamentos',
            'payments' => $payments,
        ]);
    }

    public function configs(): void
    {
        $configModel = new AdminConfig();
        $configs = $configModel->getAll(false);

        $this->setLayout('admin');
        $this->view('admin/configs', [
            'title' => 'Configurações',
            'configs' => $configs,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function saveConfigs(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $configModel = new AdminConfig();
        $configs = $this->input('configs', []);

        foreach ($configs as $key => $value) {
            $configModel->set($key, $value);
        }

        $this->json(['success' => true, 'message' => 'Configurações salvas!']);
    }

    public function emailConfig(): void
    {
        $emailModel = new EmailConfig();
        $config = $emailModel->getActive();

        $this->setLayout('admin');
        $this->view('admin/email-config', [
            'title' => 'Configuração de Email',
            'config' => $config,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function saveEmailConfig(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $emailModel = new EmailConfig();

        $data = [
            'mail_driver' => $this->input('mail_driver', 'smtp'),
            'smtp_host' => trim($this->input('smtp_host')),
            'smtp_port' => (int) $this->input('smtp_port', 587),
            'smtp_username' => trim($this->input('smtp_username')),
            'smtp_encryption' => $this->input('smtp_encryption', 'tls'),
            'from_address' => trim($this->input('from_address')),
            'from_name' => trim($this->input('from_name')),
            'is_active' => true,
        ];

        $password = $this->input('smtp_password');
        if (!empty($password)) {
            $data['smtp_password'] = $password;
        }

        $emailModel->saveConfig($data);

        $this->json(['success' => true, 'message' => 'Configuração de email salva!']);
    }

    public function testEmail(): void
    {
        $email = $this->input('email');

        if (empty($email)) {
            $this->json(['error' => 'Informe um email'], 400);
            return;
        }

        try {
            $emailService = new \App\Services\EmailService();
            $emailService->send($email, 'Teste de Email - FreelancePro', 'Este é um email de teste.');

            $this->json(['success' => true, 'message' => 'Email de teste enviado!']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function aiLogs(): void
    {
        $aiLogModel = new AILog();
        $stats = $aiLogModel->getStats();

        $this->setLayout('admin');
        $this->view('admin/ai-logs', [
            'title' => 'Logs de IA',
            'stats' => $stats,
        ]);
    }

    public function toggleUserStatus(int $id): void
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $this->json(['error' => 'Usuário não encontrado'], 404);
            return;
        }

        $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
        $userModel->update($id, ['status' => $newStatus]);

        $this->json(['success' => true, 'status' => $newStatus]);
    }

    /**
     * Listar violações de conteúdo pendentes
     */
    public function violations(): void
    {
        $violationModel = new ContentViolation();
        $violations = $violationModel->getPending();

        $this->setLayout('admin');
        $this->view('admin/violations', [
            'title' => 'Violações de Conteúdo',
            'violations' => $violations,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Aprovar violação (descartar)
     */
    public function approveViolation(int $id): void
    {
        $user = $this->currentUser();
        $violationModel = new ContentViolation();

        $violation = $violationModel->find($id);
        if (!$violation) {
            $this->json(['error' => 'Violação não encontrada'], 404);
            return;
        }

        $violationModel->approve($id, $user['id']);
        $this->json(['success' => true, 'message' => 'Violação aprovada (descartada).']);
    }

    /**
     * Rejeitar violação e penalizar usuário
     */
    public function rejectViolation(int $id): void
    {
        $user = $this->currentUser();
        $violationModel = new ContentViolation();
        $userModel = new User();

        $violation = $violationModel->find($id);
        if (!$violation) {
            $this->json(['error' => 'Violação não encontrada'], 404);
            return;
        }

        $action = $this->input('action', 'warning');
        $violationModel->reject($id, $user['id'], $action);

        // Se ação for block, bloquear usuário
        if ($action === 'block') {
            $userModel->update($violation['user_id'], ['status' => 'blocked']);
        }

        $this->json(['success' => true, 'message' => 'Violação rejeitada. Ação: ' . $action]);
    }
}
