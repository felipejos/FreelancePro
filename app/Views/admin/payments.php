<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pagamentos</h1>
            <p class="text-gray-600 text-sm">Acompanhe todas as transações realizadas na plataforma</p>
        </div>
    </div>

    <!-- Empty state -->
    <?php if (empty($payments)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="credit-card" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum pagamento encontrado</h3>
            <p class="text-gray-500 text-sm">As transações aparecerão aqui conforme os clientes forem utilizando a plataforma.</p>
        </div>
    <?php else: ?>
        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                <?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                ID #<?= (int) $payment['user_id'] ?>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <?php
                                $types = [
                                    'registration' => 'Registro',
                                    'subscription' => 'Assinatura',
                                    'playbook'     => 'Playbook',
                                    'contract'     => 'Contrato',
                                ];
                                $typeLabel = $types[$payment['type']] ?? ucfirst($payment['type']);
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    <?= htmlspecialchars($typeLabel) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-800 whitespace-nowrap">
                                R$ <?= number_format((float) $payment['amount'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <?php
                                $status = $payment['status'];
                                $statusClasses = [
                                    'pending'   => 'bg-yellow-50 text-yellow-700',
                                    'confirmed' => 'bg-green-50 text-green-700',
                                    'received'  => 'bg-green-50 text-green-700',
                                    'failed'    => 'bg-red-50 text-red-700',
                                    'refunded'  => 'bg-gray-100 text-gray-700',
                                ];
                                $labels = [
                                    'pending'   => 'Pendente',
                                    'confirmed' => 'Confirmado',
                                    'received'  => 'Recebido',
                                    'failed'    => 'Falhou',
                                    'refunded'  => 'Reembolsado',
                                ];
                                $cls = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                                $label = $labels[$status] ?? ucfirst($status);
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $cls ?>">
                                    <?= htmlspecialchars($label) ?>
                                </span>
                                <?php if (!empty($payment['paid_at'])): ?>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Pago em <?= date('d/m/Y H:i', strtotime($payment['paid_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                <?= htmlspecialchars($payment['payment_method'] ?: '-') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= htmlspecialchars($payment['description'] ?: '-') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                <?php if (!empty($payment['assas_invoice_url'])): ?>
                                    <a href="<?= htmlspecialchars($payment['assas_invoice_url']) ?>" target="_blank" class="text-blue-600 hover:underline text-xs">
                                        Ver boleto/link
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
