<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - FreelancePro</title>
    <link rel="icon" type="image/png" href="<?= $this->url('favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; border-right: 3px solid #3b82f6; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-6 border-b">
                <h1 class="text-xl font-bold text-gray-800">FreelancePro</h1>
                <p class="text-sm text-gray-500">Painel da Empresa</p>
            </div>
            
            <nav class="flex-1 p-4 space-y-1">
                <a href="<?= $this->url('dashboard') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= $this->url('playbooks') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    <span>Playbooks</span>
                </a>
                <a href="<?= $this->url('courses') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    <span>Cursos</span>
                </a>
                <a href="<?= $this->url('employees') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Funcionários</span>
                </a>
                
                <div class="pt-4 mt-4 border-t">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase mb-2">Freelancers</p>
                    <a href="<?= $this->url('projects') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                        <span>Projetos</span>
                    </a>
                    <a href="<?= $this->url('contracts') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                        <span>Contratos</span>
                    </a>
                </div>
                
                <div class="pt-4 mt-4 border-t">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase mb-2">Financeiro</p>
                    <a href="<?= $this->url('subscription') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                        <span>Assinatura</span>
                    </a>
                    <a href="<?= $this->url('payment/history') ?>" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                        <span>Histórico</span>
                    </a>
                </div>
            </nav>
            
            <div class="p-4 border-t">
                <a href="<?= $this->url('account') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Minha Conta</span>
                </a>
                <a href="<?= $this->url('logout') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Sair</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm px-8 py-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800"><?= $title ?? 'Dashboard' ?></h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Olá, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuário') ?></span>
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                        <?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-8">
                <?php
                $flash = $_SESSION['flash'] ?? [];
                unset($_SESSION['flash']);
                
                if (!empty($flash['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?= htmlspecialchars($flash['error']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($flash['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <?= htmlspecialchars($flash['success']) ?>
                    </div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
