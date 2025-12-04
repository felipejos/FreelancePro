<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Projetos</h1>
        <p class="text-gray-600">Gerencie projetos e encontre freelancers</p>
    </div>
    <a href="<?= $this->url('projects/create') ?>" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
        <i data-lucide="plus" class="w-5 h-5"></i>
        <span>Novo Projeto</span>
    </a>
</div>

<!-- Projects List -->
<?php if (empty($projects)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="briefcase" class="w-8 h-8 text-orange-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum projeto ainda</h3>
    <p class="text-gray-600 mb-4">Crie um projeto para encontrar profissionais qualificados.</p>
    <a href="<?= $this->url('projects/create') ?>" class="inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition">
        <i data-lucide="plus" class="w-5 h-5"></i>
        Criar Projeto
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="divide-y">
        <?php foreach ($projects as $project): ?>
        <div class="p-6 hover:bg-gray-50 transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <a href="<?= $this->url("projects/{$project['id']}") ?>" class="text-lg font-semibold text-gray-800 hover:text-orange-600">
                        <?= htmlspecialchars($project['title']) ?>
                    </a>
                    <p class="text-gray-600 mt-1 line-clamp-2"><?= htmlspecialchars($project['description']) ?></p>
                    
                    <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <?= htmlspecialchars($project['category'] ?? 'Geral') ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                            <?= $project['proposals_count'] ?> proposta(s)
                        </span>
                        <?php if ($project['budget_min'] && $project['budget_max']): ?>
                        <span class="flex items-center gap-1">
                            <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                            R$ <?= number_format($project['budget_min'], 0) ?> - <?= number_format($project['budget_max'], 0) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <span class="px-3 py-1 text-sm rounded-full 
                    <?= $project['status'] === 'open' ? 'bg-green-100 text-green-700' : '' ?>
                    <?= $project['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700' : '' ?>
                    <?= $project['status'] === 'completed' ? 'bg-gray-100 text-gray-700' : '' ?>
                    <?= $project['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : '' ?>">
                    <?php
                    $statusLabels = [
                        'open' => 'Aberto',
                        'in_progress' => 'Em andamento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado'
                    ];
                    echo $statusLabels[$project['status']] ?? $project['status'];
                    ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
