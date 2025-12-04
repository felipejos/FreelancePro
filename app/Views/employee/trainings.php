<!-- Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Meus Treinamentos</h1>
    <p class="text-gray-600">Realize os treinamentos atribuídos pela sua empresa</p>
</div>

<?php if (empty($assignments)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="book-open" class="w-8 h-8 text-emerald-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum treinamento atribuído</h3>
    <p class="text-gray-600">Quando a empresa atribuir treinamentos, eles aparecerão aqui.</p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($assignments as $assignment): ?>
    <a href="<?= $this->url("employee/trainings/{$assignment['id']}") ?>" class="block bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center
                    <?= $assignment['status'] === 'completed' ? ($assignment['passed'] ? 'bg-green-100' : 'bg-red-100') : 'bg-orange-100' ?>">
                    <i data-lucide="<?= $assignment['status'] === 'completed' ? ($assignment['passed'] ? 'check' : 'x') : 'book-open' ?>" 
                       class="w-6 h-6 <?= $assignment['status'] === 'completed' ? ($assignment['passed'] ? 'text-green-600' : 'text-red-600') : 'text-orange-600' ?>"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($assignment['playbook_title']) ?></p>
                    <p class="text-sm text-gray-500">
                        <?php if ($assignment['due_date']): ?>
                        Prazo: <?= date('d/m/Y', strtotime($assignment['due_date'])) ?>
                        <?php else: ?>
                        Sem prazo definido
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div class="text-right">
                <?php if ($assignment['status'] === 'completed'): ?>
                <p class="text-lg font-bold <?= $assignment['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                    <?= number_format($assignment['score'], 0) ?>%
                </p>
                <p class="text-sm <?= $assignment['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $assignment['passed'] ? 'Aprovado' : 'Reprovado' ?>
                </p>
                <?php else: ?>
                <span class="px-3 py-1 text-sm rounded-full <?= $assignment['status'] === 'pending' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700' ?>">
                    <?= $assignment['status'] === 'pending' ? 'Não iniciado' : 'Em andamento' ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
