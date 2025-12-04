<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Cadastrar Novo Funcionário</h2>
        
        <form action="<?= $this->url('employees') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="Nome do funcionário">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="email@empresa.com">
                <p class="text-sm text-gray-500 mt-1">O funcionário usará este email para acessar o portal.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha inicial</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="Mínimo 6 caracteres">
                <p class="text-sm text-gray-500 mt-1">O funcionário poderá alterar depois.</p>
            </div>
            
            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold transition">
                    Cadastrar Funcionário
                </button>
                <a href="<?= $this->url('employees') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
