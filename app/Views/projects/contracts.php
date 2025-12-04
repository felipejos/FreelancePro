<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Contratos</h1>
        <p class="text-gray-600">Acompanhe os contratos firmados com freelancers</p>
    </div>
</div>

<?php if (empty($contracts)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="file-text" class="w-8 h-8 text-blue-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum contrato ainda</h3>
    <p class="text-gray-600">Quando você aceitar uma proposta de projeto, o contrato aparecerá aqui.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Projeto</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Freelancer</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Início</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($contracts as $contract): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-800">
                    <?= htmlspecialchars($contract['project_title'] ?? ('Projeto #' . $contract['project_id'])) ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= htmlspecialchars($contract['professional_name'] ?? ('Profissional #' . $contract['professional_id'])) ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-800">
                    R$ <?= number_format((float)$contract['contract_value'], 2, ',', '.') ?>
                </td>
                <td class="px-6 py-4">
                    <?php
                    $status = $contract['status'] ?? 'active';
                    $labelMap = [
                        'active' => 'Ativo',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                        'disputed' => 'Em disputa',
                    ];
                    $classMap = [
                        'active' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        'disputed' => 'bg-yellow-100 text-yellow-700',
                    ];
                    $label = $labelMap[$status] ?? $status;
                    $class = $classMap[$status] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <span class="px-2 py-1 text-xs rounded-full <?= $class ?>">
                        <?= $label ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <?= !empty($contract['started_at']) ? date('d/m/Y', strtotime($contract['started_at'])) : '-' ?>
                </td>
                <td class="px-6 py-4 text-right text-sm">
                    <a href="<?= $this->url('contracts/' . (int)$contract['id']) ?>" class="text-blue-600 hover:underline">Ver</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
