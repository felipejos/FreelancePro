<?php

namespace App\Services;

use App\Models\ContentViolation;
use App\Models\User;
use App\Services\NotificationService;

/**
 * ContentMonitorService - Monitoramento de conteúdo para detectar tentativas de contato externo
 */
class ContentMonitorService
{
    /**
     * Padrões de telefone brasileiro
     */
    protected array $phonePatterns = [
        '/\(?\d{2}\)?\s*9?\d{4}[\s\-]?\d{4}/',                    // (11) 99999-9999 ou 11 999999999
        '/\+?55\s*\(?\d{2}\)?\s*9?\d{4}[\s\-]?\d{4}/',           // +55 11 99999-9999
        '/whats?app/i',                                           // whatsapp
        '/telegram/i',                                            // telegram
        '/zap/i',                                                 // zap (gíria)
    ];

    /**
     * Padrões de email
     */
    protected array $emailPatterns = [
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',       // email padrão
        '/[a-zA-Z0-9._%+-]+\s*\[\s*arroba\s*\]/i',               // tentativa de burlar
        '/[a-zA-Z0-9._%+-]+\s*\(\s*at\s*\)/i',                   // (at)
        '/gmail|hotmail|outlook|yahoo/i',                         // menção a provedores
    ];

    /**
     * Padrões de redes sociais/contato externo
     */
    protected array $socialPatterns = [
        '/instagram\.com/i',
        '/facebook\.com/i',
        '/linkedin\.com/i',
        '/twitter\.com/i',
        '/@[a-zA-Z0-9_]{3,}/i',                                   // @username
        '/me\s+chama\s+no/i',                                     // "me chama no"
        '/fora\s+da\s+plataforma/i',                              // "fora da plataforma"
        '/direto\s+comigo/i',                                     // "direto comigo"
    ];

    /**
     * Analisar texto e retornar violações encontradas
     */
    public function analyze(string $text): array
    {
        $violations = [];

        // Verificar telefones
        foreach ($this->phonePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $violations[] = [
                    'type' => 'phone',
                    'pattern' => $pattern,
                    'match' => $matches[0] ?? '',
                    'severity' => 'high',
                ];
            }
        }

        // Verificar emails
        foreach ($this->emailPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $violations[] = [
                    'type' => 'email',
                    'pattern' => $pattern,
                    'match' => $matches[0] ?? '',
                    'severity' => 'high',
                ];
            }
        }

        // Verificar redes sociais
        foreach ($this->socialPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $violations[] = [
                    'type' => 'social',
                    'pattern' => $pattern,
                    'match' => $matches[0] ?? '',
                    'severity' => 'medium',
                ];
            }
        }

        return $violations;
    }

    /**
     * Verificar se texto contém violações
     */
    public function hasViolations(string $text): bool
    {
        return count($this->analyze($text)) > 0;
    }

    /**
     * Processar texto e decidir ação
     * Retorna: ['allowed' => bool, 'violations' => array, 'action' => string]
     */
    public function process(string $text, int $userId, string $context = 'message'): array
    {
        $violations = $this->analyze($text);

        if (empty($violations)) {
            return [
                'allowed' => true,
                'violations' => [],
                'action' => 'none',
            ];
        }

        // Verificar severidade
        $hasHighSeverity = false;
        foreach ($violations as $v) {
            if ($v['severity'] === 'high') {
                $hasHighSeverity = true;
                break;
            }
        }

        // Registrar violação e notificar admins
        $violationId = $this->logViolation($userId, $context, $text, $violations);
        if ($violationId) {
            $this->notifyAdminsPendingReview($userId, $context, $violationId);
        }

        if ($hasHighSeverity) {
            return [
                'allowed' => false,
                'violations' => $violations,
                'action' => 'block',
                'message' => 'Não é permitido compartilhar informações de contato direto. Utilize o chat da plataforma.',
                'violation_id' => $violationId,
            ];
        }

        return [
            'allowed' => true,
            'violations' => $violations,
            'action' => 'warn',
            'message' => 'Atenção: evite compartilhar contatos externos.',
            'violation_id' => $violationId,
        ];
    }

    /**
     * Registrar violação para análise do admin
     */
    protected function logViolation(int $userId, string $context, string $content, array $violations): ?int
    {
        try {
            $violationModel = new ContentViolation();
            return $violationModel->create([
                'user_id' => $userId,
                'context' => $context,
                'content' => $content,
                'violations_json' => json_encode($violations),
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            // Silenciar erros para não interromper o fluxo
        }

        return null;
    }

    /**
     * Notificar administradores sobre pendência de revisão
     */
    protected function notifyAdminsPendingReview(int $userId, string $context, int $violationId): void
    {
        try {
            $userModel = new User();
            $admins = $userModel->query("SELECT id FROM users WHERE user_type = 'admin' LIMIT 5");
            if (empty($admins)) {
                return;
            }

            $reason = "Possível compartilhamento de contato externo ({$context}).";
            $notification = new NotificationService();

            foreach ($admins as $admin) {
                $adminId = (int)($admin['id'] ?? 0);
                if ($adminId > 0) {
                    $notification->notifyAiPendingReview($adminId, $userId, $reason, $violationId, false);
                }
            }
        } catch (\Throwable $e) {
            // evitar quebra do fluxo
        }
    }

    /**
     * Sanitizar texto removendo informações de contato
     */
    public function sanitize(string $text): string
    {
        $sanitized = $text;

        // Remover telefones
        foreach ($this->phonePatterns as $pattern) {
            $sanitized = preg_replace($pattern, '[CONTATO REMOVIDO]', $sanitized);
        }

        // Remover emails
        foreach ($this->emailPatterns as $pattern) {
            $sanitized = preg_replace($pattern, '[EMAIL REMOVIDO]', $sanitized);
        }

        return $sanitized;
    }
}
