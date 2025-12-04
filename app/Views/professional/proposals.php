<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Minhas Propostas</h1>
            <p class="text-gray-600 text-sm">Acompanhe todas as propostas que você enviou para projetos.</p>
        </div>
        <a href="<?= $this->url('professional/projects') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition">
            <i data-lucide="search" class="w-4 h-4"></i>
            Buscar novos projetos
        </a>
    </div>

    <?php if (empty($proposals)): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="send" class="w-8 h-8 text-purple-600"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Você ainda não enviou propostas</h2>
            <p class="text-gray-600 mb-4">Comece procurando projetos compatíveis com o seu perfil.</p>
            <a href="<?= $this->url('professional/projects') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition">
                <i data-lucide="search" class="w-4 h-4"></i>
                Buscar projetos
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Projeto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Enviada em</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor proposto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Prazo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($proposals as $proposal): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="font-medium">
                                <?= htmlspecialchars($proposal['project_title'] ?? ($proposal['project']['title'] ?? 'Projeto')) ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= htmlspecialchars($proposal['project_category'] ?? ($proposal['project']['category'] ?? '')) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= !empty($proposal['created_at']) ? date('d/m/Y H:i', strtotime($proposal['created_at'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            R$ <?= number_format((float)($proposal['proposed_value'] ?? 0), 2, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= (int)($proposal['estimated_days'] ?? 0) ?> dias
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $status = $proposal['status'] ?? 'pending';
                            $labelMap = [
                                'pending' => 'Pendente',
                                'accepted' => 'Aceita',
                                'rejected' => 'Rejeitada',
                                'withdrawn' => 'Retirada',
                            ];
                            $classMap = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'accepted' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'withdrawn' => 'bg-gray-100 text-gray-700',
                            ];
                            $label = $labelMap[$status] ?? $status;
                            $class = $classMap[$status] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium <?= $class ?>"><?= $label ?></span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="<?= $this->url('professional/projects/' . ($proposal['project_id'] ?? $proposal['project']['id'] ?? '')) ?>" class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                Ver projeto
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
