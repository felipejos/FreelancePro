<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Criar Novo Projeto</h2>
        
        <form action="<?= $this->url('projects') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título do Projeto</label>
                <input type="text" name="title" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       placeholder="Ex: Desenvolvimento de Website Institucional">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea name="description" rows="5" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                          placeholder="Descreva detalhadamente o projeto, requisitos e expectativas..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">Selecione uma categoria</option>
                    <option value="desenvolvimento">Desenvolvimento</option>
                    <option value="design">Design</option>
                    <option value="marketing">Marketing</option>
                    <option value="redacao">Redação</option>
                    <option value="consultoria">Consultoria</option>
                    <option value="outros">Outros</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Habilidades Necessárias</label>
                <input type="text" name="skills"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       placeholder="Ex: PHP, MySQL, JavaScript (separe por vírgulas)">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Orçamento Mínimo (R$)</label>
                    <input type="number" name="budget_min" step="0.01" min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Orçamento Máximo (R$)</label>
                    <input type="number" name="budget_max" step="0.01" min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                           placeholder="0.00">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Entrega</label>
                <input type="date" name="deadline" min="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Taxa de serviço:</strong> Será cobrada uma taxa de 7% sobre o valor do contrato quando você aceitar uma proposta.
                </p>
            </div>
            
            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-semibold transition">
                    Publicar Projeto
                </button>
                <a href="<?= $this->url('projects') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
