<!-- Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Usuários</h1>
    <p class="text-gray-600">Gerencie todos os usuários da plataforma</p>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Usuário</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Tipo</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Status</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">Cadastro</th>
                <th class="text-right px-6 py-4 text-sm font-semibold text-gray-600">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $user): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full 
                        <?= $user['user_type'] === 'admin' ? 'bg-red-100 text-red-700' : '' ?>
                        <?= $user['user_type'] === 'company' ? 'bg-blue-100 text-blue-700' : '' ?>
                        <?= $user['user_type'] === 'professional' ? 'bg-purple-100 text-purple-700' : '' ?>
                        <?= $user['user_type'] === 'employee' ? 'bg-gray-100 text-gray-700' : '' ?>">
                        <?php
                        $types = [
                            'admin' => 'Admin',
                            'company' => 'Empresa',
                            'professional' => 'Profissional',
                            'employee' => 'Funcionário'
                        ];
                        echo $types[$user['user_type']] ?? $user['user_type'];
                        ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $user['status'] === 'active' ? 'Ativo' : 'Bloqueado' ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-4">
                        <a href="<?= $this->url("admin/users/{$user['id']}") ?>" class="text-sm text-blue-600 hover:underline">
                            Visualizar
                        </a>
                        <?php if ($user['user_type'] !== 'admin'): ?>
                        <button onclick="toggleUser(<?= $user['id'] ?>)" class="text-sm <?= $user['status'] === 'active' ? 'text-red-600 hover:underline' : 'text-green-600 hover:underline' ?>">
                            <?= $user['status'] === 'active' ? 'Bloquear' : 'Ativar' ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
async function toggleUser(userId) {
    if (!confirm('Tem certeza?')) return;
    
    try {
        const response = await fetch(`<?= $this->url('admin/users/') ?>${userId}/toggle`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro');
        }
    } catch (error) {
        alert('Erro ao processar');
    }
}
</script>
