<!-- Header -->
<div class="mb-6">
    <a href="<?= $this->url('professional/projects') ?>" class="text-purple-600 hover:underline text-sm mb-2 inline-block">← Voltar aos Projetos</a>
    <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($project['title']) ?></h1>
    <p class="text-gray-500 mt-1">Publicado por <?= htmlspecialchars($project['company']['name'] ?? 'Empresa') ?></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Description -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Descrição do Projeto</h2>
            <div class="prose max-w-none text-gray-600">
                <?= nl2br(htmlspecialchars($project['description'])) ?>
            </div>
        </div>
        
        <!-- Skills -->
        <?php if (!empty($project['skills_required'])): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Habilidades Necessárias</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($project['skills_required'] as $skill): ?>
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm"><?= htmlspecialchars($skill) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Proposal Form -->
        <?php if (!$hasProposal): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Enviar Proposta</h2>
            
            <form id="proposalForm" class="space-y-4">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Carta de Apresentação</label>
                    <textarea name="cover_letter" rows="4" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder="Apresente-se e explique por que você é ideal para este projeto..."></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor Proposto (R$)</label>
                        <input type="number" name="proposed_value" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prazo Estimado (dias)</label>
                        <input type="number" name="estimated_days" min="1" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>Importante:</strong> Uma taxa de 7% será descontada do valor quando o contrato for finalizado.
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-semibold transition">
                    Enviar Proposta
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                    <p class="text-green-800 font-medium">Você já enviou uma proposta para este projeto.</p>
                </div>
                <a href="<?= $this->url('professional/proposals') ?>" class="text-green-700 hover:underline text-sm mt-2 inline-block">Ver minhas propostas</a>
            </div>

            <?php if (!empty($proposal) && !empty($proposal['id'])): ?>
            <?php
                $counterHistory = $counteroffers ?? [];
                $pendingCounter = null;
                foreach ($counterHistory as $history) {
                    if ($history['status'] === 'pending') {
                        $pendingCounter = $history;
                        break;
                    }
                }
                $proposalStatus = $proposal['status'] ?? 'pending';
                $negotiationStatus = $proposal['negotiation_status'] ?? 'idle';
                $canSendCounter = $proposalStatus === 'pending'
                    && ($project['status'] ?? 'open') === 'open'
                    && $negotiationStatus !== 'accepted'
                    && !$pendingCounter;
                $negotiationLabels = [
                    'idle' => 'Sem negociação ativa',
                    'awaiting_professional' => 'Você deve responder',
                    'awaiting_company' => 'Empresa deve responder',
                    'accepted' => 'Negociação concluída',
                ];
                $negotiationClasses = [
                    'idle' => 'bg-gray-100 text-gray-700',
                    'awaiting_professional' => 'bg-amber-100 text-amber-700',
                    'awaiting_company' => 'bg-indigo-100 text-indigo-700',
                    'accepted' => 'bg-green-100 text-green-700',
                ];
                $statusLabels = [
                    'pending' => 'Proposta pendente',
                    'accepted' => 'Contrato aprovado',
                    'accepted_pending_payment' => 'Aguardando pagamento da empresa',
                    'paid' => 'Pagamento confirmado',
                    'rejected' => 'Proposta rejeitada',
                    'withdrawn' => 'Proposta retirada',
                ];
                $statusClasses = [
                    'pending' => 'bg-gray-100 text-gray-700',
                    'accepted' => 'bg-green-100 text-green-700',
                    'accepted_pending_payment' => 'bg-yellow-100 text-yellow-700',
                    'paid' => 'bg-emerald-100 text-emerald-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'withdrawn' => 'bg-gray-200 text-gray-600',
                ];
            ?>
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-purple-600"></i>
                                Status da sua proposta
                            </p>
                            <p class="text-xs text-gray-500">
                                Enviada em <?= !empty($proposal['created_at']) ? date('d/m/Y H:i', strtotime($proposal['created_at'])) : '--' ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusClasses[$proposalStatus] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= $statusLabels[$proposalStatus] ?? ucfirst($proposalStatus) ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                            <p class="text-xs text-gray-500">Negociação</p>
                            <span class="inline-flex items-center px-3 py-1 text-xs rounded-full <?= $negotiationClasses[$negotiationStatus] ?? 'bg-gray-100 text-gray-700' ?>">
                                <?= $negotiationLabels[$negotiationStatus] ?? ucfirst($negotiationStatus) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Situação do projeto</p>
                            <p class="text-sm font-semibold text-gray-800">
                                <?= htmlspecialchars(ucfirst($project['status'] ?? 'open')) ?>
                            </p>
                        </div>
                    </div>
                    <?php if ($proposalStatus === 'accepted_pending_payment'): ?>
                        <div class="mt-4 flex items-start gap-3 text-sm text-yellow-800 bg-yellow-50 border border-yellow-100 rounded-lg p-3">
                            <i data-lucide="clock" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                            Aguarde: a empresa já aprovou você e agora está processando o pagamento. Assim que o pagamento for confirmado, você receberá um aviso e o contrato será liberado.
                        </div>
                    <?php elseif ($proposalStatus === 'paid'): ?>
                        <div class="mt-4 flex items-start gap-3 text-sm text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                            <i data-lucide="rocket" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                            Parabéns! Pagamento confirmado e contrato ativo. Verifique a área de contratos para acompanhar a execução.
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($contract)): ?>
                    <div class="mt-5 bg-white rounded-xl shadow-sm border border-purple-100 p-5">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <i data-lucide="file-text" class="w-5 h-5 text-purple-600"></i>
                                    Contrato de serviço • <?= htmlspecialchars($project['title']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Contrato #<?= (int)$contract['id'] ?> — valor bruto: R$ <?= number_format((float)($contract['contract_value'] ?? 0), 2, ',', '.') ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= !empty($contract['service_contract_path']) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' ?>">
                                <?= !empty($contract['service_contract_path']) ? 'Arquivo disponível' : 'Aguardando upload da empresa' ?>
                            </span>
                        </div>

                        <?php if (!empty($contract['service_contract_path'])): ?>
                            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-purple-50 border border-purple-100 rounded-lg p-4">
                                <div class="text-sm text-purple-900">
                                    Último arquivo: <strong><?= htmlspecialchars($contract['service_contract_original_name'] ?? 'Contrato.pdf') ?></strong>
                                </div>
                                <a href="<?= $this->url("professional/contracts/{$contract['id']}/download") ?>"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Baixar contrato (PDF)
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 flex items-start gap-3 text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-lg p-3">
                                <i data-lucide="clock" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                                Aguarde: a empresa ainda não anexou o PDF assinado. Você receberá uma notificação assim que estiver disponível.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i data-lucide="handshake" class="w-5 h-5 text-purple-600"></i>
                                Histórico de contrapropostas
                            </h2>
                            <?php if ($pendingCounter): ?>
                                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                    Aguardando resposta
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($counterHistory)): ?>
                            <div class="space-y-3 mb-5 max-h-64 overflow-y-auto pr-1">
                                <?php $orderedHistory = array_reverse($counterHistory); ?>
                                <?php foreach ($orderedHistory as $counter): ?>
                                    <?php
                                        $isPending = $counter['status'] === 'pending';
                                        $accent = match ($counter['status']) {
                                            'accepted' => 'border-green-100 bg-green-50 text-green-800',
                                            'rejected' => 'border-red-100 bg-red-50 text-red-800',
                                            default => 'border-gray-200 bg-gray-50 text-gray-800'
                                        };
                                    ?>
                                    <div class="border <?= $accent ?> rounded-lg p-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="text-sm font-semibold">
                                                <?= htmlspecialchars($counter['sender_name'] ?? ($counter['sender_type'] === 'company' ? 'Empresa' : 'Você')) ?>
                                                <span class="text-xs font-normal text-gray-500">
                                                    • <?= $counter['sender_type'] === 'company' ? 'Empresa' : 'Profissional' ?>
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?= date('d/m/Y H:i', strtotime($counter['created_at'])) ?>
                                            </div>
                                        </div>
                                        <p class="text-sm mt-2">
                                            Valor: <strong>R$ <?= number_format((float)$counter['amount'], 2, ',', '.') ?></strong> •
                                            Prazo: <strong><?= (int)$counter['estimated_days'] ?> dia(s)</strong>
                                        </p>
                                        <?php if (!empty($counter['message'])): ?>
                                            <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">
                                                <?= nl2br(htmlspecialchars($counter['message'])) ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="px-2 py-1 rounded-full <?= $isPending ? 'bg-yellow-100 text-yellow-700' : ($counter['status'] === 'accepted' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700') ?>">
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
                            <p class="text-sm text-gray-500 mb-5">
                                Ainda não houve contrapropostas neste projeto. Você pode abrir a negociação usando o formulário abaixo.
                            </p>
                        <?php endif; ?>

                        <?php if ($pendingCounter && $pendingCounter['sender_type'] === 'company'): ?>
                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-4">
                                <p class="text-sm font-semibold text-indigo-800 mb-2">Responder contraproposta da empresa</p>
                                <p class="text-sm text-indigo-800 mb-3">
                                    Valor sugerido: <strong>R$ <?= number_format((float)$pendingCounter['amount'], 2, ',', '.') ?></strong>,
                                    prazo de <strong><?= (int)$pendingCounter['estimated_days'] ?> dia(s)</strong>.
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <form method="POST" action="<?= $this->url("professional/projects/{$project['id']}/proposals/{$proposal['id']}/counter/{$pendingCounter['id']}/accept") ?>">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            Aceitar contraproposta
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= $this->url("professional/projects/{$project['id']}/proposals/{$proposal['id']}/counter/{$pendingCounter['id']}/reject") ?>">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-indigo-200 text-indigo-800 text-sm hover:bg-indigo-100 transition">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                            Rejeitar e manter valores atuais
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php elseif ($pendingCounter && $pendingCounter['sender_type'] === 'professional'): ?>
                            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4 mb-4 text-sm text-amber-800">
                                Aguarde a empresa responder à contraproposta enviada em <?= date('d/m H:i', strtotime($pendingCounter['created_at'])) ?>.
                            </div>
                        <?php endif; ?>

                        <?php if ($canSendCounter): ?>
                            <form method="POST" action="<?= $this->url("professional/projects/{$project['id']}/proposals/{$proposal['id']}/counter") ?>" class="space-y-3">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600 mb-1 block">Novo valor (R$)</label>
                                        <input type="number" name="amount" min="1" step="0.01" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Ex.: 3800,00">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600 mb-1 block">Prazo estimado (dias)</label>
                                        <input type="number" name="estimated_days" min="1" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Ex.: 18">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Mensagem para a empresa</label>
                                    <textarea name="message" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus-border-transparent"
                                              placeholder="Explique o motivo dos ajustes de valor ou prazo..."></textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition">
                                    <i data-lucide="repeat" class="w-4 h-4"></i>
                                    Enviar contraproposta para a empresa
                                </button>
                            </form>
                        <?php elseif (!$pendingCounter && $proposalStatus !== 'pending'): ?>
                            <p class="text-sm text-gray-400">
                                Esta proposta não pode mais ser negociada porque foi <?= $proposalStatus === 'accepted' ? 'aceita' : 'finalizada' ?>.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i data-lucide="messages-square" class="w-5 h-5 text-purple-600"></i>
                            Chat com a empresa
                        </h2>
                        <div class="flex-1 max-h-64 overflow-y-auto space-y-2 mb-3 bg-gray-50 rounded-lg border border-gray-100 p-3">
                            <?php if (empty($messages)): ?>
                                <p class="text-xs text-gray-400">Nenhuma mensagem ainda. Envie uma mensagem para iniciar a conversa com a empresa.</p>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <?php $isMe = isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$msg['sender_id']; ?>
                                    <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                                        <div class="max-w-[80%] rounded-lg px-3 py-2 text-xs <?= $isMe ? 'bg-purple-600 text-white' : 'bg-white text-gray-800 border border-gray-200' ?>">
                                            <p class="font-semibold mb-1">
                                                <?= htmlspecialchars($msg['sender_name'] ?? ($isMe ? 'Você' : 'Empresa')) ?>
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
                        <form method="POST" action="<?= $this->url("professional/projects/{$project['id']}/proposals/{$proposal['id']}/message") ?>" class="flex gap-2">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                            <input type="text" name="message" required
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus-border-transparent"
                                   placeholder="Escreva uma mensagem para a empresa...">
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-purple-600 text-white text-xs hover:bg-purple-700 transition">
                                <i data-lucide="send" class="w-3 h-3 mr-1"></i>
                                Enviar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Informações</h3>
            <div class="space-y-4">
                <?php if ($project['budget_min'] || $project['budget_max']): ?>
                <div>
                    <p class="text-sm text-gray-500">Orçamento</p>
                    <p class="font-semibold text-gray-800">
                        R$ <?= number_format($project['budget_min'] ?? 0, 0) ?> - <?= number_format($project['budget_max'] ?? 0, 0) ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if ($project['deadline']): ?>
                <div>
                    <p class="text-sm text-gray-500">Prazo</p>
                    <p class="font-semibold text-gray-800"><?= date('d/m/Y', strtotime($project['deadline'])) ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <p class="text-sm text-gray-500">Categoria</p>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars(ucfirst($project['category'] ?? 'Geral')) ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Propostas</p>
                    <p class="font-semibold text-gray-800"><?= $project['proposals_count'] ?></p>
                </div>
            </div>
        </div>

        <?php if (!$hasProposal && ($project['status'] ?? 'open') === 'open'): ?>
        <form method="POST" action="<?= $this->url("professional/projects/{$project['id']}/accept") ?>" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            <p class="text-sm text-gray-700">Deseja aceitar este projeto rapidamente, sem enviar uma proposta detalhada?</p>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Aceitar projeto sem proposta
            </button>
            <p class="text-xs text-gray-500">
                A empresa será notificada e poderá gerar um contrato com base nas informações do projeto.
            </p>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('proposalForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= $this->url("professional/projects/{$project['id']}/proposal") ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Erro ao enviar proposta');
        }
    } catch (error) {
        alert('Erro ao processar requisição');
    }
});
</script>
