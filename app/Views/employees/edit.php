<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Editar Funcionário</h2>

        <form action="<?= $this->url('employees/' . $employee['id']) ?>" method="POST" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                <input type="text" name="name" required
                       value="<?= htmlspecialchars($employee['name']) ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($employee['email']) ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inactive" <?= $employee['status'] === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha (opcional)</label>
                <input type="password" name="password" minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="Preencha apenas se desejar alterar a senha">
                <p class="text-sm text-gray-500 mt-1">Deixe em branco para manter a senha atual.</p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold transition">
                    Salvar alterações
                </button>
                <a href="<?= $this->url('employees') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
