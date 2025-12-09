<div class="space-y-8">
    <div class="mb-6">
        <a href="<?= $this->url('courses') ?>" class="text-emerald-600 hover:underline text-sm mb-2 inline-block">← Voltar aos Cursos</a>
        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($course['title'] ?? 'Curso') ?></h1>
        <?php if (!empty($course['description'])): ?>
            <p class="text-gray-600 mt-1 max-w-3xl"><?= htmlspecialchars($course['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-purple-600"></i>
                    Estrutura do Curso
                </h2>

                <?php if (empty($course['modules'])): ?>
                    <p class="text-gray-500 text-sm">Nenhum módulo encontrado para este curso.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($course['modules'] as $module): ?>
                            <div class="border border-gray-200 rounded-lg">
                                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            Módulo <?= (int)$module['order_number'] ?>: <?= htmlspecialchars($module['title']) ?>
                                        </p>
                                        <?php if (!empty($module['description'])): ?>
                                            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($module['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        <?= count($module['lessons'] ?? []) ?> aulas
                                    </span>
                                </div>

                                <?php if (!empty($module['lessons'])): ?>
                                    <div class="px-4 py-3 space-y-2">
                                        <?php foreach ($module['lessons'] as $lesson): ?>
                                            <a href="<?= $this->url('courses/lessons/' . $lesson['id']) ?>" class="block">
                                                <div class="flex items-start gap-3 hover:bg-purple-50 rounded-lg px-2 py-2 transition">
                                                    <div class="mt-1">
                                                        <i data-lucide="play-circle" class="w-4 h-4 text-purple-500"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-800">
                                                            Aula <?= (int)$lesson['order_number'] ?>: <?= htmlspecialchars($lesson['title']) ?>
                                                        </p>
                                                        <?php if (!empty($lesson['content_html'])): ?>
                                                            <div class="prose prose-sm max-w-none text-gray-600 mt-1 line-clamp-3">
                                                                <?= $lesson['content_html'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-emerald-600"></i>
                    Visualização
                </h2>
                <p class="text-sm text-gray-600">Use os links das aulas para pré-visualizar o conteúdo com o layout do aluno.</p>
            </div>
        </div>
    </div>
</div>
