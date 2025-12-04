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
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="messages-square" class="w-5 h-5 text-purple-600"></i>
                    Chat com a empresa
                </h2>

                <div class="max-h-64 overflow-y-auto space-y-2 mb-3 bg-gray-50 rounded-lg border border-gray-100 p-3">
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
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Escreva uma mensagem para a empresa...">
                    <button type="submit" class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-purple-600 text-white text-xs hover:bg-purple-700 transition">
                        <i data-lucide="send" class="w-3 h-3 mr-1"></i>
                        Enviar
                    </button>
                </form>
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
