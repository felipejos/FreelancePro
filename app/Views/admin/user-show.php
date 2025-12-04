<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Detalhes do Usuário</h1>
        <p class="text-sm text-gray-600">Visualize os dados cadastrais completos deste usuário.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Nome</p>
                <p class="text-sm text-gray-800 font-medium"><?= htmlspecialchars($user['name'] ?? '') ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Email</p>
                <p class="text-sm text-gray-800"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Tipo</p>
                <p class="text-sm text-gray-800">
                    <?php
                    $types = [
                        'admin' => 'Admin',
                        'company' => 'Empresa',
                        'professional' => 'Profissional',
                        'employee' => 'Funcionário'
                    ];
                    echo $types[$user['user_type']] ?? $user['user_type'];
                    ?>
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Status</p>
                <p class="text-sm text-gray-800"><?= $user['status'] === 'active' ? 'Ativo' : 'Bloqueado' ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Telefone</p>
                <p class="text-sm text-gray-800"><?= htmlspecialchars($user['phone'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">CPF</p>
                <p class="text-sm text-gray-800"><?= htmlspecialchars($user['cpf'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Data de nascimento</p>
                <p class="text-sm text-gray-800">
                    <?php if (!empty($user['birth_date'])): ?>
                        <?= date('d/m/Y', strtotime($user['birth_date'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Criado em</p>
                <p class="text-sm text-gray-800">
                    <?php if (!empty($user['created_at'])): ?>
                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Último login</p>
                <p class="text-sm text-gray-800">
                    <?php if (!empty($user['last_login'])): ?>
                        <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                    <?php else: ?>
                        Nunca acessou
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div>
        <a href="<?= $this->url('admin/users') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Voltar para lista de usuários
        </a>
    </div>
</div>
