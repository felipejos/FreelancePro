<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Configuração de Email</h2>
        
        <form id="emailForm" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Envio</label>
                <select name="mail_driver" id="mailDriver" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="smtp" <?= ($config['mail_driver'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                    <option value="mail" <?= ($config['mail_driver'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail (padrão)</option>
                </select>
            </div>
            
            <div id="smtpFields" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Servidor SMTP</label>
                        <input type="text" name="smtp_host" value="<?= htmlspecialchars($config['smtp_host'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="smtp.gmail.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Porta</label>
                        <input type="number" name="smtp_port" value="<?= $config['smtp_port'] ?? 587 ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário SMTP</label>
                        <input type="text" name="smtp_username" value="<?= htmlspecialchars($config['smtp_username'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="seu@email.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha SMTP</label>
                        <input type="password" name="smtp_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Deixe em branco para manter">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Criptografia</label>
                    <select name="smtp_encryption" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="tls" <?= ($config['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($config['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= ($config['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Nenhuma</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Remetente</label>
                    <input type="email" name="from_address" value="<?= htmlspecialchars($config['from_address'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="noreply@seudominio.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Remetente</label>
                    <input type="text" name="from_name" value="<?= htmlspecialchars($config['from_name'] ?? 'FreelancePro') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                    Salvar Configuração
                </button>
            </div>
        </form>
        
        <!-- Test Email -->
        <div class="mt-8 pt-8 border-t">
            <h3 class="font-semibold text-gray-800 mb-4">Testar Envio</h3>
            <div class="flex gap-4">
                <input type="email" id="testEmail" placeholder="email@teste.com"
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button onclick="testEmail()" class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-lg transition">
                    Enviar Teste
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= $this->url('admin/email') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        alert(data.success ? 'Configuração salva!' : (data.error || 'Erro'));
    } catch (error) {
        alert('Erro ao processar requisição');
    }
});

async function testEmail() {
    const email = document.getElementById('testEmail').value;
    if (!email) {
        alert('Informe um email');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('email', email);
        
        const response = await fetch('<?= $this->url('admin/email/test') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        alert(data.success ? 'Email de teste enviado!' : (data.error || 'Erro'));
    } catch (error) {
        alert('Erro ao enviar');
    }
}
</script>
