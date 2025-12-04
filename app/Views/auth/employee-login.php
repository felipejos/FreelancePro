<h2 class="text-2xl font-bold text-gray-800 mb-2">Portal do Funcionário</h2>
<p class="text-gray-600 mb-6">Acesse sua área de treinamentos</p>

<form action="<?= $this->url('employee/login') ?>" method="POST" class="space-y-5">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email corporativo</label>
        <input type="email" name="email" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="seu@empresa.com">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
        <input type="password" name="password" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Sua senha">
    </div>
    
    <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition">
        Acessar Portal
    </button>
</form>

<div class="mt-6 text-center">
    <a href="<?= $this->url('login') ?>" class="text-gray-500 hover:text-gray-700">← Sou empresa/profissional</a>
</div>
