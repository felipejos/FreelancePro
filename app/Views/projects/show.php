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
    <?php
        $selectedProposal = null;
        if (!empty($project['selected_proposal_id']) && !empty($proposals)) {
            foreach ($proposals as $proposalCandidate) {
                if ((int)$proposalCandidate['id'] === (int)$project['selected_proposal_id']) {
                    $selectedProposal = $proposalCandidate;
                    break;
                }
            }
        }
        $authUser = $_SESSION['user'] ?? [];
    ?>
    <div class="space-y-6">
        <?php if ($selectedProposal): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                            Proposta selecionada
                        </p>
                        <p class="text-gray-500 text-sm">
                            <?= htmlspecialchars($selectedProposal['professional_name'] ?? 'Profissional') ?>
                            • Valor aprovado: <strong>R$ <?= number_format((float)$selectedProposal['proposed_value'], 2, ',', '.') ?></strong>
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $selectedProposal['status'] === 'accepted_pending_payment' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                        <?= $selectedProposal['status'] === 'accepted_pending_payment' ? 'Pagamento pendente' : 'Pagamento confirmado' ?>
                    </span>
                </div>

                <?php if ($selectedProposal['status'] === 'accepted_pending_payment'): ?>
                    <p class="text-sm text-gray-600 mt-4">
                        Gere o pagamento para ativar o contrato e liberar o início do trabalho. Os dados abaixo são utilizados apenas para esta transação.
                    </p>
                    <form method="POST"
                          action="<?= $this->url("projects/{$project['id']}/proposals/{$selectedProposal['id']}/pay") ?>"
                          class="space-y-4 mt-4 border-t border-gray-100 pt-4">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Nome completo</label>
                                <input type="text" name="billing_name" required
                                       value="<?= htmlspecialchars($authUser['name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">E-mail</label>
                                <input type="email" name="billing_email" required
                                       value="<?= htmlspecialchars($authUser['email'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">CPF/CNPJ</label>
                                <input type="text" name="billing_document" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                       placeholder="Somente números">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Telefone</label>
                                <input type="text" name="billing_phone"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                       placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">CEP</label>
                                <input type="text" name="billing_zip"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Rua</label>
                                <input type="text" name="billing_street"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus-border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Número</label>
                                <input type="text" name="billing_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus-border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Complemento</label>
                                <input type="text" name="billing_complement"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus-border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Bairro</label>
                                <input type="text" name="billing_neighborhood"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus-border-transparent">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Nome no cartão</label>
                                <input type="text" name="card_holder" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Número do cartão</label>
                                <input type="text" name="card_number" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                       placeholder="Somente números">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Mês</label>
                                    <input type="text" name="card_month" maxlength="2" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                           placeholder="MM">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Ano</label>
                                    <input type="text" name="card_year" maxlength="4" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                           placeholder="AAAA">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">CVV</label>
                                    <input type="text" name="card_cvv" maxlength="4" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-6">
                            <label class="inline-flex items-start gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="accept_terms" value="1" required class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>
                                    Li e aceito os termos de uso e contrato. O pagamento gera o recibo e confirma a contratação.
                                </span>
                            </label>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                Pagar proposta agora
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="mt-4 flex flex-col gap-2 text-sm text-emerald-700">
                        <div class="flex items-center gap-3">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            <span>Pagamento confirmado! Um recibo está disponível em “Pagamentos > Histórico”.</span>
                        </div>
                        <?php if (!empty($proposalsPayments[$selectedProposal['id']]['assas_invoice_url'] ?? null)): ?>
                            <a href="<?= htmlspecialchars($proposalsPayments[$selectedProposal['id']]['assas_invoice_url']) ?>"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 text-emerald-700 hover:underline">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                Ver recibo agora
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($selectedContract)): ?>
                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                                    Contrato de serviço • <?= htmlspecialchars($project['title']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Contrato #<?= (int)$selectedContract['id'] ?> • atualize o PDF assinado para liberar ao profissional.
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= !empty($selectedContract['service_contract_path']) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' ?>">
                                <?= !empty($selectedContract['service_contract_path']) ? 'Arquivo disponível' : 'Aguardando upload' ?>
                            </span>
                        </div>

                        <?php if (!empty($selectedContract['service_contract_path'])): ?>
                            <div class="flex items-center justify-between bg-purple-50 border border-purple-100 rounded-lg p-4">
                                <div class="text-sm text-purple-900">
                                    Último arquivo: <strong><?= htmlspecialchars($selectedContract['service_contract_original_name'] ?? 'Contrato.pdf') ?></strong>
                                </div>
                                <a href="<?= $this->url("contracts/{$selectedContract['id']}/download") ?>"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Baixar contrato
                                </a>
                            </div>
                        <?php endif; ?>

                        <form method="POST"
                              action="<?= $this->url("projects/{$project['id']}/contracts/{$selectedContract['id']}/upload") ?>"
                              enctype="multipart/form-data"
                              class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                            <label class="text-xs font-semibold text-gray-600 block">Novo arquivo do contrato (PDF)</label>
                            <input type="file" name="service_contract" accept="application/pdf"
                                   class="w-full text-xs text-gray-600 file:mr-4 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100">
                            <p class="text-xs text-gray-500">Envie apenas PDFs assinados. O arquivo ficará disponível imediatamente para o freelancer.</p>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Salvar contrato
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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
                <div class="space-y-8">
                    <?php foreach ($proposals as $proposal): ?>
                    <?php
                        $counterHistory = $counteroffersByProposal[$proposal['id']] ?? [];
                        $pendingCounter = null;
                        foreach ($counterHistory as $history) {
                            if ($history['status'] === 'pending') {
                                $pendingCounter = $history;
                                break;
                            }
                        }
                        $pStatus = $proposal['status'] ?? 'pending';
                        $negotiationStatus = $proposal['negotiation_status'] ?? 'idle';
                        $canSendCounter = $pStatus === 'pending'
                            && ($project['status'] ?? 'open') === 'open'
                            && empty($project['selected_proposal_id'])
                            && $negotiationStatus !== 'accepted'
                            && !$pendingCounter;
                        $negotiationLabels = [
                            'idle' => 'Sem negociação ativa',
                            'awaiting_professional' => 'Aguardando resposta do profissional',
                            'awaiting_company' => 'Aguardando resposta da empresa',
                            'accepted' => 'Negociação concluída',
                        ];
                        $negotiationClasses = [
                            'idle' => 'bg-gray-100 text-gray-700',
                            'awaiting_professional' => 'bg-purple-100 text-purple-700',
                            'awaiting_company' => 'bg-amber-100 text-amber-700',
                            'accepted' => 'bg-emerald-100 text-emerald-700',
                        ];
                        $pLabelMap = [
                            'pending' => 'Pendente',
                            'accepted' => 'Aceita',
                            'accepted_pending_payment' => 'Aguardando pagamento',
                            'paid' => 'Paga',
                            'rejected' => 'Rejeitada',
                            'withdrawn' => 'Retirada',
                        ];
                        $pClassMap = [
                            'pending' => 'bg-gray-100 text-gray-700',
                            'accepted' => 'bg-green-100 text-green-700',
                            'accepted_pending_payment' => 'bg-amber-100 text-amber-700',
                            'paid' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'withdrawn' => 'bg-yellow-100 text-yellow-700',
                        ];
                        $pLabel = $pLabelMap[$pStatus] ?? $pStatus;
                        $pClass = $pClassMap[$pStatus] ?? 'bg-gray-100 text-gray-700';
                        $thread = $messagesByProposal[$proposal['id']] ?? [];
                    ?>
                    <div class="border border-gray-200 rounded-xl p-4 space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold">
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
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full <?= $pClass ?>">
                                    <?= $pLabel ?>
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full <?= $negotiationClasses[$negotiationStatus] ?? 'bg-gray-100 text-gray-700' ?>">
                                    <?= $negotiationLabels[$negotiationStatus] ?? ucfirst($negotiationStatus) ?>
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Valor proposto</p>
                                <p class="text-lg font-semibold text-gray-800">
                                    R$ <?= number_format((float)$proposal['proposed_value'], 2, ',', '.') ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Prazo estimado</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    <?= (int)$proposal['estimated_days'] ?> dia(s)
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status do projeto</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    <?= htmlspecialchars(ucfirst($project['status'] ?? 'open')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($pStatus === 'pending' && empty($project['selected_proposal_id'])): ?>
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
                            <?php elseif (!empty($project['selected_proposal_id']) && (int)$project['selected_proposal_id'] === (int)$proposal['id']): ?>
                                <p class="text-xs text-emerald-700 font-medium flex items-center gap-1">
                                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                                    Proposta escolhida para contrato
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                            <div class="xl:col-span-2 bg-gray-50 border border-gray-100 rounded-lg p-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                        <i data-lucide="handshake" class="w-4 h-4 text-emerald-600"></i>
                                        Histórico de contrapropostas
                                    </p>
                                    <?php if ($pendingCounter): ?>
                                        <span class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                            Aguardando resposta
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($counterHistory)): ?>
                                    <?php $orderedHistory = array_reverse($counterHistory); ?>
                                    <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                                        <?php foreach ($orderedHistory as $counter): ?>
                                            <?php
                                                $isPending = $counter['status'] === 'pending';
                                                $accent = match ($counter['status']) {
                                                    'accepted' => 'border-emerald-100 bg-white text-emerald-800',
                                                    'rejected' => 'border-red-100 bg-white text-red-800',
                                                    default => 'border-gray-200 bg-white text-gray-800'
                                                };
                                            ?>
                                            <div class="border <?= $accent ?> rounded-lg p-3">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="text-sm font-semibold">
                                                        <?= htmlspecialchars($counter['sender_name'] ?? ($counter['sender_type'] === 'company' ? 'Empresa' : 'Profissional')) ?>
                                                        <span class="text-xs font-normal text-gray-500">
                                                            • <?= $counter['sender_type'] === 'company' ? 'Empresa' : 'Profissional' ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= date('d/m/Y H:i', strtotime($counter['created_at'])) ?>
                                                    </div>
                                                </div>
                                                <p class="text-sm mt-2">
                                                    Valor: <strong>R$ <?= number_format((float)$counter['amount'], 2, ',', '.') ?></strong> —
                                                    Prazo: <strong><?= (int)$counter['estimated_days'] ?> dia(s)</strong>
                                                </p>
                                                <?php if (!empty($counter['message'])): ?>
                                                    <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">
                                                        <?= nl2br(htmlspecialchars($counter['message'])) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="px-2 py-1 rounded-full <?= $isPending ? 'bg-yellow-100 text-yellow-700' : ($counter['status'] === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700') ?>">
                                                        <?= $counter['status'] === 'pending' ? 'Aguardando resposta' : ucfirst($counter['status']) ?>
                                                    </span>
                                                    <?php if (!empty($counter['responded_by'])): ?>
                                                        <span class="text-gray-500">
                                                            Respondido por <?= htmlspecialchars($counter['responder_name'] ?? 'Usuário') ?>
                                                            em <?= date('d/m/Y H:i', strtotime($counter['responded_at'])) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">
                                        Nenhuma contraproposta registrada ainda. Inicie uma negociação utilizando o formulário abaixo.
                                    </p>
                                <?php endif; ?>

                                <?php if ($pendingCounter && $pendingCounter['sender_type'] === 'professional'): ?>
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                        <p class="text-sm font-semibold text-amber-800 mb-2">Responder contraproposta do profissional</p>
                                        <p class="text-sm text-amber-800 mb-3">
                                            Valor sugerido: <strong>R$ <?= number_format((float)$pendingCounter['amount'], 2, ',', '.') ?></strong> —
                                            prazo de <strong><?= (int)$pendingCounter['estimated_days'] ?> dia(s)</strong>.
                                        </p>
                                        <div class="flex flex-wrap gap-3">
                                            <form method="POST" action="<?= $this->url("projects/{$project['id']}/proposals/{$proposal['id']}/counter/{$pendingCounter['id']}/accept") ?>">
                                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                    Aceitar contraproposta
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= $this->url("projects/{$project['id']}/proposals/{$proposal['id']}/counter/{$pendingCounter['id']}/reject") ?>">
                                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-amber-200 text-amber-800 text-sm hover:bg-amber-100 transition">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                    Rejeitar e manter valores atuais
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($canSendCounter): ?>
                                    <form method="POST" action="<?= $this->url("projects/{$project['id']}/proposals/{$proposal['id']}/counter") ?>" class="space-y-3">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Novo valor (R$)</label>
                                                <input type="number" name="amount" min="1" step="0.01" required
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                                       placeholder="Ex.: 4500,00">
                                            </div>
                                            <div>
                                                <label class="text-xs font-semibold text-gray-600 mb-1 block">Prazo estimado (dias)</label>
                                                <input type="number" name="estimated_days" min="1" required
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                                       placeholder="Ex.: 20">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Mensagem (opcional)</label>
                                            <textarea name="message" rows="3"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                                      placeholder="Explique o motivo dos novos valores..."></textarea>
                                        </div>
                                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition">
                                            <i data-lucide="repeat" class="w-4 h-4"></i>
                                            Enviar contraproposta ao profissional
                                        </button>
                                    </form>
                                <?php elseif (!$pendingCounter && $pStatus !== 'pending'): ?>
                                    <p class="text-sm text-gray-400">
                                        Esta proposta não pode mais ser negociada porque foi <?= $pStatus === 'accepted' ? 'aceita' : 'finalizada' ?>.
                                    </p>
                                <?php elseif ($pendingCounter && $pendingCounter['sender_type'] === 'company'): ?>
                                    <p class="text-sm text-gray-500">
                                        Aguardando resposta do profissional para a contraproposta enviada em <?= date('d/m H:i', strtotime($pendingCounter['created_at'])) ?>.
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="bg-white border border-gray-100 rounded-lg p-4 flex flex-col">
                                <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 mb-3">
                                    <i data-lucide="messages-square" class="w-4 h-4 text-gray-500"></i>
                                    Chat com o profissional
                                </p>
                                <div class="flex-1 max-h-64 overflow-y-auto space-y-2 mb-3 bg-gray-50 rounded-md border border-gray-100 p-2">
                                    <?php if (empty($thread)): ?>
                                        <p class="text-xs text-gray-400">Nenhuma mensagem ainda. Envie a primeira mensagem para iniciar a conversa.</p>
                                    <?php else: ?>
                                        <?php foreach ($thread as $msg): ?>
                                            <?php $isMe = isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$msg['sender_id']; ?>
                                            <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                                                <div class="max-w-[80%] rounded-lg px-3 py-2 text-xs <?= $isMe ? 'bg-emerald-600 text-white' : 'bg-white text-gray-800 border border-gray-200' ?>">
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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
