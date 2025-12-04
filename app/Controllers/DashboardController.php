<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Playbook;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\PlaybookAssignment;
use App\Models\Project;
use App\Models\Contract;

/**
 * DashboardController - Dashboard da empresa
 */
class DashboardController extends Controller
{
    /**
     * Dashboard principal da empresa
     */
    public function index(): void
    {
        $user = $this->currentUser();
        $companyId = $user['id'];

        // Carregar estatísticas
        $playbookModel = new Playbook();
        $courseModel = new Course();
        $userModel = new User();
        $subscriptionModel = new Subscription();
        $assignmentModel = new PlaybookAssignment();
        $projectModel = new Project();
        $contractModel = new Contract();

        $stats = [
            'playbooks' => $playbookModel->countByCompany($companyId),
            'courses' => $courseModel->countByCompany($companyId),
            'employees' => $userModel->countWhere('company_id', $companyId),
            'projects' => count($projectModel->getByCompany($companyId)),
            'contracts' => count($contractModel->getByCompany($companyId)),
        ];

        // Assinatura ativa
        $subscription = $subscriptionModel->getActiveByCompany($companyId);

        // Estatísticas de treinamento
        $trainingStats = $assignmentModel->getStatsByCompany($companyId);

        // Últimos playbooks
        $recentPlaybooks = array_slice($playbookModel->getByCompany($companyId), 0, 5);

        // Últimos projetos
        $recentProjects = array_slice($projectModel->getByCompany($companyId), 0, 5);

        $this->setLayout('dashboard');
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'subscription' => $subscription,
            'trainingStats' => $trainingStats,
            'recentPlaybooks' => $recentPlaybooks,
            'recentProjects' => $recentProjects,
        ]);
    }
}
