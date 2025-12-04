<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Services\EmailService;

/**
 * AuthController - Autenticação de usuários
 */
class AuthController extends Controller
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Página de login
     */
    public function loginPage(): void
    {
        $this->setLayout('auth');
        $this->view('auth/login', [
            'title' => 'Login',
            'csrf' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processar login
     */
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido. Tente novamente.');
            $this->redirect('login');
        }

        $email = $this->input('email');
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Preencha todos os campos.');
            $this->redirect('login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Email ou senha incorretos.');
            $this->redirect('login');
        }

        if ($user['status'] !== 'active') {
            $this->flash('error', 'Sua conta não está ativa.');
            $this->redirect('login');
        }

        // Não permitir login de funcionários aqui
        if ($user['user_type'] === 'employee') {
            $this->flash('error', 'Funcionários devem usar a área de login específica.');
            $this->redirect('employee/login');
        }

        // Criar sessão
        $this->createSession($user);

        // Atualizar último login
        $this->userModel->updateLastLogin($user['id']);

        // Redirecionar baseado no tipo
        $this->redirectToDashboard($user['user_type']);
    }

    /**
     * Página de cadastro
     */
    public function registerPage(): void
    {
        $this->setLayout('auth');
        $this->view('auth/register', [
            'title' => 'Cadastro',
            'csrf' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processar cadastro
     */
    public function register(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido. Tente novamente.');
            $this->redirect('register');
        }

        $data = [
            'name' => trim($this->input('name')),
            'email' => trim($this->input('email')),
            'password' => $this->input('password'),
            'user_type' => $this->input('user_type', 'company'),
        ];

        // Validações
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            $this->flash('error', 'Preencha todos os campos obrigatórios.');
            $this->redirect('register');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email inválido.');
            $this->redirect('register');
        }

        if (strlen($data['password']) < 6) {
            $this->flash('error', 'A senha deve ter pelo menos 6 caracteres.');
            $this->redirect('register');
        }

        // Verificar se email já existe
        if ($this->userModel->findByEmail($data['email'])) {
            $this->flash('error', 'Este email já está cadastrado.');
            $this->redirect('register');
        }

        // Garantir tipo válido
        if (!in_array($data['user_type'], ['company', 'professional'])) {
            $data['user_type'] = 'company';
        }

        // Criar usuário
        $data['status'] = 'active'; // Ativar direto (sem confirmação de email)
        $userId = $this->userModel->createUser($data);

        if (!$userId) {
            $this->flash('error', 'Erro ao criar conta. Tente novamente.');
            $this->redirect('register');
        }

        $this->flash('success', 'Conta criada com sucesso! Faça login para continuar.');
        $this->redirect('login');
    }

    /**
     * Página esqueci minha senha
     */
    public function forgotPasswordPage(): void
    {
        $this->setLayout('auth');
        $this->view('auth/forgot-password', [
            'title' => 'Esqueci Minha Senha',
            'csrf' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processar solicitação de reset de senha
     */
    public function forgotPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('forgot-password');
        }

        $email = trim($this->input('email'));

        if (empty($email)) {
            $this->flash('error', 'Informe seu email.');
            $this->redirect('forgot-password');
        }

        $user = $this->userModel->findByEmail($email);

        // Sempre mostrar mensagem de sucesso para evitar enumeração
        if ($user) {
            $token = $this->userModel->generateResetToken($user['id']);
            
            // Enviar email
            $emailService = new EmailService();
            $resetLink = $this->url("reset-password?token={$token}");
            
            $emailService->send(
                $user['email'],
                'Redefinição de Senha - FreelancePro',
                "Olá {$user['name']},\n\nVocê solicitou a redefinição de sua senha.\n\nClique no link abaixo para criar uma nova senha:\n{$resetLink}\n\nEste link expira em 1 hora.\n\nSe você não solicitou esta alteração, ignore este email."
            );
        }

        $this->flash('success', 'Se o email existir em nossa base, você receberá instruções para redefinir sua senha.');
        $this->redirect('login');
    }

    /**
     * Página de reset de senha
     */
    public function resetPasswordPage(): void
    {
        $token = $this->input('token');

        if (empty($token)) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('login');
        }

        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            $this->flash('error', 'Token inválido ou expirado.');
            $this->redirect('forgot-password');
        }

        $this->setLayout('auth');
        $this->view('auth/reset-password', [
            'title' => 'Redefinir Senha',
            'token' => $token,
            'csrf' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processar reset de senha
     */
    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('login');
        }

        $token = $this->input('token');
        $password = $this->input('password');

        if (empty($token) || empty($password)) {
            $this->flash('error', 'Dados inválidos.');
            $this->redirect('login');
        }

        if (strlen($password) < 6) {
            $this->flash('error', 'A senha deve ter pelo menos 6 caracteres.');
            $this->redirect("reset-password?token={$token}");
        }

        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            $this->flash('error', 'Token inválido ou expirado.');
            $this->redirect('forgot-password');
        }

        // Atualizar senha
        $this->userModel->updatePassword($user['id'], $password);
        $this->userModel->clearResetToken($user['id']);

        $this->flash('success', 'Senha alterada com sucesso! Faça login com a nova senha.');
        $this->redirect('login');
    }

    /**
     * Login de funcionário
     */
    public function employeeLoginPage(): void
    {
        $this->setLayout('auth');
        $this->view('auth/employee-login', [
            'title' => 'Login do Funcionário',
            'csrf' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processar login de funcionário
     */
    public function employeeLogin(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('employee/login');
        }

        $email = $this->input('email');
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Preencha todos os campos.');
            $this->redirect('employee/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Email ou senha incorretos.');
            $this->redirect('employee/login');
        }

        if ($user['user_type'] !== 'employee') {
            $this->flash('error', 'Acesso permitido apenas para funcionários.');
            $this->redirect('employee/login');
        }

        if ($user['status'] !== 'active') {
            $this->flash('error', 'Sua conta não está ativa.');
            $this->redirect('employee/login');
        }

        $this->createSession($user);
        $this->userModel->updateLastLogin($user['id']);

        $this->redirect('employee/dashboard');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }

    /**
     * Criar sessão do usuário
     */
    protected function createSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'user_type' => $user['user_type'],
            'company_id' => $user['company_id'] ?? null,
        ];
    }

    /**
     * Redirecionar para dashboard apropriado
     */
    protected function redirectToDashboard(string $userType): void
    {
        $routes = [
            'admin' => 'admin/dashboard',
            'company' => 'dashboard',
            'professional' => 'professional/dashboard',
            'employee' => 'employee/dashboard',
        ];

        $this->redirect($routes[$userType] ?? 'dashboard');
    }
}
