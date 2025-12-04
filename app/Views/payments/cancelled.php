<div class="max-w-lg mx-auto text-center py-12">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i data-lucide="x-circle" class="w-10 h-10 text-red-600"></i>
    </div>
    
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Assinatura cancelada</h1>
    <p class="text-gray-600 mb-8">Sua assinatura foi cancelada. Você pode reativar a qualquer momento escolhendo um plano novamente.</p>
    
    <div class="flex items-center justify-center gap-3">
        <a href="<?= $this->url('plans') ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
            Ver planos
            <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </a>
        <a href="<?= $this->url('dashboard') ?>" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-lg transition">
            Voltar ao dashboard
        </a>
    </div>
</div>
