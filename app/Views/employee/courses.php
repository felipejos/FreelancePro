<!-- Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Meus Cursos</h1>
    <p class="text-gray-600">Acompanhe os cursos em que você está matriculado</p>
</div>

<?php if (empty($enrollments)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="graduation-cap" class="w-8 h-8 text-emerald-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum curso disponível</h3>
    <p class="text-gray-600">Quando a empresa matricular você em cursos, eles aparecerão aqui.</p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($enrollments as $enrollment): ?>
    <a href="<?= $this->url('employee/courses/' . $enrollment['course_id']) ?>" class="block bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-emerald-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($enrollment['course_title'] ?? 'Curso') ?>
                    </p>
                    <p class="text-sm text-gray-500 line-clamp-2">
                        <?= htmlspecialchars($enrollment['course_description'] ?? '') ?>
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        <?= (int)($enrollment['total_modules'] ?? 0) ?> módulos 
                        • <?= (int)($enrollment['total_lessons'] ?? 0) ?> aulas
                    </p>
                </div>
            </div>

            <div class="text-right min-w-[120px]">
                <?php $status = $enrollment['status'] ?? 'pending'; ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    <?= $status === 'completed' ? 'bg-green-100 text-green-700' : ($status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>">
                    <?php if ($status === 'completed'): ?>
                        Concluído
                    <?php elseif ($status === 'in_progress'): ?>
                        Em andamento
                    <?php else: ?>
                        Não iniciado
                    <?php endif; ?>
                </span>

                <?php $progress = isset($enrollment['progress_percentage']) ? (float)$enrollment['progress_percentage'] : 0; ?>
                <div class="mt-2">
                    <p class="text-xs text-gray-500 mb-1">Progresso</p>
                    <div class="w-32 bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: <?= max(0, min(100, $progress)) ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1 font-medium">
                        <?= number_format($progress, 0) ?>%
                    </p>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
