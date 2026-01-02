<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - FreelancePro Admin</title>
    <link rel="icon" type="image/png" href="<?= $this->url('favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 flex flex-col">
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold text-white">FreelancePro</h1>
                <p class="text-sm text-gray-400">Painel Admin</p>
            </div>
            
            <nav class="flex-1 p-4 space-y-1">
                <a href="<?= $this->url('admin/dashboard') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= $this->url('admin/users') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Usuários</span>
                </a>
                <a href="<?= $this->url('admin/payments') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    <span>Pagamentos</span>
                </a>
                <a href="<?= $this->url('admin/configs') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Configurações</span>
                </a>
                <a href="<?= $this->url('admin/email') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    <span>Email</span>
                </a>
                <a href="<?= $this->url('admin/ai-logs') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="cpu" class="w-5 h-5"></i>
                    <span>Logs de IA</span>
                </a>
                <a href="<?= $this->url('admin/violations') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    <span>Violações</span>
                </a>
            </nav>
            
            <div class="p-4 border-t border-gray-700">
                <a href="<?= $this->url('account') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 transition">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Minha Conta</span>
                </a>
                <a href="<?= $this->url('logout') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-gray-700 transition">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Sair</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-auto bg-gray-100">
            <header class="bg-white shadow-sm px-8 py-4">
                <h2 class="text-xl font-semibold text-gray-800"><?= $title ?? 'Admin' ?></h2>
            </header>
            
            <div class="p-8">
                <?php
                $flash = $_SESSION['flash'] ?? [];
                unset($_SESSION['flash']);
                if (!empty($flash['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><?= htmlspecialchars($flash['success']) ?></div>
                <?php endif; ?>
                <?php if (!empty($flash['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><?= htmlspecialchars($flash['error']) ?></div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
