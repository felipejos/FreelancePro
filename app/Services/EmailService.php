<?php

namespace App\Services;

use App\Models\EmailConfig;

/**
 * EmailService - Serviço de envio de emails
 */
class EmailService
{
    protected ?array $config;

    public function __construct()
    {
        $emailModel = new EmailConfig();
        $this->config = $emailModel->getActiveWithPassword();
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = false): bool
    {
        if (!$this->config || !$this->config['is_active']) {
            throw new \Exception('Configuração de email não encontrada ou inativa.');
        }

        if ($this->config['mail_driver'] === 'smtp') {
            return $this->sendSmtp($to, $subject, $body, $isHtml);
        }

        return $this->sendMail($to, $subject, $body, $isHtml);
    }

    protected function sendSmtp(string $to, string $subject, string $body, bool $isHtml): bool
    {
        $host = $this->config['smtp_host'];
        $port = $this->config['smtp_port'];
        $username = $this->config['smtp_username'];
        $password = $this->config['smtp_password'];
        $encryption = $this->config['smtp_encryption'];
        $fromAddress = $this->config['from_address'];
        $fromName = $this->config['from_name'];

        // Construir headers
        $headers = [];
        $headers[] = "From: {$fromName} <{$fromAddress}>";
        $headers[] = "Reply-To: {$fromAddress}";
        $headers[] = "MIME-Version: 1.0";
        
        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        // Conectar ao servidor SMTP
        // - SSL (porta 465): usa wrapper ssl:// direto
        // - TLS (porta 587): conecta em TCP normal e faz STARTTLS depois
        $connectHost = $host;
        if ($encryption === 'ssl') {
            $connectHost = 'ssl://' . $host;
        }

        $socket = @fsockopen($connectHost, $port, $errno, $errstr, 30);

        if (!$socket) {
            $details = trim(($errstr ?? '') . " (código {$errno})");
            throw new \Exception("Não foi possível conectar ao servidor SMTP" . ($details ? ": {$details}" : '.'));
        }

        // Ler resposta inicial
        $this->getSmtpResponse($socket);

        // EHLO
        $this->sendSmtpCommand($socket, "EHLO " . gethostname());
        
        // STARTTLS se necessário
        if ($encryption === 'tls') {
            $this->sendSmtpCommand($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendSmtpCommand($socket, "EHLO " . gethostname());
        }

        // AUTH LOGIN
        $this->sendSmtpCommand($socket, "AUTH LOGIN");
        $this->sendSmtpCommand($socket, base64_encode($username));
        $this->sendSmtpCommand($socket, base64_encode($password));

        // MAIL FROM
        $this->sendSmtpCommand($socket, "MAIL FROM:<{$fromAddress}>");

        // RCPT TO
        $this->sendSmtpCommand($socket, "RCPT TO:<{$to}>");

        // DATA
        $this->sendSmtpCommand($socket, "DATA");

        // Enviar mensagem
        $message = "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= implode("\r\n", $headers) . "\r\n\r\n";
        $message .= $body . "\r\n.";
        
        $this->sendSmtpCommand($socket, $message);

        // QUIT
        $this->sendSmtpCommand($socket, "QUIT");
        fclose($socket);

        return true;
    }

    protected function sendSmtpCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->getSmtpResponse($socket);
    }

    protected function getSmtpResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    protected function sendMail(string $to, string $subject, string $body, bool $isHtml): bool
    {
        $fromAddress = $this->config['from_address'] ?? 'noreply@freelancepro.com';
        $fromName = $this->config['from_name'] ?? 'FreelancePro';

        $headers = [];
        $headers[] = "From: {$fromName} <{$fromAddress}>";
        $headers[] = "Reply-To: {$fromAddress}";
        $headers[] = "MIME-Version: 1.0";
        
        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public function sendTemplate(string $to, string $subject, string $template, array $data = []): bool
    {
        $templatePath = ROOT_PATH . "/app/Views/emails/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template de email não encontrado: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        $body = ob_get_clean();

        return $this->send($to, $subject, $body, true);
    }
}
