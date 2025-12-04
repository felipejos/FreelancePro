<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Cursos</h1>
        <p class="text-gray-600">Crie cursos completos com IA</p>
    </div>
    <a href="<?= $this->url('courses/create') ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
        <i data-lucide="plus" class="w-5 h-5"></i>
        <span>Criar Curso</span>
    </a>
</div>

<!-- Courses Grid -->
<?php if (empty($courses)): ?>
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="graduation-cap" class="w-8 h-8 text-purple-600"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum curso ainda</h3>
    <p class="text-gray-600 mb-4">Crie seu primeiro curso usando IA para capacitar sua equipe.</p>
    <a href="<?= $this->url('courses/create') ?>" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
        <i data-lucide="plus" class="w-5 h-5"></i>
        Criar Curso
    </a>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($courses as $course): ?>
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-purple-600"></i>
                </div>
                <span class="px-2 py-1 text-xs rounded-full <?= $course['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                    <?= $course['status'] === 'published' ? 'Publicado' : 'Rascunho' ?>
                </span>
            </div>
            
            <h3 class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($course['title']) ?></h3>
            <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?= htmlspecialchars($course['description'] ?? 'Sem descrição') ?></p>
            
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <?= $course['total_modules'] ?> módulos
                </span>
                <span class="flex items-center gap-1">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    <?= $course['total_lessons'] ?> aulas
                </span>
            </div>
        </div>
        
        <div class="px-6 py-4 border-t bg-gray-50 rounded-b-xl flex items-center justify-between">
            <a href="<?= $this->url("courses/{$course['id']}") ?>" class="text-purple-600 hover:underline text-sm">Ver detalhes</a>
            <div class="flex items-center gap-3">
                <a href="<?= $this->url("courses/{$course['id']}/manage") ?>" class="text-gray-600 hover:underline text-sm">Gerenciar</a>
                <button
                    type="button"
                    onclick="deleteCourse(<?= (int)$course['id'] ?>)"
                    class="text-gray-400 hover:text-red-600 transition"
                    title="Excluir curso"
                >
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
async function deleteCourse(courseId) {
    if (!confirm('Tem certeza que deseja excluir este curso? Esta ação não pode ser desfeita.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('_method', 'DELETE');

        const response = await fetch('<?= $this->url('courses') ?>/' + courseId, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Erro ao excluir curso.');
        }
    } catch (e) {
        alert('Erro ao excluir curso.');
    }
}
</script>
