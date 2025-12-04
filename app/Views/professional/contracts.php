<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Meus Contratos</h1>
            <p class="text-gray-600 text-sm">Acompanhe os contratos ativos e concluídos com as empresas.</p>
        </div>
    </div>

    <?php if (empty($contracts)): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="file-text" class="w-8 h-8 text-purple-600"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Você ainda não possui contratos</h2>
            <p class="text-gray-600 mb-4">Quando uma empresa aceitar sua proposta, o contrato aparecerá aqui.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Projeto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($contracts as $contract): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?= htmlspecialchars($contract['project_title'] ?? '') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <?= htmlspecialchars($contract['company_name'] ?? '') ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $status = $contract['status'] ?? 'active';
                            $labelMap = [
                                'active' => 'Ativo',
                                'completed' => 'Concluído',
                                'cancelled' => 'Cancelado',
                            ];
                            $classMap = [
                                'active' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                            $label = $labelMap[$status] ?? $status;
                            $class = $classMap[$status] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium <?= $class ?>"><?= $label ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                            R$ <?= number_format((float)($contract['contract_value'] ?? 0), 2, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
