<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Playbooks</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['playbooks'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i data-lucide="book-open" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Cursos</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['courses'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i data-lucide="graduation-cap" class="w-6 h-6 text-purple-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Funcionários</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['employees'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-emerald-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Projetos</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['projects'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i data-lucide="briefcase" class="w-6 h-6 text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Status -->
<?php if ($subscription): ?>
<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 mb-8 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-blue-100">Plano atual</p>
            <p class="text-2xl font-bold"><?= htmlspecialchars($subscription['plan_name']) ?></p>
            <p class="text-blue-100 text-sm mt-1">Válido até <?= date('d/m/Y', strtotime($subscription['current_period_end'])) ?></p>
        </div>
        <a href="<?= $this->url('subscription') ?>" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
            Gerenciar
        </a>
    </div>
</div>
<?php else: ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-yellow-800 font-semibold">Você ainda não tem uma assinatura ativa</p>
            <p class="text-yellow-700 text-sm">Assine um plano para começar a criar treinamentos.</p>
        </div>
        <a href="<?= $this->url('plans') ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition">
            Ver planos
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="<?= $this->url('playbooks/create') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">
            <i data-lucide="plus" class="w-6 h-6 text-blue-600"></i>
        </div>
        <h3 class="font-semibold text-gray-800">Criar Playbook</h3>
        <p class="text-sm text-gray-500 mt-1">Gere treinamentos com IA</p>
    </a>
    
    <a href="<?= $this->url('courses/create') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
            <i data-lucide="plus" class="w-6 h-6 text-purple-600"></i>
        </div>
        <h3 class="font-semibold text-gray-800">Criar Curso</h3>
        <p class="text-sm text-gray-500 mt-1">Monte cursos completos com IA</p>
    </a>
    
    <a href="<?= $this->url('employees/create') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition">
            <i data-lucide="user-plus" class="w-6 h-6 text-emerald-600"></i>
        </div>
        <h3 class="font-semibold text-gray-800">Adicionar Funcionário</h3>
        <p class="text-sm text-gray-500 mt-1">Cadastre novos colaboradores</p>
    </a>
</div>

<!-- Recent Items -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Playbooks -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Playbooks Recentes</h3>
            <a href="<?= $this->url('playbooks') ?>" class="text-blue-600 text-sm hover:underline">Ver todos</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentPlaybooks)): ?>
                <p class="text-gray-500 text-center py-4">Nenhum playbook criado ainda.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentPlaybooks as $playbook): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($playbook['title']) ?></p>
                            <p class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($playbook['created_at'])) ?></p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full <?= $playbook['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                            <?= $playbook['status'] === 'published' ? 'Publicado' : 'Rascunho' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Training Stats -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="font-semibold text-gray-800">Desempenho dos Treinamentos</h3>
        </div>
        <div class="p-6">
            <?php if (!empty($trainingStats['total'])): ?>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Taxa de conclusão</span>
                            <span class="font-medium"><?= round(($trainingStats['completed'] / $trainingStats['total']) * 100) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= ($trainingStats['completed'] / $trainingStats['total']) * 100 ?>%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Taxa de aprovação</span>
                            <span class="font-medium"><?= $trainingStats['completed'] > 0 ? round(($trainingStats['passed'] / $trainingStats['completed']) * 100) : 0 ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: <?= $trainingStats['completed'] > 0 ? ($trainingStats['passed'] / $trainingStats['completed']) * 100 : 0 ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t">
                        <p class="text-sm text-gray-600">Nota média: <span class="font-semibold text-gray-800"><?= number_format($trainingStats['avg_score'] ?? 0, 1) ?>%</span></p>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum treinamento realizado ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
