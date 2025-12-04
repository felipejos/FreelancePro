<!-- Header -->
<div class="mb-6">
    <a href="<?= $this->url("playbooks/{$playbook['id']}") ?>" class="text-blue-600 hover:underline text-sm mb-2 inline-block">← Voltar ao Playbook</a>
    <h1 class="text-2xl font-bold text-gray-800">Atribuir: <?= htmlspecialchars($playbook['title']) ?></h1>
</div>

<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <form id="assignForm" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Selecione os funcionários</label>
                
                <?php if (empty($employees)): ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 mb-4">Nenhum funcionário cadastrado.</p>
                    <a href="<?= $this->url('employees/create') ?>" class="text-blue-600 hover:underline">Cadastrar funcionário</a>
                </div>
                <?php else: ?>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <?php foreach ($employees as $employee): ?>
                    <label class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer <?= in_array($employee['id'], $assignedIds) ? 'opacity-50' : '' ?>">
                        <input type="checkbox" name="employee_ids[]" value="<?= $employee['id'] ?>" 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               <?= in_array($employee['id'], $assignedIds) ? 'disabled checked' : '' ?>>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($employee['name']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($employee['email']) ?></p>
                        </div>
                        <?php if (in_array($employee['id'], $assignedIds)): ?>
                        <span class="ml-auto text-xs text-gray-500">Já atribuído</span>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prazo (opcional)</label>
                <input type="date" name="due_date" min="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                Atribuir Treinamento
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('assignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= $this->url("playbooks/{$playbook['id']}/assign") ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            window.location.href = '<?= $this->url("playbooks/{$playbook['id']}") ?>';
        } else {
            alert(data.error || 'Erro ao atribuir');
        }
    } catch (error) {
        alert('Erro ao processar requisição');
    }
});
</script>
