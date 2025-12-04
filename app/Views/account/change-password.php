<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Alterar Senha</h2>
        
        <form action="<?= $this->url('account/password') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha atual</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
                <input type="password" name="new_password" required minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Mínimo 6 caracteres">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nova senha</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                    Alterar Senha
                </button>
                <a href="<?= $this->url('account') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
