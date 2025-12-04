<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Freelancer' ?> - FreelancePro</title>
    <link rel="icon" type="image/png" href="<?= $this->url('favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-purple-700 flex flex-col">
            <div class="p-6 border-b border-purple-600">
                <h1 class="text-xl font-bold text-white">FreelancePro</h1>
                <p class="text-sm text-purple-200">Área do Freelancer</p>
            </div>
            
            <nav class="flex-1 p-4 space-y-1">
                <a href="<?= $this->url('professional/dashboard') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= $this->url('professional/projects') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="search" class="w-5 h-5"></i>
                    <span>Buscar Projetos</span>
                </a>
                <a href="<?= $this->url('professional/proposals') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Minhas Propostas</span>
                </a>
                <a href="<?= $this->url('professional/contracts') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span>Meus Contratos</span>
                </a>
                <a href="<?= $this->url('professional/reviews') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="star" class="w-5 h-5"></i>
                    <span>Avaliações</span>
                </a>
            </nav>
            
            <div class="p-4 border-t border-purple-600">
                <a href="<?= $this->url('account') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white hover:bg-purple-600 transition">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Minha Conta</span>
                </a>
                <a href="<?= $this->url('logout') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-purple-200 hover:bg-purple-600 transition">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Sair</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm px-8 py-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800"><?= $title ?? 'Freelancer' ?></h2>
                <span class="text-sm text-gray-600"><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></span>
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
