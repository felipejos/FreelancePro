<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FreelancePro' ?> - FreelancePro</title>
    <link rel="icon" type="image/png" href="<?= $this->url('favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-600 to-purple-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white">FreelancePro</h1>
            <p class="text-blue-200 mt-2">Plataforma de Treinamentos + Freelancers</p>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php
            // Mensagens flash
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
        
        <!-- Footer -->
        <p class="text-center text-blue-200 text-sm mt-6">
            &copy; <?= date('Y') ?> FreelancePro. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
