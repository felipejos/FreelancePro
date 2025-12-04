<!-- Welcome -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 mb-8 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Olá, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>!</h1>
            <p class="text-purple-200 mt-1">Encontre projetos e gerencie seus contratos</p>
        </div>
        <div class="text-right">
            <p class="text-purple-200 text-sm">Avaliação média</p>
            <div class="flex items-center gap-1 mt-1">
                <i data-lucide="star" class="w-5 h-5 text-yellow-400 fill-yellow-400"></i>
                <span class="text-xl font-bold"><?= number_format($avgRating, 1) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Propostas Enviadas</p>
        <p class="text-3xl font-bold text-purple-600"><?= $stats['proposals'] ?></p>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Contratos Ativos</p>
        <p class="text-3xl font-bold text-blue-600"><?= $stats['active_contracts'] ?></p>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Projetos Concluídos</p>
        <p class="text-3xl font-bold text-green-600"><?= $stats['completed'] ?></p>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="<?= $this->url('professional/projects') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <i data-lucide="search" class="w-6 h-6 text-purple-600"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-800">Buscar Projetos</p>
            <p class="text-sm text-gray-500">Encontre novas oportunidades</p>
        </div>
    </a>
    
    <a href="<?= $this->url('professional/proposals') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <i data-lucide="send" class="w-6 h-6 text-blue-600"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-800">Minhas Propostas</p>
            <p class="text-sm text-gray-500">Acompanhe suas propostas</p>
        </div>
    </a>
</div>

<!-- Recent Contracts -->
<?php if (!empty($contracts)): ?>
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Contratos Recentes</h2>
        <a href="<?= $this->url('professional/contracts') ?>" class="text-purple-600 text-sm hover:underline">Ver todos</a>
    </div>
    <div class="divide-y">
        <?php foreach ($contracts as $contract): ?>
        <div class="p-6 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($contract['project_title']) ?></p>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($contract['company_name']) ?></p>
            </div>
            <div class="text-right">
                <p class="font-semibold text-gray-800">R$ <?= number_format($contract['professional_amount'], 2, ',', '.') ?></p>
                <span class="text-xs px-2 py-1 rounded-full <?= $contract['status'] === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                    <?= $contract['status'] === 'active' ? 'Ativo' : 'Concluído' ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
