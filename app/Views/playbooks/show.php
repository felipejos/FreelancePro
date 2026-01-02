<!-- Header -->
<style>
 .playbook-content h2 { margin-top: 1.25rem; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 700; }
 .playbook-content h3 { margin-top: 1rem; margin-bottom: 0.5rem; font-size: 1.125rem; font-weight: 600; }
 .playbook-content p { margin: 0.5rem 0; line-height: 1.7; }
 .playbook-content ul { margin: 0.5rem 0 0.75rem 1.25rem; list-style: disc; }
 .playbook-content li { margin: 0.25rem 0; }
</style>
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="<?= $this->url('playbooks') ?>" class="text-blue-600 hover:underline text-sm mb-2 inline-block">← Voltar aos Playbooks</a>
        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($playbook['title']) ?></h1>
    </div>
    <div class="flex items-center gap-3">
        <?php if ($playbook['status'] === 'draft'): ?>
        <button onclick="publishPlaybook()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="check" class="w-5 h-5"></i>
            Publicar
        </button>
        <?php endif; ?>
        <a href="<?= $this->url("playbooks/{$playbook['id']}/assign") ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="users" class="w-5 h-5"></i>
            Atribuir
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-full text-sm <?= $playbook['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                        <?= $playbook['status'] === 'published' ? 'Publicado' : 'Rascunho' ?>
                    </span>
                    <span class="text-gray-500 text-sm">Criado em <?= date('d/m/Y', strtotime($playbook['created_at'])) ?></span>
                </div>
                <span class="text-gray-500 text-sm">Fonte: <?= ucfirst($playbook['source_type']) ?></span>
            </div>
        </div>
        
        <!-- Video Player -->
        <?php
        $videoMode = $playbook['video_mode'] ?? 'none';
        $videoUrl = $playbook['video_url'] ?? '';
        if ($videoUrl):
            $isYoutube = preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $ytMatch);
            $isVimeo = preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $vimeoMatch);
            $isLocalUpload = strpos($videoUrl, 'uploads/playbooks/videos/') === 0;
        ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="video" class="w-5 h-5"></i>
                Vídeo do Treinamento
            </h2>
            <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden">
                <?php if ($isYoutube): ?>
                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytMatch[1]) ?>?rel=0" 
                            class="w-full h-full" frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen></iframe>
                <?php elseif ($isVimeo): ?>
                    <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vimeoMatch[1]) ?>" 
                            class="w-full h-full" frameborder="0" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen></iframe>
                <?php elseif ($isLocalUpload): ?>
                    <video controls class="w-full h-full">
                        <source src="<?= $this->url($videoUrl) ?>" type="video/<?= pathinfo($videoUrl, PATHINFO_EXTENSION) ?>">
                        Seu navegador não suporta reprodução de vídeo.
                    </video>
                <?php else: ?>
                    <video controls class="w-full h-full">
                        <source src="<?= htmlspecialchars($videoUrl) ?>">
                        Seu navegador não suporta reprodução de vídeo.
                    </video>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Conteúdo do Treinamento</h2>
            <div class="playbook-content prose max-w-none">
                <?= $playbook['content_html'] ?>
            </div>
        </div>
        
        <!-- Questions -->
        <?php if (!empty($playbook['questions'])): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Questionário (<?= count($playbook['questions']) ?> questões)</h2>
            <div class="space-y-6">
                <?php foreach ($playbook['questions'] as $index => $question): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-800 mb-3"><?= $index + 1 ?>. <?= htmlspecialchars($question['question_text']) ?></p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="p-2 rounded <?= $question['correct_option'] === 'A' ? 'bg-green-100 text-green-700' : 'bg-white' ?>">
                            A) <?= htmlspecialchars($question['option_a']) ?>
                        </div>
                        <div class="p-2 rounded <?= $question['correct_option'] === 'B' ? 'bg-green-100 text-green-700' : 'bg-white' ?>">
                            B) <?= htmlspecialchars($question['option_b']) ?>
                        </div>
                        <div class="p-2 rounded <?= $question['correct_option'] === 'C' ? 'bg-green-100 text-green-700' : 'bg-white' ?>">
                            C) <?= htmlspecialchars($question['option_c']) ?>
                        </div>
                        <div class="p-2 rounded <?= $question['correct_option'] === 'D' ? 'bg-green-100 text-green-700' : 'bg-white' ?>">
                            D) <?= htmlspecialchars($question['option_d']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Estatísticas</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Atribuições</span>
                    <span class="font-semibold"><?= count($assignments) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Concluídos</span>
                    <span class="font-semibold"><?= count(array_filter($assignments, fn($a) => $a['status'] === 'completed')) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Questões</span>
                    <span class="font-semibold"><?= count($playbook['questions'] ?? []) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Assignments -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Funcionários Atribuídos</h3>
            <?php if (empty($assignments)): ?>
            <p class="text-gray-500 text-sm">Nenhum funcionário atribuído ainda.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($assignments, 0, 5) as $assignment): ?>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700"><?= htmlspecialchars($assignment['employee_name']) ?></span>
                    <span class="text-xs px-2 py-1 rounded-full 
                        <?= $assignment['status'] === 'completed' ? 'bg-green-100 text-green-700' : '' ?>
                        <?= $assignment['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700' : '' ?>
                        <?= $assignment['status'] === 'pending' ? 'bg-gray-100 text-gray-700' : '' ?>">
                        <?= $assignment['score'] ? number_format($assignment['score'], 0) . '%' : ucfirst($assignment['status']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Configuração de Vídeo -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Vídeo do Playbook</h3>
            <?php
            $videoMode = $playbook['video_mode'] ?? 'none';
            $videoUrl = $playbook['video_url'] ?? '';
            $videoOriginal = $playbook['video_original_name'] ?? '';
            ?>
            
            <?php if ($videoUrl): ?>
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">
                    <i data-lucide="video" class="w-4 h-4 inline-block mr-1"></i>
                    Vídeo configurado (<?= $videoMode === 'upload' ? 'Upload' : ($videoMode === 'ai' ? 'IA' : 'Link') ?>)
                </p>
                <?php if ($videoOriginal): ?>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($videoOriginal) ?></p>
                <?php else: ?>
                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($videoUrl) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form action="<?= $this->url("playbooks/{$playbook['id']}/video") ?>" method="POST" enctype="multipart/form-data" id="videoForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modo de Vídeo</label>
                    <select name="video_mode" id="videoModeSelect" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="none" <?= $videoMode === 'none' ? 'selected' : '' ?>>Sem vídeo</option>
                        <option value="url" <?= $videoMode === 'url' ? 'selected' : '' ?>>Link (YouTube/Vimeo/etc)</option>
                        <option value="upload" <?= $videoMode === 'upload' ? 'selected' : '' ?>>Upload de arquivo</option>
                        <option value="ai" <?= $videoMode === 'ai' ? 'selected' : '' ?>>Sugestão por IA</option>
                    </select>
                </div>

                <div id="videoUrlField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL do Vídeo</label>
                    <input type="url" name="video_url" value="<?= htmlspecialchars($videoUrl) ?>" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div id="videoUploadField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo de Vídeo</label>
                    <input type="file" name="video_file" accept=".mp4,.webm,.ogg,.mov,.m4v" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Formatos: MP4, WEBM, OGG, MOV, M4V</p>
                </div>

                <div id="videoAiField" class="mb-4 hidden">
                    <p class="text-sm text-gray-600">A IA irá sugerir um vídeo educacional do YouTube relacionado ao conteúdo deste playbook.</p>
                </div>

                <div class="mb-4 flex items-start gap-2">
                    <input type="checkbox" id="acceptTermsVideo" name="accept_terms" value="1" class="mt-1 text-blue-600 focus:ring-blue-500" required>
                    <label for="acceptTermsVideo" class="text-sm text-gray-700 leading-5">
                        Confirmo que li e aceito os termos de uso para vídeos de playbooks (versão 1.0).
                    </label>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Salvar Configuração de Vídeo
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Video mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const modeSelect = document.getElementById('videoModeSelect');
    const urlField = document.getElementById('videoUrlField');
    const uploadField = document.getElementById('videoUploadField');
    const aiField = document.getElementById('videoAiField');

    function toggleVideoFields() {
        const mode = modeSelect.value;
        urlField.classList.toggle('hidden', mode !== 'url');
        uploadField.classList.toggle('hidden', mode !== 'upload');
        aiField.classList.toggle('hidden', mode !== 'ai');
    }

    modeSelect.addEventListener('change', toggleVideoFields);
    toggleVideoFields();
});

async function publishPlaybook() {
    if (!confirm('Deseja publicar este playbook?')) return;
    
    try {
        const response = await fetch('<?= $this->url("playbooks/{$playbook['id']}/publish") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': '<?= $csrf ?>'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        });
        const ct = response.headers.get('content-type') || '';
        let data = null, text = '';
        if (ct.indexOf('application/json') !== -1) {
            data = await response.json();
        } else {
            text = await response.text();
        }
        
        if (data && data.requires_payment) {
            const amount = Number(data.amount || 0);
            alert('Este playbook requer pagamento de R$ ' + amount.toFixed(2));
            return;
        }
        if (data && data.success) {
            location.reload();
        } else {
            alert((data && data.error) || text || 'Erro ao publicar');
        }
    } catch (error) {
        alert('Erro ao processar');
    }
}
</script>
