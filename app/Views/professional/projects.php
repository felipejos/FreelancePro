<!-- Header -->
<div class="mb-4">
    <h1 class="text-2xl font-bold text-gray-800">Projetos Disponíveis</h1>
    <p class="text-gray-600">Encontre projetos e envie suas propostas</p>
</div>

<!-- Filtros -->
<?php
    $filters = $filters ?? [];
?>
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
            <input type="text" name="search" placeholder="Título ou descrição"
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Valor mínimo (R$)</label>
                <input type="number" name="budget_min" step="1" min="0"
                       value="<?= htmlspecialchars($filters['budget_min'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Valor máximo (R$)</label>
                <input type="number" name="budget_max" step="1" min="0"
                       value="<?= htmlspecialchars($filters['budget_max'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prazo a partir de</label>
                <input type="date" name="deadline_from"
                       value="<?= htmlspecialchars($filters['deadline_from'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prazo até</label>
                <input type="date" name="deadline_to"
                       value="<?= htmlspecialchars($filters['deadline_to'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mín. propostas</label>
                <input type="number" name="min_proposals" min="0" step="1"
                       value="<?= htmlspecialchars($filters['min_proposals'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Máx. propostas</label>
                <input type="number" name="max_proposals" min="0" step="1"
                       value="<?= htmlspecialchars($filters['max_proposals'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
        </div>
        <div class="md:col-span-2 flex flex-wrap justify-end gap-2">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Aplicar filtros
            </button>
            <a href="<?= $this->url('professional/projects') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                Limpar filtros
            </a>
        </div>
    </div>
</form>

<?php if (empty($projects)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="search" class="w-8 h-8 text-purple-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum projeto encontrado</h3>
    <p class="text-gray-600">
        <?php if (!empty($hasFilters)): ?>
            Nenhum projeto corresponde aos filtros selecionados. Tente ajustar os filtros.
        <?php else: ?>
            Não há projetos abertos no momento. Volte mais tarde.
        <?php endif; ?>
    </p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($projects as $project): ?>
    <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <a href="<?= $this->url("professional/projects/{$project['id']}") ?>" class="text-lg font-semibold text-gray-800 hover:text-purple-600">
                    <?= htmlspecialchars($project['title']) ?>
                </a>
                <p class="text-sm text-gray-500 mt-1">Por <?= htmlspecialchars($project['company_name']) ?></p>
                <p class="text-gray-600 mt-2 line-clamp-2"><?= htmlspecialchars($project['description']) ?></p>
                
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                    <?php if ($project['budget_min'] && $project['budget_max']): ?>
                    <span class="flex items-center gap-1">
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                        R$ <?= number_format($project['budget_min'], 0) ?> - <?= number_format($project['budget_max'], 0) ?>
                    </span>
                    <?php endif; ?>
                    <span class="flex items-center gap-1">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                        <?= $project['proposals_count'] ?> proposta(s)
                    </span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                    </span>
                </div>
            </div>
            
            <a href="<?= $this->url("professional/projects/{$project['id']}") ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                Ver Projeto
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
