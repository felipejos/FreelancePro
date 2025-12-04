<h2 class="text-2xl font-bold text-gray-800 mb-6">Criar sua conta</h2>

<form action="<?= $this->url('register') ?>" method="POST" class="space-y-5">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    
    <div>
        <label id="nameLabel" class="block text-sm font-medium text-gray-700 mb-1">Nome da empresa (preferencial)</label>
        <input type="text" name="name" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Nome da empresa">
    </div>
    
    <div>
        <label id="emailLabel" class="block text-sm font-medium text-gray-700 mb-1">Email corporativo (preferencial)</label>
        <input type="email" name="email" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="email@minhaempresa.com">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
        <input type="password" name="password" required minlength="6"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Mínimo 6 caracteres">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de conta</label>
        <select name="user_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <option value="company">Empresa</option>
            <option value="professional">Profissional/Freelancer</option>
        </select>
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
        Criar conta
    </button>
</form>

<script>
(function(){
  var select=document.querySelector('select[name="user_type"]');
  var nameLabel=document.getElementById('nameLabel');
  var nameInput=document.querySelector('input[name="name"]');
  var emailLabel=document.getElementById('emailLabel');
  var emailInput=document.querySelector('input[name="email"]');
  function update(){
    if(select && nameLabel && nameInput && emailLabel && emailInput){
      if(select.value==='company'){
        nameLabel.textContent='Nome da empresa (preferencial)';
        nameInput.placeholder='Nome da empresa';
        emailLabel.textContent='Email corporativo (preferencial)';
        emailInput.placeholder='email@minhaempresa.com';
      }else{
        nameLabel.textContent='Nome do profissional';
        nameInput.placeholder='Seu nome';
        emailLabel.textContent='Email';
        emailInput.placeholder='seu@email.com';
      }
    }
  }
  if(select){
    select.addEventListener('change',update);
    update();
  }
})();
</script>

<div class="mt-6 text-center">
    <p class="text-gray-600">Já tem uma conta?</p>
    <a href="<?= $this->url('login') ?>" class="text-blue-600 font-semibold hover:underline">Fazer login</a>
</div>
