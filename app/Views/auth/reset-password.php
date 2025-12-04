<h2 class="text-2xl font-bold text-gray-800 mb-6">Redefinir senha</h2>

<form action="<?= $this->url('reset-password') ?>" method="POST" class="space-y-5">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
        <input type="password" name="password" required minlength="6"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Mínimo 6 caracteres">
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
        Redefinir senha
    </button>
</form>
