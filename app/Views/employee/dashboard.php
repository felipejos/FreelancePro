<!-- Welcome -->
<div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl p-6 mb-8 text-white">
    <h1 class="text-2xl font-bold">Bem-vindo, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>!</h1>
    <p class="text-emerald-100 mt-1">Acompanhe seus treinamentos e cursos</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Treinamentos Pendentes</p>
                <p class="text-3xl font-bold text-orange-600"><?= count($pendingTrainings) ?></p>
            </div>
            <i data-lucide="clock" class="w-8 h-8 text-orange-200"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Concluídos</p>
                <p class="text-3xl font-bold text-green-600"><?= count($completedTrainings) ?></p>
            </div>
            <i data-lucide="check-circle" class="w-8 h-8 text-green-200"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Cursos Matriculados</p>
                <p class="text-3xl font-bold text-blue-600"><?= count($enrollments) ?></p>
            </div>
            <i data-lucide="graduation-cap" class="w-8 h-8 text-blue-200"></i>
        </div>
    </div>
</div>

<!-- Pending Trainings -->
<?php if (!empty($pendingTrainings)): ?>
<div class="bg-white rounded-xl shadow-sm mb-8">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-gray-800">Treinamentos Pendentes</h2>
    </div>
    <div class="divide-y">
        <?php foreach ($pendingTrainings as $training): ?>
        <a href="<?= $this->url("employee/trainings/{$training['id']}") ?>" class="flex items-center justify-between p-6 hover:bg-gray-50 transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="book-open" class="w-6 h-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($training['playbook_title']) ?></p>
                    <p class="text-sm text-gray-500">
                        <?= $training['due_date'] ? 'Prazo: ' . date('d/m/Y', strtotime($training['due_date'])) : 'Sem prazo definido' ?>
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 text-sm rounded-full bg-orange-100 text-orange-700">
                <?= $training['status'] === 'pending' ? 'Não iniciado' : 'Em andamento' ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Enrollments -->
<?php if (!empty($enrollments)): ?>
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-gray-800">Meus Cursos</h2>
    </div>
    <div class="divide-y">
        <?php foreach ($enrollments as $enrollment): ?>
        <a href="<?= $this->url("employee/courses/{$enrollment['course_id']}") ?>" class="flex items-center justify-between p-6 hover:bg-gray-50 transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($enrollment['course_title']) ?></p>
                    <p class="text-sm text-gray-500"><?= $enrollment['total_lessons'] ?> aulas</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold text-blue-600"><?= number_format($enrollment['progress_percentage'], 0) ?>%</p>
                <div class="w-24 bg-gray-200 rounded-full h-2 mt-1">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $enrollment['progress_percentage'] ?>%"></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (empty($pendingTrainings) && empty($enrollments)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
    <p class="text-gray-600">Nenhum treinamento ou curso atribuído ainda.</p>
</div>
<?php endif; ?>
