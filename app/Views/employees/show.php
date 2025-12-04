<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xl">
                <?= strtoupper(substr($employee['name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($employee['name']) ?></h1>
                <p class="text-gray-600"><?= htmlspecialchars($employee['email']) ?></p>
            </div>
        </div>
        <span class="px-3 py-1 text-sm rounded-full <?= $employee['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $employee['status'] === 'active' ? 'Ativo' : 'Inativo' ?>
        </span>
    </div>

    <?php if (!empty($courses ?? [])): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Matricular em curso</h2>
                <p class="text-sm text-gray-500">Selecione um curso publicado para matricular este funcionário.</p>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso</label>
                <select id="employee-course-select" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Selecione um curso</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int)$course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:w-auto w-full">
                <button type="button"
                        onclick="enrollEmployeeInCourse(<?= (int)$employee['id'] ?>)"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Matricular</span>
                </button>
            </div>
        </div>
        <p id="employee-course-message" class="text-xs text-gray-500 mt-2"></p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-dashed border-gray-200">
        <p class="text-sm text-gray-500">Nenhum curso publicado disponível para matrícula. Publique um curso primeiro para matricular este funcionário.</p>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Data de cadastro</p>
            <p class="text-lg font-semibold text-gray-800"><?= date('d/m/Y', strtotime($employee['created_at'])) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Treinamentos atribuídos</p>
            <p class="text-lg font-semibold text-gray-800"><?= count($assignments) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Cursos matriculados</p>
            <p class="text-lg font-semibold text-gray-800"><?= count($enrollments) ?></p>
        </div>
    </div>

    <?php if (!empty($assignments)): ?>
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Treinamentos</h2>
            <p class="text-sm text-gray-500">Treinamentos do colaborador</p>
        </div>
        <div class="divide-y">
            <?php foreach ($assignments as $assignment): ?>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($assignment['playbook_title'] ?? '') ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <?php if (!empty($assignment['due_date'])): ?>
                            Prazo: <?= date('d/m/Y', strtotime($assignment['due_date'])) ?>
                        <?php else: ?>
                            Sem prazo definido
                        <?php endif; ?>
                    </p>
                </div>
                <div class="text-right">
                    <?php if ($assignment['status'] === 'completed'): ?>
                        <p class="text-sm font-semibold <?= $assignment['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $assignment['passed'] ? 'Aprovado' : 'Reprovado' ?>
                        </p>
                        <p class="text-xs text-gray-500">Nota: <?= number_format($assignment['score'] ?? 0, 0) ?>%</p>
                    <?php else: ?>
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium <?= $assignment['status'] === 'pending' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $assignment['status'] === 'pending' ? 'Não iniciado' : 'Em andamento' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($enrollments)): ?>
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Cursos</h2>
            <p class="text-sm text-gray-500">Cursos em que o colaborador está matriculado</p>
        </div>
        <div class="divide-y">
            <?php foreach ($enrollments as $enrollment): ?>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($enrollment['course_title'] ?? '') ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <?= (int)($enrollment['total_modules'] ?? 0) ?> módulos 
                        • <?= (int)($enrollment['total_lessons'] ?? 0) ?> aulas
                    </p>
                </div>
                <div class="text-right">
                    <?php $status = $enrollment['status'] ?? 'pending'; ?>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
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
                        <div class="w-32 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-emerald-500" style="width: <?= max(0, min(100, $progress)) ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-1 font-medium">
                            <?= number_format($progress, 0) ?>%
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($assignments) && empty($enrollments)): ?>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <p class="text-gray-600">Nenhum treinamento ou curso atribuído para este funcionário ainda.</p>
    </div>
    <?php endif; ?>
    <script>
        async function enrollEmployeeInCourse(employeeId) {
            const select = document.getElementById('employee-course-select');
            const messageEl = document.getElementById('employee-course-message');
            const courseId = select ? select.value : '';

            if (!courseId) {
                messageEl.textContent = 'Selecione um curso para matricular o funcionário.';
                messageEl.className = 'text-xs text-red-600 mt-2';
                return;
            }

            messageEl.textContent = 'Matriculando funcionário no curso...';
            messageEl.className = 'text-xs text-gray-500 mt-2';

            try {
                const formData = new FormData();
                formData.append('_token', '<?= htmlspecialchars($csrf ?? '') ?>');
                formData.append('employee_ids[]', employeeId);

                const response = await fetch('<?= $this->url('courses') ?>/' + courseId + '/enroll', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageEl.textContent = data.message || 'Funcionário matriculado com sucesso!';
                    messageEl.className = 'text-xs text-emerald-600 mt-2';
                    window.location.reload();
                } else {
                    messageEl.textContent = data.error || 'Erro ao matricular funcionário no curso.';
                    messageEl.className = 'text-xs text-red-600 mt-2';
                }
            } catch (e) {
                messageEl.textContent = 'Erro ao matricular funcionário no curso.';
                messageEl.className = 'text-xs text-red-600 mt-2';
            }
        }
    </script>
</div>
