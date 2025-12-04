<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Histórico de Pagamentos</h1>
            <p class="text-gray-600 text-sm">Veja os pagamentos realizados pela sua empresa na plataforma.</p>
        </div>
    </div>

    <?php if (empty($payments)): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="receipt" class="w-8 h-8 text-gray-500"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Nenhum pagamento registrado</h2>
            <p class="text-gray-600">Assim que você fizer uma assinatura ou pagamento, ele aparecerá aqui.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Data</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Descrição</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($payments as $payment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= !empty($payment['created_at']) ? date('d/m/Y H:i', strtotime($payment['created_at'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800">
                            <?= htmlspecialchars($payment['description'] ?? '') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php
                            $type = $payment['type'] ?? 'subscription';
                            $typeLabels = [
                                'registration' => 'Registro',
                                'subscription' => 'Assinatura',
                                'playbook' => 'Playbook',
                                'contract' => 'Contrato',
                            ];
                            echo $typeLabels[$type] ?? $type;
                            ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $status = $payment['status'] ?? 'pending';
                            $labelMap = [
                                'pending' => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'received' => 'Recebido',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                            ];
                            $classMap = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-green-100 text-green-700',
                                'received' => 'bg-blue-100 text-blue-700',
                                'failed' => 'bg-red-100 text-red-700',
                                'refunded' => 'bg-gray-100 text-gray-700',
                            ];
                            $label = $labelMap[$status] ?? $status;
                            $class = $classMap[$status] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $class ?>"><?= $label ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-800">
                            R$ <?= number_format((float)($payment['amount'] ?? 0), 2, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
