<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Curso' ?> - FreelancePro</title>
    <link rel="icon" type="image/png" href="<?= $this->url('favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <?php
        $user = $_SESSION['user'] ?? null;
        $userType = $user['user_type'] ?? null;
        $dashboardRoute = 'dashboard';
        if ($userType === 'employee') {
            $dashboardRoute = 'employee/dashboard';
        } elseif ($userType === 'professional') {
            $dashboardRoute = 'professional/dashboard';
        } elseif ($userType === 'admin') {
            $dashboardRoute = 'admin/dashboard';
        }
        ?>
        <!-- Top bar -->
        <header class="bg-white shadow-sm">
            <div class="max-w-6xl mx-auto px-4 md:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-lg">
                        FP
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">FreelancePro</p>
                        <p class="text-xs text-gray-500">Área de cursos e treinamentos</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <?php if ($user): ?>
                        <a href="<?= $this->url($dashboardRoute) ?>" class="text-gray-600 hover:text-gray-900 flex items-center gap-1">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Voltar ao painel</span>
                        </a>
                        <span class="text-gray-400">|</span>
                        <a href="<?= $this->url('logout') ?>" class="text-red-600 hover:text-red-700 flex items-center gap-1">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Sair</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 py-6 px-4 md:px-8">
            <div class="max-w-6xl mx-auto">
                <?php
                $flash = $_SESSION['flash'] ?? [];
                unset($_SESSION['flash']);
                if (!empty($flash['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        <?= htmlspecialchars($flash['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flash['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        <?= htmlspecialchars($flash['success']) ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
