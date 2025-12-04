<h2 class="text-2xl font-bold text-gray-800 mb-2">Esqueceu sua senha?</h2>
<p class="text-gray-600 mb-6">Digite seu email e enviaremos instruções para redefinir sua senha.</p>

<form action="<?= $this->url('forgot-password') ?>" method="POST" class="space-y-5">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="seu@email.com">
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
        Enviar instruções
    </button>
</form>

<div class="mt-6 text-center">
    <a href="<?= $this->url('login') ?>" class="text-blue-600 hover:underline">← Voltar ao login</a>
</div>
