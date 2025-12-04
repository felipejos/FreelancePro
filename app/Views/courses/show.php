<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($course['title']) ?></h1>
            <p class="text-gray-600 text-sm max-w-2xl mt-1">
                <?= htmlspecialchars($course['description'] ?? '') ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $course['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= $course['status'] === 'published' ? 'Publicado' : 'Rascunho' ?>
            </span>
            <?php if ($course['status'] !== 'published'): ?>
                <button type="button"
                        onclick="publishCourse(<?= (int)$course['id'] ?>)"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Publicar curso</span>
                </button>
            <?php endif; ?>
            <form method="POST" action="<?= $this->url("courses/{$course['id']}/regenerate") ?>"
                  onsubmit="return confirm('Isso irá apagar e recriar todos os módulos e aulas deste curso com base no título e descrição. Deseja continuar?');">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Regerar conteúdo com IA</span>
                </button>
            </form>
            <a href="<?= $this->url("courses/{$course['id']}/manage") ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-black transition">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Gerenciar Curso
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Módulos</p>
            <p class="text-3xl font-bold text-gray-800"><?= (int)($course['total_modules'] ?? count($course['modules'] ?? [])) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Aulas</p>
            <p class="text-3xl font-bold text-gray-800"><?= (int)($course['total_lessons'] ?? 0) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Matrículas</p>
            <p class="text-3xl font-bold text-gray-800"><?= count($enrollments ?? []) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Modules and Lessons -->
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
                                            <a href="<?= $this->url("courses/lessons/{$lesson['id']}") ?>" class="block">
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

            <!-- Questions per module (if any) -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                    Questionários por módulo
                </h2>

                <?php
                $hasQuestions = false;
                foreach ($course['modules'] as $m) {
                    if (!empty($m['questions'])) { $hasQuestions = true; break; }
                }
                ?>

                <?php if (!$hasQuestions): ?>
                    <p class="text-gray-500 text-sm">Nenhuma questão cadastrada para este curso.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($course['modules'] as $module): ?>
                            <?php if (empty($module['questions'])) continue; ?>
                            <div class="border border-gray-200 rounded-lg">
                                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-800">
                                        Questionário - <?= htmlspecialchars($module['title']) ?>
                                    </p>
                                    <span class="text-xs text-gray-400"><?= count($module['questions']) ?> questões</span>
                                </div>
                                <div class="px-4 py-3 space-y-2">
                                    <?php foreach ($module['questions'] as $qIndex => $question): ?>
                                        <div class="text-sm text-gray-700">
                                            <span class="font-medium"><?= $qIndex + 1 ?>.</span>
                                            <?= htmlspecialchars($question['question_text']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Enrollments -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-emerald-600"></i>
                    Matrículas
                </h2>

                <?php if (empty($enrollments)): ?>
                    <p class="text-gray-500 text-sm mb-4">Nenhum funcionário matriculado neste curso ainda.</p>
                    <a href="<?= $this->url("courses/{$course['id']}/manage") ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Matricular Funcionários
                    </a>
                <?php else: ?>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="border border-gray-200 rounded-lg px-4 py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">
                                        <?= htmlspecialchars($enrollment['employee_name'] ?? ('ID #' . $enrollment['employee_id'])) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= htmlspecialchars($enrollment['employee_email'] ?? '') ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 mb-1">Progresso</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        <?= number_format((float)($enrollment['progress_percentage'] ?? 0), 1) ?>%
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= $this->url("courses/{$course['id']}/manage") ?>" class="inline-flex items-center gap-2 mt-4 text-sm text-emerald-700 hover:underline">
                        Gerenciar matrículas
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
async function publishCourse(courseId) {
    if (!confirm('Deseja publicar este curso? Funcionários matriculados poderão acessá-lo.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('_token', '<?= htmlspecialchars($csrf ?? '') ?>');

        const response = await fetch('<?= $this->url('courses') ?>/' + courseId + '/publish', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message || 'Curso publicado com sucesso!');
            window.location.reload();
        } else {
            alert(data.error || 'Erro ao publicar curso.');
        }
    } catch (e) {
        alert('Erro ao publicar curso.');
    }
}
</script>
