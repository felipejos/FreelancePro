<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Violações de Conteúdo</h1>
            <p class="text-sm text-gray-500 mt-1">Monitoramento de tentativas de contato externo</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($violations)): ?>
            <div class="p-8 text-center text-gray-500">
                <i data-lucide="shield-check" class="w-12 h-12 mx-auto mb-4 text-green-300"></i>
                <p>Nenhuma violação pendente de revisão.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contexto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conteúdo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Violações</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($violations as $v): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($v['user_name'] ?? 'N/A') ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($v['user_email'] ?? '') ?></div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= htmlspecialchars($v['context']) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($v['content']) ?>">
                                        <?= htmlspecialchars(substr($v['content'], 0, 100)) ?>...
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $violations_data = json_decode($v['violations_json'] ?? '[]', true);
                                    foreach ($violations_data as $viol):
                                        $badgeColor = $viol['severity'] === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700';
                                    ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-xs <?= $badgeColor ?> mr-1 mb-1">
                                            <?= htmlspecialchars($viol['type']) ?>: <?= htmlspecialchars(substr($viol['match'], 0, 20)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($v['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button onclick="approveViolation(<?= $v['id'] ?>)"
                                                class="px-3 py-1 text-xs bg-green-50 text-green-700 rounded hover:bg-green-100 transition">
                                            Aprovar
                                        </button>
                                        <button onclick="rejectViolation(<?= $v['id'] ?>, 'warning')"
                                                class="px-3 py-1 text-xs bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition">
                                            Aviso
                                        </button>
                                        <button onclick="rejectViolation(<?= $v['id'] ?>, 'block')"
                                                class="px-3 py-1 text-xs bg-red-50 text-red-700 rounded hover:bg-red-100 transition">
                                            Bloquear
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
async function approveViolation(id) {
    if (!confirm('Aprovar esta violação (descartar)?')) return;
    
    try {
        const res = await fetch(`<?= $this->url('admin/violations') ?>/${id}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao aprovar');
        }
    } catch (e) {
        console.error(e);
        alert('Erro ao processar');
    }
}

async function rejectViolation(id, action) {
    const actionText = action === 'block' ? 'bloquear o usuário' : 'emitir aviso';
    if (!confirm(`Rejeitar violação e ${actionText}?`)) return;
    
    try {
        const res = await fetch(`<?= $this->url('admin/violations') ?>/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao rejeitar');
        }
    } catch (e) {
        console.error(e);
        alert('Erro ao processar');
    }
}
</script>
