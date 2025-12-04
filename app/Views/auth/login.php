<h2 class="text-2xl font-bold text-gray-800 mb-6">Entrar na sua conta</h2>

<form action="<?= $this->url('login') ?>" method="POST" class="space-y-5">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="seu@email.com">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
        <input type="password" name="password" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Sua senha">
    </div>
    
    <div class="flex items-center justify-between">
        <label class="flex items-center">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-600">Lembrar-me</span>
        </label>
        <a href="<?= $this->url('forgot-password') ?>" class="text-sm text-blue-600 hover:underline">Esqueci minha senha</a>
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
        Entrar
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-gray-600">Não tem uma conta?</p>
    <a href="<?= $this->url('register') ?>" class="text-blue-600 font-semibold hover:underline">Criar conta gratuita</a>
</div>

<div class="mt-6 pt-6 border-t text-center">
    <a href="<?= $this->url('employee/login') ?>" class="text-sm text-gray-500 hover:text-gray-700">
        Sou funcionário → Acessar portal
    </a>
</div>
