<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Logs de IA</h1>
            <p class="text-sm text-gray-600">Visão geral do uso da IA na plataforma.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Total de Chamadas</p>
            <p class="text-3xl font-bold text-gray-900"><?= (int)($stats['total_calls'] ?? 0) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Sucessos</p>
            <p class="text-3xl font-bold text-emerald-600"><?= (int)($stats['success_calls'] ?? 0) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Erros</p>
            <p class="text-3xl font-bold text-red-600"><?= (int)($stats['error_calls'] ?? 0) ?></p>
        </div>
    </div>

    <?php if (!empty($stats['by_type'])): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Uso por tipo de chamada</h2>
            <div class="space-y-2 text-sm text-gray-700">
                <?php foreach ($stats['by_type'] as $type => $count): ?>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-1 last:border-b-0">
                        <span class="capitalize"><?= htmlspecialchars($type) ?></span>
                        <span class="font-semibold"><?= (int)$count ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($stats['recent'])): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Chamadas recentes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs text-gray-500 uppercase">
                            <th class="py-2 pr-4">Data</th>
                            <th class="py-2 pr-4">Tipo</th>
                            <th class="py-2 pr-4">Usuário</th>
                            <th class="py-2 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($stats['recent'] as $log): ?>
                            <tr>
                                <td class="py-2 pr-4 text-gray-700">
                                    <?= htmlspecialchars($log['created_at'] ?? '') ?>
                                </td>
                                <td class="py-2 pr-4 text-gray-700">
                                    <?= htmlspecialchars($log['type'] ?? '-') ?>
                                </td>
                                <td class="py-2 pr-4 text-gray-700">
                                    <?= htmlspecialchars($log['user_id'] ?? '-') ?>
                                </td>
                                <td class="py-2 pr-4">
                                    <?php if (!empty($log['status']) && $log['status'] === 'error'): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Erro</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">OK</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
