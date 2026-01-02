<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Playbook;
use App\Models\PlaybookAssignment;
use App\Models\PlaybookAnswer;
use App\Models\PlaybookQuestion;
use App\Models\CourseEnrollment;
use App\Models\Course;

/**
 * EmployeeController - Gerenciamento de Funcionários
 */
class EmployeeController extends Controller
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Listar funcionários da empresa
     */
    public function index(): void
    {
        $user = $this->currentUser();
        $employees = $this->userModel->getEmployeesByCompany($user['id']);

        $this->setLayout('dashboard');
        $this->view('employees/index', [
            'title' => 'Funcionários',
            'employees' => $employees,
        ]);
    }

    /**
     * Página de criar funcionário
     */
    public function create(): void
    {
        $this->setLayout('dashboard');
        $this->view('employees/create', [
            'title' => 'Novo Funcionário',
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Salvar novo funcionário
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect('employees/create');
        }

        $user = $this->currentUser();

        $data = [
            'name' => trim($this->input('name')),
            'email' => trim($this->input('email')),
            'password' => $this->input('password'),
            'user_type' => 'employee',
            'company_id' => $user['id'],
            'status' => 'active',
        ];

        // Validações
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            $this->flash('error', 'Preencha todos os campos obrigatórios.');
            $this->redirect('employees/create');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email inválido.');
            $this->redirect('employees/create');
        }

        if ($this->userModel->findByEmail($data['email'])) {
            $this->flash('error', 'Este email já está cadastrado.');
            $this->redirect('employees/create');
        }

        $employeeId = $this->userModel->createUser($data);

        if (!$employeeId) {
            $this->flash('error', 'Erro ao criar funcionário.');
            $this->redirect('employees/create');
        }

        $this->flash('success', 'Funcionário criado com sucesso!');
        $this->redirect('employees');
    }

    /**
     * Visualizar perfil do funcionário
     */
    public function show(int $id): void
    {
        $user = $this->currentUser();
        $employee = $this->userModel->find($id);

        if (!$employee || $employee['company_id'] != $user['id'] || $employee['user_type'] != 'employee') {
            $this->flash('error', 'Funcionário não encontrado.');
            $this->redirect('employees');
        }

        // Buscar treinamentos atribuídos
        $assignmentModel = new PlaybookAssignment();
        $assignments = $assignmentModel->getByEmployee($id);

        // Buscar cursos matriculados
        $enrollmentModel = new CourseEnrollment();
        $enrollments = $enrollmentModel->getByEmployee($id);

        // Buscar cursos publicados da empresa para matrícula
        $courseModel = new Course();
        $courses = $courseModel->getPublishedByCompany($user['id']);

        $this->setLayout('dashboard');
        $this->view('employees/show', [
            'title' => $employee['name'],
            'employee' => $employee,
            'assignments' => $assignments,
            'enrollments' => $enrollments,
            'courses' => $courses,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Editar funcionário
     */
    public function edit(int $id): void
    {
        $user = $this->currentUser();
        $employee = $this->userModel->find($id);

        if (!$employee || $employee['company_id'] != $user['id'] || $employee['user_type'] != 'employee') {
            $this->flash('error', 'Funcionário não encontrado.');
            $this->redirect('employees');
        }

        $this->setLayout('dashboard');
        $this->view('employees/edit', [
            'title' => 'Editar: ' . $employee['name'],
            'employee' => $employee,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Atualizar funcionário
     */
    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token inválido.');
            $this->redirect("employees/{$id}/edit");
        }

        $user = $this->currentUser();
        $employee = $this->userModel->find($id);

        if (!$employee || $employee['company_id'] != $user['id']) {
            $this->flash('error', 'Funcionário não encontrado.');
            $this->redirect('employees');
        }

        $data = [
            'name' => trim($this->input('name')),
            'email' => trim($this->input('email')),
            'status' => $this->input('status', 'active'),
        ];

        // Verificar se email já existe (outro usuário)
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            $this->flash('error', 'Este email já está em uso.');
            $this->redirect("employees/{$id}/edit");
        }

        // Atualizar senha se informada
        $password = $this->input('password');
        if (!empty($password)) {
            $this->userModel->updatePassword($id, $password);
        }

        $this->userModel->update($id, $data);

        $this->flash('success', 'Funcionário atualizado com sucesso!');
        $this->redirect("employees/{$id}");
    }

    /**
     * Deletar funcionário
     */
    public function delete(int $id): void
    {
        $user = $this->currentUser();
        $employee = $this->userModel->find($id);

        if (!$employee || $employee['company_id'] != $user['id']) {
            $this->json(['error' => 'Funcionário não encontrado'], 404);
            return;
        }

        $this->userModel->delete($id);

        $this->json([
            'success' => true,
            'message' => 'Funcionário excluído com sucesso!',
        ]);
    }

    /**
     * Resetar treinamento do funcionário
     */
    public function resetTraining(int $employeeId, int $assignmentId): void
    {
        $user = $this->currentUser();
        $employee = $this->userModel->find($employeeId);

        if (!$employee || $employee['company_id'] != $user['id']) {
            $this->json(['error' => 'Funcionário não encontrado'], 404);
            return;
        }

        $assignmentModel = new PlaybookAssignment();
        $assignment = $assignmentModel->find($assignmentId);

        if (!$assignment || $assignment['employee_id'] != $employeeId) {
            $this->json(['error' => 'Treinamento não encontrado'], 404);
            return;
        }

        $assignmentModel->reset($assignmentId);

        $this->json([
            'success' => true,
            'message' => 'Treinamento resetado com sucesso!',
        ]);
    }
}
