<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
        <div>
            <a href="<?= $this->url('employee/courses') ?>" class="text-emerald-600 hover:underline text-sm mb-2 inline-block">← Voltar aos Meus Cursos</a>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($course['title'] ?? '') ?></h1>
            <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($course['description'] ?? '') ?></p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">Seu progresso</p>
            <p class="text-2xl font-bold text-emerald-600"><?= (int)($enrollment['progress'] ?? 0) ?>%</p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Progresso do Curso</span>
            <span class="text-sm text-gray-500">
                <?= (int)($course['total_modules'] ?? 0) ?> módulos • <?= (int)($course['total_lessons'] ?? 0) ?> aulas
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-emerald-600 h-3 rounded-full transition-all" style="width: <?= (int)($enrollment['progress'] ?? 0) ?>%"></div>
        </div>
    </div>

    <!-- Modules List -->
    <div class="space-y-4">
        <?php if (empty($course['modules'])): ?>
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="book-open" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Nenhum módulo disponível</h2>
                <p class="text-gray-600">Este curso ainda não possui conteúdo publicado.</p>
            </div>
        <?php else: ?>
            <?php foreach ($course['modules'] as $moduleIndex => $module): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Module Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-sm font-bold">
                                <?= $moduleIndex + 1 ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($module['title'] ?? "Módulo " . ($moduleIndex + 1)) ?></h3>
                                <?php if (!empty($module['description'])): ?>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($module['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">
                            <?= count($module['lessons'] ?? []) ?> aula(s)
                        </span>
                    </div>
                </div>

                <!-- Lessons List -->
                <div class="divide-y divide-gray-100">
                    <?php if (empty($module['lessons'])): ?>
                        <div class="px-6 py-4 text-sm text-gray-500">
                            Nenhuma aula neste módulo.
                        </div>
                    <?php else: ?>
                        <?php foreach ($module['lessons'] as $lessonIndex => $lesson): ?>
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-xs">
                                    <?= $lessonIndex + 1 ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">
                                        <?= htmlspecialchars($lesson['title'] ?? "Aula " . ($lessonIndex + 1)) ?>
                                    </p>
                                    <?php if (!empty($lesson['duration_minutes'])): ?>
                                        <p class="text-xs text-gray-500">
                                            <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                            <?= (int)$lesson['duration_minutes'] ?> min
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= $this->url("employee/lessons/{$lesson['id']}") ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700 transition">
                                <i data-lucide="play" class="w-3 h-3"></i>
                                Assistir
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Module Questions (if any) -->
                <?php if (!empty($module['questions'])): ?>
                <div class="bg-yellow-50 px-6 py-3 border-t border-yellow-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="help-circle" class="w-4 h-4 text-yellow-600"></i>
                        <span class="text-sm text-yellow-800">
                            <?= count($module['questions']) ?> questão(ões) de avaliação
                        </span>
                    </div>
                    <a href="<?= $this->url("employee/courses/{$course['id']}/modules/{$module['id']}/quiz") ?>"
                       class="text-xs text-yellow-700 hover:underline font-medium">
                        Fazer Avaliação
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
