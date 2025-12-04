<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
        <div>
            <a href="<?= $this->url('employee/courses') ?>" class="text-emerald-600 hover:underline text-sm mb-2 inline-block">← Voltar aos Meus Cursos</a>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($lesson['title'] ?? 'Aula') ?></h1>
        </div>
    </div>

    <!-- Video / Content Area -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (!empty($lesson['video_url'])): ?>
        <div class="aspect-video bg-gray-900">
            <?php
            $videoUrl = $lesson['video_url'];
            // Detectar se é YouTube ou Vimeo e fazer embed
            if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                // Extrair ID do YouTube
                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches);
                $youtubeId = $matches[1] ?? '';
                if ($youtubeId) {
                    echo '<iframe class="w-full h-full" src="https://www.youtube.com/embed/' . htmlspecialchars($youtubeId) . '" frameborder="0" allowfullscreen></iframe>';
                }
            } elseif (strpos($videoUrl, 'vimeo.com') !== false) {
                preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                $vimeoId = $matches[1] ?? '';
                if ($vimeoId) {
                    echo '<iframe class="w-full h-full" src="https://player.vimeo.com/video/' . htmlspecialchars($vimeoId) . '" frameborder="0" allowfullscreen></iframe>';
                }
            } else {
                // Vídeo direto
                echo '<video class="w-full h-full" controls><source src="' . htmlspecialchars($videoUrl) . '" type="video/mp4">Seu navegador não suporta vídeo.</video>';
            }
            ?>
        </div>
        <?php else: ?>
        <div class="aspect-video bg-gray-100 flex items-center justify-center">
            <div class="text-center text-gray-500">
                <i data-lucide="video-off" class="w-12 h-12 mx-auto mb-2"></i>
                <p>Nenhum vídeo disponível para esta aula.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lesson Content -->
        <div class="p-6">
            <?php if (!empty($lesson['content'])): ?>
            <div class="prose max-w-none text-gray-700">
                <?= nl2br(htmlspecialchars($lesson['content'])) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($lesson['duration_minutes'])): ?>
            <p class="text-sm text-gray-500 mt-4">
                <i data-lucide="clock" class="w-4 h-4 inline"></i>
                Duração estimada: <?= (int)$lesson['duration_minutes'] ?> minutos
            </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between bg-white rounded-xl shadow-sm p-4">
        <button onclick="completeLesson(<?= (int)$lesson['id'] ?>)" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Marcar como Concluída
        </button>

        <?php if (!empty($nextLesson)): ?>
        <a href="<?= $this->url("employee/lessons/{$nextLesson['id']}") ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
            Próxima Aula
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

<script>
async function completeLesson(lessonId) {
    try {
        const response = await fetch(`<?= $this->url('employee/lessons/') ?>${lessonId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message || 'Aula concluída!');
            location.reload();
        } else {
            alert(data.error || 'Erro ao marcar aula como concluída.');
        }
    } catch (error) {
        alert('Erro ao processar requisição.');
    }
}
</script>
