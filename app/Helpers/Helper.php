<?php

namespace App\Helpers;

/**
 * Helper - Funções auxiliares
 */
class Helper
{
    /**
     * Gerar URL completa
     */
    public static function url(string $path = ''): string
    {
        $config = require ROOT_PATH . '/config/app.php';
        return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
    }

    /**
     * Gerar URL de asset
     */
    public static function asset(string $path): string
    {
        return self::url('public/' . ltrim($path, '/'));
    }

    /**
     * Escapar HTML
     */
    public static function e(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Formatar moeda BRL
     */
    public static function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Formatar data
     */
    public static function date(string $date, string $format = 'd/m/Y'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Formatar data e hora
     */
    public static function datetime(string $datetime, string $format = 'd/m/Y H:i'): string
    {
        return date($format, strtotime($datetime));
    }

    /**
     * Gerar CSRF token field
     */
    public static function csrf(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    /**
     * Verificar CSRF token
     */
    public static function verifyCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Obter mensagem flash
     */
    public static function flash(string $type = null)
    {
        if ($type === null) {
            $flash = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Definir mensagem flash
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Verificar se é requisição AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obter IP do cliente
     */
    public static function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '0.0.0.0';
        
        return $ip;
    }

    /**
     * Gerar slug
     */
    public static function slug(string $string): string
    {
        $string = preg_replace('/[áàãâä]/ui', 'a', $string);
        $string = preg_replace('/[éèêë]/ui', 'e', $string);
        $string = preg_replace('/[íìîï]/ui', 'i', $string);
        $string = preg_replace('/[óòõôö]/ui', 'o', $string);
        $string = preg_replace('/[úùûü]/ui', 'u', $string);
        $string = preg_replace('/[ç]/ui', 'c', $string);
        $string = preg_replace('/[^a-z0-9]/i', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return strtolower(trim($string, '-'));
    }

    /**
     * Limitar texto
     */
    public static function limit(string $string, int $length = 100, string $end = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length) . $end;
    }

    /**
     * Formatar CPF
     */
    public static function formatCpf(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    /**
     * Formatar telefone
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        }
        
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }

    /**
     * Validar email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar CPF
     */
    public static function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1*$/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Gerar senha aleatória
     */
    public static function generatePassword(int $length = 8): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Tempo relativo (ex: "há 2 horas")
     */
    public static function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'agora';
        } elseif ($diff < 3600) {
            $mins = round($diff / 60);
            return "há {$mins} minuto" . ($mins > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = round($diff / 3600);
            return "há {$hours} hora" . ($hours > 1 ? 's' : '');
        } elseif ($diff < 604800) {
            $days = round($diff / 86400);
            return "há {$days} dia" . ($days > 1 ? 's' : '');
        } else {
            return self::date($datetime);
        }
    }
}
