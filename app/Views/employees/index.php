<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Funcionários</h1>
        <p class="text-gray-600">Gerencie sua equipe e treinamentos</p>
    </div>
    <a href="<?= $this->url('employees/create') ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        <span>Novo Funcionário</span>
    </a>
</div>

<!-- Employees Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($employees)): ?>
    <div class="p-12 text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="users" class="w-8 h-8 text-emerald-600"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum funcionário cadastrado</h3>
        <p class="text-gray-600 mb-4">Cadastre funcionários para atribuir treinamentos.</p>
        <a href="<?= $this->url('employees/create') ?>" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            Cadastrar Funcionário
        </a>
    </div>
    <?php else: ?>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Funcionário</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Email</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Status</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Cadastro</th>
                <th class="text-right px-6 py-4 text-sm font-semibold text-gray-600">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($employees as $employee): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-semibold">
                            <?= strtoupper(substr($employee['name'], 0, 1)) ?>
                        </div>
                        <span class="font-medium text-gray-800"><?= htmlspecialchars($employee['name']) ?></span>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($employee['email']) ?></td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full <?= $employee['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $employee['status'] === 'active' ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($employee['created_at'])) ?></td>
                <td class="px-6 py-4 text-right">
                    <a href="<?= $this->url("employees/{$employee['id']}") ?>" class="text-blue-600 hover:underline text-sm mr-3">Ver</a>
                    <a href="<?= $this->url("employees/{$employee['id']}/edit") ?>" class="text-gray-600 hover:underline text-sm">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
