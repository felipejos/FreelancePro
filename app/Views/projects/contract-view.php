<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Contrato #<?= (int)$contract['id'] ?></h1>
            <p class="text-gray-600 text-sm mt-1">
                Projeto: <?= htmlspecialchars($contract['project_title'] ?? '') ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
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
            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $class ?>">
                <?= $label ?>
            </span>
            <a href="<?= $this->url('contracts') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Dados principais -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Valor do contrato</p>
            <p class="text-2xl font-bold text-gray-800">R$ <?= number_format((float)$contract['contract_value'], 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Taxa da plataforma</p>
            <p class="text-lg font-semibold text-gray-800">R$ <?= number_format((float)$contract['platform_fee'], 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Valor para o freelancer</p>
            <p class="text-lg font-semibold text-gray-800">R$ <?= number_format((float)$contract['professional_amount'], 2, ',', '.') ?></p>
        </div>
    </div>

    <!-- Participantes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i data-lucide="building-2" class="w-5 h-5 text-gray-600"></i>
                Empresa
            </h2>
            <p class="font-medium text-gray-800"><?= htmlspecialchars($contract['company_name'] ?? '') ?></p>
            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($contract['company_email'] ?? '') ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-gray-600"></i>
                Freelancer
            </h2>
            <p class="font-medium text-gray-800"><?= htmlspecialchars($contract['professional_name'] ?? '') ?></p>
            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($contract['professional_email'] ?? '') ?></p>
        </div>
    </div>

    <!-- Detalhes do projeto -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-lucide="briefcase" class="w-5 h-5 text-gray-600"></i>
            Detalhes do projeto
        </h2>
        <p class="text-sm text-gray-700 whitespace-pre-line">
            <?= nl2br(htmlspecialchars($contract['project_description'] ?? '')) ?>
        </p>
    </div>

    <!-- Ações -->
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-gray-600"></i>
                Ações do contrato
            </h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php if (($contract['status'] ?? 'active') === 'active'): ?>
                <button type="button"
                        onclick="completeContract(<?= (int)$contract['id'] ?>)"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Marcar como concluído</span>
                </button>
            <?php endif; ?>
        </div>

        <?php if (!empty($myReview)): ?>
        <div class="mt-6 border-t pt-4">
            <h3 class="text-md font-semibold text-gray-800 mb-2">Sua avaliação</h3>
            <div class="flex items-center gap-2 mb-2">
                <?php $rating = (int)($myReview['rating'] ?? 0); ?>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i data-lucide="star" class="w-4 h-4 <?= $i <= $rating ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                <?php endfor; ?>
            </div>
            <?php if (!empty($myReview['comment'])): ?>
            <p class="text-sm text-gray-700 mb-1">"<?= nl2br(htmlspecialchars($myReview['comment'])) ?>"</p>
            <?php endif; ?>
            <p class="text-xs text-gray-400">
                <?= !empty($myReview['created_at']) ? 'Avaliado em ' . date('d/m/Y H:i', strtotime($myReview['created_at'])) : '' ?>
            </p>
        </div>
        <?php elseif (!empty($canReview)): ?>
        <div class="mt-6 border-t pt-4">
            <h3 class="text-md font-semibold text-gray-800 mb-2">Avaliar freelancer</h3>
            <p class="text-sm text-gray-500 mb-3">Avaliação visível para o profissional e para futuros projetos.</p>
            <form onsubmit="return submitReview(event, <?= (int)$contract['id'] ?>)">
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                        <select name="rating" id="review-rating" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="5">5 - Excelente</option>
                            <option value="4">4 - Muito bom</option>
                            <option value="3">3 - Bom</option>
                            <option value="2">2 - Regular</option>
                            <option value="1">1 - Ruim</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comentário (opcional)</label>
                    <textarea name="comment" id="review-comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Como foi a experiência de trabalho?"></textarea>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition">
                    <i data-lucide="star" class="w-4 h-4"></i>
                    <span>Enviar avaliação</span>
                </button>
                <p id="review-message" class="mt-2 text-xs text-gray-500"></p>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
async function completeContract(contractId) {
    if (!confirm('Deseja marcar este contrato como concluído?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('_token', '<?= htmlspecialchars($csrf ?? '') ?>');

        const response = await fetch('<?= $this->url('contracts') ?>/' + contractId + '/complete', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message || 'Contrato finalizado!');
            window.location.reload();
        } else {
            alert(data.error || 'Erro ao finalizar contrato.');
        }
    } catch (e) {
        alert('Erro ao finalizar contrato.');
    }
}

async function submitReview(event, contractId) {
    event.preventDefault();

    const ratingEl = document.getElementById('review-rating');
    const commentEl = document.getElementById('review-comment');
    const messageEl = document.getElementById('review-message');

    const formData = new FormData();
    formData.append('_token', '<?= htmlspecialchars($csrf ?? '') ?>');
    formData.append('rating', ratingEl.value);
    formData.append('comment', commentEl.value);

    messageEl.textContent = 'Enviando avaliação...';
    messageEl.className = 'mt-2 text-xs text-gray-500';

    try {
        const response = await fetch('<?= $this->url('contracts') ?>/' + contractId + '/review', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Recarrega a página para exibir apenas as estrelas e o comentário
            // (a view já esconde o formulário quando existe avaliação)
            window.location.reload();
        } else {
            messageEl.textContent = data.error || 'Erro ao enviar avaliação.';
            messageEl.className = 'mt-2 text-xs text-red-600';
        }
    } catch (e) {
        messageEl.textContent = 'Erro ao enviar avaliação.';
        messageEl.className = 'mt-2 text-xs text-red-600';
    }

    return false;
}
</script>
