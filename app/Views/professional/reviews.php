<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Minhas Avaliações</h1>
            <p class="text-gray-600 text-sm">Veja o que as empresas acharam do seu trabalho.</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2 flex items-center gap-2">
            <i data-lucide="star" class="w-5 h-5 text-yellow-500"></i>
            <div class="text-sm">
                <p class="text-gray-700 font-semibold">
                    Nota média: <span class="text-yellow-600"><?= number_format((float)($avgRating ?? 0), 1, ',', '.') ?></span>
                </p>
                <p class="text-xs text-gray-500"><?= count($reviews ?? []) ?> avaliação(ões)</p>
            </div>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="star-off" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Você ainda não recebeu avaliações</h2>
            <p class="text-gray-600">Assim que concluir contratos com as empresas, suas avaliações aparecerão aqui.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($reviews as $review): ?>
            <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            <?= htmlspecialchars($review['reviewer_name'] ?? 'Empresa') ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            Projeto: <?= htmlspecialchars($review['project_title'] ?? '') ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <?php $rating = (int)($review['rating'] ?? 0); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i data-lucide="star" class="w-4 h-4 <?= $i <= $rating ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if (!empty($review['comment'])): ?>
                <p class="text-sm text-gray-700">
                    "<?= nl2br(htmlspecialchars($review['comment'])) ?>"
                </p>
                <?php endif; ?>

                <p class="text-xs text-gray-400 text-right">
                    <?= !empty($review['created_at']) ? date('d/m/Y H:i', strtotime($review['created_at'])) : '' ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
