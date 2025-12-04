<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\UserAddress;

/**
 * AccountController - Minha Conta
 */
class AccountController extends Controller
{
    protected User $userModel;
    protected UserAddress $addressModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->addressModel = new UserAddress();
    }

    public function index(): void
    {
        $user = $this->currentUser();
        $userData = $this->userModel->find($user['id']);
        $address = $this->addressModel->getDefaultByUser($user['id']);

        $layout = $this->getLayoutByUserType($user['user_type']);

        $this->setLayout($layout);
        $this->view('account/index', [
            'title' => 'Minha Conta',
            'userData' => $userData,
            'address' => $address,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('account');
        }

        $user = $this->currentUser();

        $data = [
            'name' => trim($this->input('name')),
            'phone' => trim($this->input('phone')),
            'cpf' => trim($this->input('cpf')),
            'birth_date' => $this->input('birth_date') ?: null,
        ];

        // Verificar email único
        $email = trim($this->input('email'));
        $existing = $this->userModel->findByEmail($email);
        
        if ($existing && $existing['id'] != $user['id']) {
            $this->flash('error', 'Este email já está em uso.');
            $this->redirect('account');
        }

        $data['email'] = $email;

        $this->userModel->update($user['id'], $data);

        // Atualizar sessão
        $_SESSION['user']['name'] = $data['name'];
        $_SESSION['user']['email'] = $data['email'];

        $this->flash('success', 'Dados atualizados com sucesso!');
        $this->redirect('account');
    }

    public function updateAddress(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
        }

        $user = $this->currentUser();

        $data = [
            'user_id' => $user['id'],
            'street' => trim($this->input('street')),
            'number' => trim($this->input('number')),
            'complement' => trim($this->input('complement')),
            'neighborhood' => trim($this->input('neighborhood')),
            'city' => trim($this->input('city')),
            'state' => trim($this->input('state')),
            'zip_code' => trim($this->input('zip_code')),
        ];

        $existing = $this->addressModel->getDefaultByUser($user['id']);

        if ($existing) {
            $this->addressModel->update($existing['id'], $data);
        } else {
            $this->addressModel->create($data);
        }

        $this->json(['success' => true, 'message' => 'Endereço atualizado!']);
    }

    public function changePassword(): void
    {
        $user = $this->currentUser();
        $layout = $this->getLayoutByUserType($user['user_type']);

        $this->setLayout($layout);
        $this->view('account/change-password', [
            'title' => 'Alterar Senha',
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function updatePassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('account/password');
        }

        $user = $this->currentUser();
        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');

        // Buscar usuário com senha
        $userData = $this->userModel->findByEmail($user['email']);

        if (!$this->userModel->verifyPassword($currentPassword, $userData['password'])) {
            $this->flash('error', 'Senha atual incorreta.');
            $this->redirect('account/password');
        }

        if (strlen($newPassword) < 6) {
            $this->flash('error', 'A nova senha deve ter pelo menos 6 caracteres.');
            $this->redirect('account/password');
        }

        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'As senhas não coincidem.');
            $this->redirect('account/password');
        }

        $this->userModel->updatePassword($user['id'], $newPassword);

        $this->flash('success', 'Senha alterada com sucesso!');
        $this->redirect('account');
    }

    protected function getLayoutByUserType(string $type): string
    {
        return match ($type) {
            'admin' => 'admin',
            'employee' => 'employee',
            'professional' => 'professional',
            default => 'dashboard',
        };
    }
}
