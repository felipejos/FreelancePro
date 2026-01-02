<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notificações</h1>
        <button onclick="markAllRead()" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
            Marcar todas como lidas
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="p-8 text-center text-gray-500">
                <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                <p>Nenhuma notificação ainda.</p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-100">
                <?php foreach ($notifications as $notification): ?>
                    <?php
                        $referenceUrl = null;
                        if (!empty($notification['reference_id'])) {
                            if (($userType ?? 'company') === 'professional') {
                                $referenceUrl = $this->url("professional/projects/{$notification['reference_id']}");
                            } else {
                                // padrão para empresa/employee/admin
                                $referenceUrl = $this->url("projects/{$notification['reference_id']}");
                            }
                        }
                    ?>
                    <li class="p-4 hover:bg-gray-50 transition <?= $notification['is_read'] ? 'opacity-60' : 'bg-purple-50/30' ?>">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <?php
                                $iconMap = [
                                    'proposal_new' => 'file-plus',
                                    'proposal_accepted' => 'check-circle',
                                    'proposal_rejected' => 'x-circle',
                                    'payment_confirmed' => 'credit-card',
                                    'pending_review' => 'alert-triangle',
                                ];
                                $icon = $iconMap[$notification['type']] ?? 'bell';
                                $colorMap = [
                                    'proposal_new' => 'text-blue-600',
                                    'proposal_accepted' => 'text-green-600',
                                    'proposal_rejected' => 'text-red-600',
                                    'payment_confirmed' => 'text-emerald-600',
                                    'pending_review' => 'text-yellow-600',
                                ];
                                $color = $colorMap[$notification['type']] ?? 'text-gray-600';
                                ?>
                                <i data-lucide="<?= $icon ?>" class="w-5 h-5 <?= $color ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($notification['title']) ?></p>
                                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($notification['message']) ?></p>
                                <p class="text-xs text-gray-400 mt-2">
                                    <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <?php if ($referenceUrl): ?>
                                    <a href="<?= $referenceUrl ?>" class="text-xs inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-purple-200 text-purple-600 hover:bg-purple-50 transition">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        Ver projeto
                                    </a>
                                <?php endif; ?>
                                <?php if (!$notification['is_read']): ?>
                                    <button onclick="markRead(<?= $notification['id'] ?>)" class="text-xs text-purple-600 hover:underline">
                                        Marcar como lida
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
async function markRead(id) {
    try {
        const res = await fetch(`<?= $this->url('notifications') ?>/${id}/read`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        if (res.ok) {
            location.reload();
        }
    } catch (e) {
        console.error(e);
    }
}

async function markAllRead() {
    try {
        const res = await fetch('<?= $this->url('notifications/read-all') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        if (res.ok) {
            location.reload();
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
