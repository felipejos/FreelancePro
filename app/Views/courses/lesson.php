<?php
$videoUrl = $lesson['video_url'] ?? '';
$youtubeId = null;
$currentUserType = $_SESSION['user']['user_type'] ?? null;

// Modo atual pode vir explicitamente via query (?mode=ai|url|upload|none)
$allowedModes = ['ai', 'url', 'upload', 'none'];
$requestedMode = $_GET['mode'] ?? null;
$currentVideoMode = in_array($requestedMode, $allowedModes, true) ? $requestedMode : null;

// Se não veio pela query, inferir a partir do tipo de URL existente
if ($currentVideoMode === null) {
    $currentVideoMode = $videoUrl ? 'url' : 'none';
}

if ($videoUrl) {
    if (strpos($videoUrl, 'youtube.com') !== false) {
        $query = parse_url($videoUrl, PHP_URL_QUERY) ?? '';
        parse_str($query, $qs);
        $youtubeId = $qs['v'] ?? null;
    } elseif (strpos($videoUrl, 'youtu.be') !== false) {
        $path = parse_url($videoUrl, PHP_URL_PATH) ?? '';
        $youtubeId = ltrim($path, '/');
    }
}
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a href="<?= $this->url("courses/{$course['id']}") ?>" class="text-purple-600 hover:underline text-sm inline-flex items-center gap-1 mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar ao curso
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($lesson['title']) ?></h1>
            <p class="text-sm text-gray-500">
                Parte do curso: <?= htmlspecialchars($course['title']) ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Conteúdo principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Vídeo -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="video" class="w-4 h-4 text-purple-600"></i>
                    Vídeo da aula
                </h2>
                <?php if ($videoUrl && $youtubeId): ?>
                    <div class="aspect-video w-full rounded-lg overflow-hidden bg-black">
                        <iframe
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>"
                            class="w-full h-full border-0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                <?php elseif ($videoUrl): ?>
                    <video controls class="w-full rounded-lg bg-black" src="<?= htmlspecialchars(str_starts_with($videoUrl, 'http') ? $videoUrl : $this->url($videoUrl)) ?>">
                        Seu navegador não suporta reprodução de vídeo.
                    </video>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Nenhum vídeo configurado para esta aula ainda.</p>
                <?php endif; ?>
            </div>

            <!-- Conteúdo em texto (visualização + edição opcional) -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                    Conteúdo da aula
                </h2>

                <!-- Visualização formatada (sem mostrar tags) -->
                <div class="prose max-w-none mb-4">
                    <?= $lesson['content_html'] ?>
                </div>

                <!-- Edição avançada em HTML (oculta por padrão) -->
                <button
                    type="button"
                    id="toggle-lesson-editor"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 transition mb-3"
                >
                    <i data-lucide="edit-3" class="w-3 h-3"></i>
                    <span>Editar conteúdo em HTML</span>
                </button>

                <div id="lesson-editor-section" class="space-y-4" style="display: none;">
                    <form method="POST" action="<?= $this->url("courses/lessons/{$lesson['id']}") ?>" class="space-y-4">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Título da aula</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Conteúdo da aula (HTML permitido)</label>
                            <textarea name="content_html" rows="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:border-transparent"><?php echo htmlspecialchars($lesson['content_html'] ?? ''); ?></textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Você pode usar tags HTML como &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt; para estruturar melhor o conteúdo.</p>
                        </div>

                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Salvar alterações da aula</span>
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($nextLesson)): ?>
                <div class="mt-2 flex justify-end">
                    <a href="<?= $this->url("courses/lessons/{$nextLesson['id']}") ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition">
                        <span>Próxima aula: <?= htmlspecialchars($nextLesson['title']) ?></span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Configuração de vídeo (empresa) -->
        <?php if ($currentUserType === 'company'): ?>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4 text-purple-600"></i>
                    Configuração de vídeo da aula
                </h2>
                <p class="text-xs text-gray-500 mb-3">Escolha como o vídeo desta aula será definido.</p>

                <form method="POST" action="<?= $this->url("courses/lessons/{$lesson['id']}/video") ?>" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

                    <div class="space-y-2 text-sm text-gray-700">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="video_mode" value="ai" class="mt-1 video-mode-radio" <?= $currentVideoMode === 'ai' ? 'checked' : '' ?>>
                            <span>
                                <span class="font-medium">Sugestão da IA</span><br>
                                Pedir para a IA indicar automaticamente um vídeo público do YouTube relacionado ao conteúdo desta aula.
                            </span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="video_mode" value="url" class="mt-1 video-mode-radio" <?= $currentVideoMode === 'url' ? 'checked' : '' ?>>
                            <span>
                                <span class="font-medium">Link manual (YouTube ou outro)</span><br>
                                Informe um link de vídeo do YouTube ou outra URL de player.
                            </span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="video_mode" value="upload" class="mt-1 video-mode-radio" <?= $currentVideoMode === 'upload' ? 'checked' : '' ?>>
                            <span>
                                <span class="font-medium">Upload de vídeo próprio</span><br>
                                Envie um arquivo de vídeo do seu computador (MP4, WEBM, OGG, MOV).
                            </span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="video_mode" value="none" class="mt-1 video-mode-radio" <?= $videoUrl ? '' : 'checked' ?>>
                            <span>
                                <span class="font-medium">Sem vídeo</span><br>
                                Remover qualquer vídeo associado a esta aula.
                            </span>
                        </label>
                    </div>

                    <div class="space-y-3 mt-3">
                        <div id="video-url-group">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Link do vídeo (YouTube ou outro)</label>
                            <input type="text" name="video_url" value="<?= htmlspecialchars($currentVideoMode === 'url' ? $videoUrl : '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="https://www.youtube.com/watch?v=...">
                        </div>

                        <div id="video-upload-group" class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Upload de vídeo</label>
                            <input type="file" name="video_file" accept="video/*" class="w-full text-xs text-gray-600">
                            <p class="text-[11px] text-gray-400">Tamanhos muito grandes podem demorar para enviar.</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition mt-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Salvar configuração de vídeo</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('.video-mode-radio');
    const urlGroup = document.getElementById('video-url-group');
    const uploadGroup = document.getElementById('video-upload-group');

    function updateVisibility() {
        let mode = 'none';
        radios.forEach(r => { if (r.checked) mode = r.value; });
        urlGroup.style.display = (mode === 'url') ? 'block' : 'none';
        uploadGroup.style.display = (mode === 'upload') ? 'block' : 'none';
    }

    radios.forEach(r => r.addEventListener('change', updateVisibility));
    updateVisibility();
});
</script>
