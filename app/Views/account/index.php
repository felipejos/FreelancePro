<div class="max-w-3xl mx-auto">
    <!-- Profile Info -->
    <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Informações Pessoais</h2>
        
        <form action="<?= $this->url('account') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($userData['name'] ?? '') ?>" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="(00) 00000-0000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                    <input type="text" name="cpf" value="<?= htmlspecialchars($userData['cpf'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="000.000.000-00">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                    <input type="date" name="birth_date" value="<?= $userData['birth_date'] ?? '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                Salvar Alterações
            </button>
        </form>
    </div>
    
    <!-- Address -->
    <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Endereço</h2>
        
        <form id="addressForm" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rua</label>
                    <input type="text" name="street" value="<?= htmlspecialchars($address['street'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="number" value="<?= htmlspecialchars($address['number'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complement" value="<?= htmlspecialchars($address['complement'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                    <input type="text" name="neighborhood" value="<?= htmlspecialchars($address['neighborhood'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="zip_code" value="<?= htmlspecialchars($address['zip_code'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="00000-000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($address['city'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <input type="text" name="state" value="<?= htmlspecialchars($address['state'] ?? '') ?>" maxlength="2"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="UF">
                </div>
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                Salvar Endereço
            </button>
        </form>
    </div>
    
    <!-- Change Password Link -->
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Segurança</h2>
        <a href="<?= $this->url('account/password') ?>" class="text-blue-600 hover:underline flex items-center gap-2">
            <i data-lucide="key" class="w-5 h-5"></i>
            Alterar minha senha
        </a>
    </div>
</div>

<script>
document.getElementById('addressForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= $this->url('account/address') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Endereço atualizado com sucesso!');
        } else {
            alert(data.error || 'Erro ao salvar endereço');
        }
    } catch (error) {
        alert('Erro ao processar requisição');
    }
});
</script>
