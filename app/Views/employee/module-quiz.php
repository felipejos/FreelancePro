<!-- Header -->
<div class="mb-6">
    <a href="<?= $this->url('employee/courses/' . (int)$course['id']) ?>" class="text-emerald-600 hover:underline text-sm mb-2 inline-block">← Voltar ao Curso</a>
    <h1 class="text-2xl font-bold text-gray-800">Avaliação do Módulo: <?= htmlspecialchars($module['title'] ?? '') ?></h1>
    <p class="text-gray-500 mt-1">Tentativas utilizadas: <?= (int)($result['attempts'] ?? 0) ?> de <?= (int)($maxAttempts ?? 0) ?></p>
</div>

<?php if (!empty($enrollment['is_locked'])): ?>
<div class="p-4 mb-6 rounded-lg bg-red-50 text-red-700 border border-red-200">
    Sua matrícula está bloqueada. Solicite liberação ao gestor.
</div>
<?php endif; ?>

<?php if (!empty($result) && (int)($result['passed']) === 1): ?>
<!-- Result Card (Aprovado) -->
<div class="bg-white rounded-xl shadow-sm p-8 mb-8">
    <div class="text-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 bg-green-100">
            <i data-lucide="check" class="w-10 h-10 text-green-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-green-600">Aprovado!</h2>
        <p class="text-4xl font-bold text-gray-800 mt-2"><?= number_format((float)($result['score'] ?? 0), 1) ?>%</p>
        <p class="text-gray-500 mt-2">Nota mínima: 70%</p>
    </div>
</div>
<?php elseif (!empty($result) && empty($result['passed']) && (int)($result['attempts'] ?? 0) >= (int)($maxAttempts ?? 0)): ?>
<!-- Result Card (Bloqueado) -->
<div class="bg-white rounded-xl shadow-sm p-8 mb-8">
    <div class="text-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100">
            <i data-lucide="lock" class="w-10 h-10 text-red-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-red-600">Tentativas esgotadas</h2>
        <p class="text-gray-700 mt-2">Você atingiu o limite de tentativas para este módulo. Solicite liberação ao gestor.</p>
    </div>
</div>
<?php endif; ?>

<!-- Quiz -->
<?php
$attemptsUsed = (int)($result['attempts'] ?? 0);
$attemptsLimit = (int)($maxAttempts ?? 0);
$lockedModule = (!empty($result) && empty($result['passed']) && $attemptsUsed >= $attemptsLimit);
$canAnswer = !$lockedModule && (empty($result['passed']) || (int)$result['passed'] === 0);
?>

<?php if (!empty($questions) && $canAnswer): ?>
<div class="bg-white rounded-xl shadow-sm p-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Questionário do Módulo</h2>

    <form id="moduleQuizForm" class="space-y-8">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

        <?php foreach ($questions as $index => $question): ?>
        <div class="p-6 bg-gray-50 rounded-lg">
            <p class="font-medium text-gray-800 mb-4"><?= $index + 1 ?>. <?= htmlspecialchars($question['question_text']) ?></p>

            <div class="space-y-2">
                <?php foreach (['A','B','C','D'] as $opt): ?>
                <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-emerald-500 cursor-pointer transition">
                    <input type="radio" name="answers[<?= (int)$question['id'] ?>]" value="<?= $opt ?>" required class="text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-3"><?= $opt ?>) <?= htmlspecialchars($question['option_' . strtolower($opt)]) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" id="submitModuleQuizBtn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-lg font-semibold transition">
            Enviar Respostas
        </button>
    </form>
</div>
<?php elseif (empty($questions)): ?>
<div class="p-4 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200">
    Nenhuma questão disponível para este módulo.
</div>
<?php endif; ?>

<script>
const form = document.getElementById('moduleQuizForm');
form?.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!confirm('Tem certeza que deseja enviar suas respostas?')) return;

    const btn = document.getElementById('submitModuleQuizBtn');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const formData = new FormData(this);
        const res = await fetch('<?= $this->url("employee/courses/{$course['id']}/modules/{$module['id']}/quiz") ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            alert(data.message || 'Respostas enviadas.');
            location.reload();
        } else if (data.locked) {
            alert(data.error || 'Limite de tentativas atingido. Matrícula bloqueada ou módulo bloqueado.');
            location.reload();
        } else {
            alert(data.error || 'Erro ao enviar');
            btn.disabled = false;
            btn.textContent = 'Enviar Respostas';
        }
    } catch (err) {
        alert('Erro ao processar');
        btn.disabled = false;
        btn.textContent = 'Enviar Respostas';
    }
});
</script>
