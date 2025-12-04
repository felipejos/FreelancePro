<div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Escolha seu Plano</h1>
        <p class="text-gray-600">Selecione o plano ideal para sua empresa</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($plans as $plan): ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden <?= $plan['name'] === 'Profissional' ? 'ring-2 ring-blue-500' : '' ?>">
            <?php if ($plan['name'] === 'Profissional'): ?>
            <div class="bg-blue-500 text-white text-center py-2 text-sm font-semibold">Mais Popular</div>
            <?php endif; ?>
            
            <div class="p-8">
                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($plan['name']) ?></h3>
                <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars($plan['description'] ?? '') ?></p>
                
                <div class="mt-6">
                    <span class="text-4xl font-bold text-gray-800">R$ <?= number_format($plan['price'], 2, ',', '.') ?></span>
                    <span class="text-gray-500">/mês</span>
                </div>
                
                <ul class="mt-6 space-y-3">
                    <?php foreach ($plan['features'] ?? [] as $feature): ?>
                    <li class="flex items-center gap-2 text-gray-600">
                        <i data-lucide="check" class="w-5 h-5 text-green-500"></i>
                        <span><?= htmlspecialchars($feature) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <a href="<?= $this->url("checkout/{$plan['id']}") ?>" 
                   class="block w-full mt-8 py-3 text-center rounded-lg font-semibold transition
                          <?= $plan['name'] === 'Profissional' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' ?>">
                    Assinar Plano
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
