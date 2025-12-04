<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($project['title']) ?></h1>
            <p class="text-gray-600 text-sm max-w-2xl mt-1">
                <?= htmlspecialchars($project['description'] ?? '') ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <?php
            $status = $project['status'] ?? 'open';
            $statusLabels = [
                'open' => 'Aberto',
                'in_progress' => 'Em andamento',
                'completed' => 'Concluído',
                'cancelled' => 'Cancelado',
            ];
            $statusClasses = [
                'open' => 'bg-green-100 text-green-700',
                'in_progress' => 'bg-blue-100 text-blue-700',
                'completed' => 'bg-gray-100 text-gray-700',
                'cancelled' => 'bg-red-100 text-red-700',
            ];
            $statusLabel = $statusLabels[$status] ?? $status;
            $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
            ?>
            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                <?= $statusLabel ?>
            </span>
            <a href="<?= $this->url('projects') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Categoria</p>
            <p class="text-lg font-semibold text-gray-800">
                <?= htmlspecialchars($project['category'] ?? 'Geral') ?>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Orçamento</p>
            <?php if (!empty($project['budget_min']) && !empty($project['budget_max'])): ?>
                <p class="text-lg font-semibold text-gray-800">
                    R$ <?= number_format((float)$project['budget_min'], 0, ',', '.') ?> -
                    R$ <?= number_format((float)$project['budget_max'], 0, ',', '.') ?>
                </p>
            <?php else: ?>
                <p class="text-lg font-semibold text-gray-800">Não informado</p>
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Propostas</p>
            <p class="text-2xl font-bold text-gray-800"><?= (int)($project['proposals_count'] ?? 0) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Prazo</p>
            <p class="text-lg font-semibold text-gray-800">
                <?= !empty($project['deadline']) ? date('d/m/Y', strtotime($project['deadline'])) : 'Não definido' ?>
            </p>
        </div>
    </div>

    <!-- Habilidades -->
    <?php if (!empty($project['skills_required']) && is_array($project['skills_required'])): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-lucide="stars" class="w-5 h-5 text-orange-500"></i>
            Habilidades necessárias
        </h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($project['skills_required'] as $skill): ?>
                <?php if (!empty($skill)): ?>
                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                    <?= htmlspecialchars($skill) ?>
                </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Propostas -->
    <?php if (!empty($isOwner)): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="message-square" class="w-5 h-5 text-blue-600"></i>
                Propostas recebidas
            </h2>
        </div>

        <?php if (empty($proposals)): ?>
            <p class="text-sm text-gray-500">Nenhuma proposta enviada ainda para este projeto.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($proposals as $proposal): ?>
                <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold">
                                <?= strtoupper(substr($proposal['professional_name'] ?? 'F', 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($proposal['professional_name'] ?? 'Profissional') ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= htmlspecialchars($proposal['professional_email'] ?? '') ?>
                                </p>
                                <?php if (isset($proposal['avg_rating'])): ?>
                                    <p class="text-xs text-yellow-600 mt-1">
                                        Média de avaliação: <?= number_format((float)$proposal['avg_rating'], 1) ?>/5
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">
                            <?= nl2br(htmlspecialchars($proposal['cover_letter'] ?? '')) ?>
                        </p>
                    </div>

                    <div class="w-full md:w-64 flex flex-col items-start md:items-end gap-2">
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Valor proposto</p>
                            <p class="text-lg font-semibold text-gray-800">
                                R$ <?= number_format((float)$proposal['proposed_value'], 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Prazo estimado</p>
                            <p class="text-sm font-medium text-gray-800">
                                <?= (int)$proposal['estimated_days'] ?> dia(s)
                            </p>
                        </div>
                        <?php
                        $pStatus = $proposal['status'] ?? 'pending';
                        $pLabelMap = [
                            'pending' => 'Pendente',
                            'accepted' => 'Aceita',
                            'rejected' => 'Rejeitada',
                            'withdrawn' => 'Retirada',
                        ];
                        $pClassMap = [
                            'pending' => 'bg-gray-100 text-gray-700',
                            'accepted' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'withdrawn' => 'bg-yellow-100 text-yellow-700',
                        ];
                        $pLabel = $pLabelMap[$pStatus] ?? $pStatus;
                        $pClass = $pClassMap[$pStatus] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="px-2 py-1 text-xs rounded-full <?= $pClass ?>">
                            <?= $pLabel ?>
                        </span>

                        <?php if ($pStatus === 'pending' && empty($project['selected_proposal_id'])): ?>
                        <div class="flex flex-wrap gap-2 justify-end mt-2">
                            <button type="button"
                                    onclick="acceptProposal(<?= (int)$project['id'] ?>, <?= (int)$proposal['id'] ?>)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700 transition">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                Aceitar proposta
                            </button>
                            <button type="button"
                                    onclick="rejectProposal(<?= (int)$project['id'] ?>, <?= (int)$proposal['id'] ?>)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs hover:bg-red-100 transition">
                                <i data-lucide="x" class="w-3 h-3"></i>
                                Rejeitar
                            </button>
                        </div>
                        <?php elseif (!empty($project['selected_proposal_id']) && (int)$project['selected_proposal_id'] === (int)$proposal['id']): ?>
                        <p class="text-xs text-emerald-700 font-medium mt-2 flex items-center gap-1">
                            <i data-lucide="badge-check" class="w-3 h-3"></i>
                            Proposta escolhida para contrato
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $thread = $messagesByProposal[$proposal['id']] ?? []; ?>
                <div class="mt-3 border border-gray-100 rounded-lg bg-gray-50 p-3">
                    <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <i data-lucide="messages-square" class="w-4 h-4 text-gray-500"></i>
                        Chat com o profissional
                    </p>
                    <div class="max-h-48 overflow-y-auto space-y-2 mb-2 bg-white rounded-md border border-gray-100 p-2">
                        <?php if (empty($thread)): ?>
                            <p class="text-xs text-gray-400">Nenhuma mensagem ainda. Envie a primeira mensagem para iniciar a conversa.</p>
                        <?php else: ?>
                            <?php foreach ($thread as $msg): ?>
                                <?php $isMe = isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$msg['sender_id']; ?>
                                <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                                    <div class="max-w-[80%] rounded-lg px-3 py-2 text-xs <?= $isMe ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800' ?>">
                                        <p class="font-semibold mb-1">
                                            <?= htmlspecialchars($msg['sender_name'] ?? ($isMe ? 'Você' : 'Outro')) ?>
                                        </p>
                                        <p class="whitespace-pre-line">
                                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        </p>
                                        <p class="mt-1 text-[10px] opacity-75 text-right">
                                            <?= !empty($msg['created_at']) ? date('d/m H:i', strtotime($msg['created_at'])) : '' ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= $this->url("projects/{$project['id']}/proposals/{$proposal['id']}/message") ?>" class="flex gap-2">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        <input type="text" name="message" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="Escreva uma mensagem para este profissional...">
                        <button type="submit" class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700 transition">
                            <i data-lucide="send" class="w-3 h-3 mr-1"></i>
                            Enviar
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
async function acceptProposal(projectId, proposalId) {
    if (!confirm('Aceitar esta proposta e gerar contrato? As outras propostas serão marcadas como rejeitadas.')) {
        return;
    }

    try {
        const response = await fetch('<?= $this->url('projects') ?>/' + projectId + '/proposals/' + proposalId + '/accept', {
            method: 'POST'
        });

        const data = await response.json();
        if (data.success) {
            alert('Proposta aceita e contrato criado!');
            window.location.href = '<?= $this->url('contracts') ?>';
        } else {
            alert(data.error || 'Erro ao aceitar proposta.');
        }
    } catch (e) {
        alert('Erro ao aceitar proposta.');
    }
}

async function rejectProposal(projectId, proposalId) {
    if (!confirm('Rejeitar esta proposta?')) {
        return;
    }

    try {
        const response = await fetch('<?= $this->url('projects') ?>/' + projectId + '/proposals/' + proposalId + '/reject', {
            method: 'POST'
        });

        const data = await response.json();
        if (data.success) {
            alert('Proposta rejeitada.');
            window.location.reload();
        } else {
            alert(data.error || 'Erro ao rejeitar proposta.');
        }
    } catch (e) {
        alert('Erro ao rejeitar proposta.');
    }
}
</script>
