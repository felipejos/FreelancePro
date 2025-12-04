<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Usuários</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['total_users'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Empresas</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['companies'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i data-lucide="building" class="w-6 h-6 text-purple-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Profissionais</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['professionals'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <i data-lucide="user-check" class="w-6 h-6 text-emerald-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Funcionários</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['employees'] ?? 0 ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i data-lucide="user" class="w-6 h-6 text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Payment Stats -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Resumo Financeiro</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-green-600">Total Recebido</p>
            <p class="text-2xl font-bold text-green-700">R$ <?= number_format($paymentStats['total_received'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="p-4 bg-yellow-50 rounded-lg">
            <p class="text-sm text-yellow-600">Pendente</p>
            <p class="text-2xl font-bold text-yellow-700">R$ <?= number_format($paymentStats['total_pending'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-600">Total Transações</p>
            <p class="text-2xl font-bold text-blue-700"><?= $paymentStats['total'] ?? 0 ?></p>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="<?= $this->url('admin/configs') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <i data-lucide="settings" class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mb-4"></i>
        <h3 class="font-semibold text-gray-800">Configurações</h3>
        <p class="text-sm text-gray-500">APIs, chaves e valores</p>
    </a>
    
    <a href="<?= $this->url('admin/email') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <i data-lucide="mail" class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mb-4"></i>
        <h3 class="font-semibold text-gray-800">Email</h3>
        <p class="text-sm text-gray-500">Configurar servidor SMTP</p>
    </a>
    
    <a href="<?= $this->url('admin/ai-logs') ?>" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
        <i data-lucide="cpu" class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mb-4"></i>
        <h3 class="font-semibold text-gray-800">Logs de IA</h3>
        <p class="text-sm text-gray-500">Monitorar uso da OpenAI</p>
    </a>
</div>
