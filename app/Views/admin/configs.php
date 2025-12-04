<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Configurações do Sistema</h2>
        
        <form id="configForm" class="space-y-8">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <!-- API Keys -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i data-lucide="key" class="w-5 h-5"></i>
                    Chaves de API
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OpenAI API Key</label>
                        <input type="password" name="configs[openai_api_key]" 
                               value="<?= $configs['openai_api_key']['config_value'] ?? '' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="sk-...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ASSAS API Key</label>
                        <input type="password" name="configs[assas_api_key]" 
                               value="<?= $configs['assas_api_key']['config_value'] ?? '' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ambiente ASSAS</label>
                        <select name="configs[assas_environment]" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="sandbox" <?= ($configs['assas_environment']['config_value'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testes)</option>
                            <option value="production" <?= ($configs['assas_environment']['config_value'] ?? '') === 'production' ? 'selected' : '' ?>>Produção</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- reCAPTCHA -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                    reCAPTCHA
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Key</label>
                        <input type="text" name="configs[recaptcha_site_key]" 
                               value="<?= $configs['recaptcha_site_key']['config_value'] ?? '' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                        <input type="password" name="configs[recaptcha_secret_key]" 
                               value="<?= $configs['recaptcha_secret_key']['config_value'] ?? '' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>
            
            <!-- Valores -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                    Valores
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taxa de Registro (R$)</label>
                        <input type="number" step="0.01" name="configs[registration_fee]" 
                               value="<?= $configs['registration_fee']['config_value'] ?? '29.90' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensalidade (R$)</label>
                        <input type="number" step="0.01" name="configs[monthly_fee]" 
                               value="<?= $configs['monthly_fee']['config_value'] ?? '29.90' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taxa por Playbook (R$)</label>
                        <input type="number" step="0.01" name="configs[playbook_fee]" 
                               value="<?= $configs['playbook_fee']['config_value'] ?? '19.90' ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taxa Freelancer (%)</label>
                        <input type="number" step="0.01" name="configs[freelancer_fee]" 
                               value="<?= ($configs['freelancer_fee']['config_value'] ?? 0.07) * 100 ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="7">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                Salvar Configurações
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('configForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= $this->url('admin/configs') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Configurações salvas com sucesso!');
        } else {
            alert(data.error || 'Erro ao salvar');
        }
    } catch (error) {
        alert('Erro ao processar requisição');
    }
});
</script>
