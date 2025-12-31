<?php
/**
 * BabeStand - Classe Mailer
 * Envio de emails via SMTP (Mailcow)
 * Templates unificados em /templates/emails/
 */

namespace App\Services;

class Mailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = MAIL_PASSWORD;
        $this->encryption = MAIL_ENCRYPTION;
        $this->fromAddress = MAIL_FROM_ADDRESS;
        $this->fromName = MAIL_FROM_NAME;
    }

    public function sendLoginToken(string $to, string $name, string $token, string $code = null): bool
    {
        return $this->sendTemplate($to, 'login_token', [
            'name' => $name,
            'verify_url' => SITE_URL . '/verify-login.php?token=' . urlencode($token),
            'verification_code' => $code,
            'expiry_minutes' => TOKEN_EXPIRY_MINUTES,
            'ip_address' => getClientIp(),
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendVerificationEmail(string $to, string $name, string $token): bool
    {
        return $this->sendTemplate($to, 'verification_email', [
            'name' => $name,
            'verify_url' => SITE_URL . '/verificar-email.php?token=' . urlencode($token),
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendPasswordResetEmail(string $to, string $name, string $token): bool
    {
        return $this->sendTemplate($to, 'password_reset', [
            'name' => $name,
            'reset_url' => SITE_URL . '/redefinir-password.php?token=' . urlencode($token),
            'expiry_hours' => 1,
            'ip_address' => getClientIp(),
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendTestDriveConfirmation(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'test_drive_confirmation', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'date' => $data['date'],
            'time' => $data['time'],
            'address' => $data['address'] ?? 'BabeStand - Porto'
        ]);
    }

    public function sendContactNotification(array $data): bool
    {
        return $this->sendTemplate('admin@babestand.fsociety.pt', 'contact_notification', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? 'Não indicado',
            'subject' => $data['subject'],
            'message' => $data['message'],
            'vehicle' => $data['vehicle'] ?? null,
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendAccountLockedEmail(string $to, string $name, string $unlockToken): bool
    {
        $originalFrom = $this->fromAddress;
        $this->fromAddress = 'noreply@babestand.fsociety.pt';
        
        $result = $this->sendTemplate($to, 'account_locked', [
            'name' => $name,
            'unlock_url' => SITE_URL . '/desbloquear-conta.php?token=' . urlencode($unlockToken),
            'ip_address' => getClientIp(),
            'date' => date('d/m/Y H:i'),
            'support_email' => 'suporte@babestand.fsociety.pt'
        ]);
        
        $this->fromAddress = $originalFrom;
        return $result;
    }

    public function sendContactConfirmation(string $to, string $name, string $subject): bool
    {
        return $this->sendTemplate($to, 'contact_confirmation', [
            'name' => $name,
            'subject' => $subject,
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendTestDriveStatusUpdate(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'test_drive_status', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'date' => $data['date'],
            'time' => $data['time'],
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? ''
        ]);
    }

    public function sendAdminReply(string $to, string $name, string $originalMessage, string $reply): bool
    {
        return $this->sendTemplate($to, 'admin_reply', [
            'name' => $name,
            'original_message' => $originalMessage,
            'reply' => $reply
        ]);
    }

    public function sendEmailChangeConfirmation(string $to, string $name, string $confirmUrl): bool
    {
        return $this->sendTemplate($to, 'email_change', [
            'name' => $name,
            'confirm_url' => $confirmUrl,
            'ip_address' => getClientIp(),
            'date' => date('d/m/Y H:i')
        ]);
    }

    public function sendNewTestDriveAdmin(array $data): bool
    {
        return $this->sendTemplate('admin@babestand.fsociety.pt', 'new_test_drive_admin', [
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'] ?? null,
            'vehicle' => $data['vehicle'],
            'date' => $data['date'],
            'time' => $data['time'],
            'notes' => $data['notes'] ?? ''
        ]);
    }

    public function sendFavoriteSold(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'favorite_sold', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'similar_url' => SITE_URL . '/veiculos.php',
            'favorites_url' => SITE_URL . '/conta/favoritos.php'
        ]);
    }

    public function sendMaintenanceReminder(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'maintenance_reminder', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'maintenance_type' => $data['maintenance_type'],
            'due_date' => $data['due_date'] ?? null,
            'due_mileage' => $data['due_mileage'] ?? null,
            'my_vehicles_url' => SITE_URL . '/conta/meus-veiculos.php'
        ]);
    }

    public function sendWaitingListAvailable(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'waiting_list_available', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'vehicle_id' => $data['vehicle_id'],
            'vehicle_url' => SITE_URL . '/veiculo.php?id=' . $data['vehicle_id'],
            'test_drive_url' => SITE_URL . '/agendar-test-drive.php?veiculo=' . $data['vehicle_id']
        ]);
    }

    public function sendSellTradeStatus(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'sell_trade_status', [
            'name' => $name,
            'vehicle' => $data['vehicle'],
            'request_type' => $data['request_type'],
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? '',
            'my_vehicles_url' => SITE_URL . '/conta/meus-veiculos.php'
        ]);
    }

    public function sendVehiclePurchased(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'vehicle_purchased', [
            'name' => $name,
            'vehicle_name' => $data['vehicle_name'],
            'vehicle_year' => $data['vehicle_year'] ?? null,
            'mileage' => $data['mileage'] ?? null,
            'sold_price' => $data['sold_price'] ?? null
        ]);
    }

    public function sendNegotiationUpdate(string $to, string $name, array $data): bool
    {
        return $this->sendTemplate($to, 'negotiation_update', [
            'name' => $name,
            'vehicle_name' => $data['vehicle_name'],
            'vehicle_id' => $data['vehicle_id'],
            'type' => $data['type'],
            'action' => $data['action'],
            'offer_value' => $data['offer_value'] ?? null,
            'final_price' => $data['final_price'] ?? null,
            'admin_message' => $data['admin_message'] ?? ''
        ]);
    }

    private function sendTemplate(string $to, string $templateName, array $data): bool
    {
        $html = $this->getTemplate($templateName, $data);
        $subject = $this->extractSubject($html, $templateName);
        return $this->send($to, $subject, $html);
    }

    private function extractSubject(string $html, string $templateName): string
    {
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8') . ' - ' . SITE_NAME;
        }
        $subjects = [
            'login_token' => 'Acesso à sua conta',
            'verification_email' => 'Ative a sua conta',
            'password_reset' => 'Recuperar Password',
            'test_drive_confirmation' => 'Test Drive Confirmado',
            'contact_notification' => 'Nova mensagem de contacto',
            'account_locked' => 'ALERTA: Conta Suspensa',
            'contact_confirmation' => 'Recebemos a sua mensagem',
            'test_drive_status' => 'Atualização do Test Drive',
            'admin_reply' => 'Resposta à sua mensagem',
            'email_change' => 'Confirmar alteração de email',
            'new_test_drive_admin' => 'Novo pedido de Test Drive',
            'favorite_sold' => 'Um veículo dos seus favoritos foi vendido',
            'maintenance_reminder' => 'Lembrete de Manutenção',
            'waiting_list_available' => 'Veículo disponível',
            'sell_trade_status' => 'Atualização do seu pedido',
            'vehicle_purchased' => 'Parabéns pela sua compra',
            'negotiation_update' => 'Atualização da sua negociação'
        ];
        return ($subjects[$templateName] ?? 'Notificação') . ' - ' . SITE_NAME;
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (empty($textBody)) {
            $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        }
        $boundary = md5(time());
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: ' . $this->fromName . ' <' . $this->fromAddress . '>',
            'Reply-To: ' . $this->fromAddress,
            'X-Mailer: BabeStand/1.0'
        ];
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $textBody . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--{$boundary}--";
        try {
            return $this->sendViaSMTP($to, $subject, $body, $headers);
        } catch (\Exception $e) {
            if (DEBUG_MODE) {
                error_log("SMTP Error: " . $e->getMessage());
            }
            return @mail($to, $subject, $body, implode("\r\n", $headers));
        }
    }

    private function sendViaSMTP(string $to, string $subject, string $body, array $headers): bool
    {
        $socket = @fsockopen(
            ($this->encryption === 'tls' ? '' : 'ssl://') . $this->host,
            $this->port, $errno, $errstr, 30
        );
        if (!$socket) {
            throw new \Exception("Could not connect to SMTP server: $errstr ($errno)");
        }
        $this->getResponse($socket);
        $this->sendCommand($socket, "EHLO " . SITE_DOMAIN);
        if ($this->encryption === 'tls') {
            $this->sendCommand($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand($socket, "EHLO " . SITE_DOMAIN);
        }
        $this->sendCommand($socket, "AUTH LOGIN");
        $this->sendCommand($socket, base64_encode($this->username));
        $this->sendCommand($socket, base64_encode($this->password));
        $this->sendCommand($socket, "MAIL FROM:<{$this->fromAddress}>");
        $this->sendCommand($socket, "RCPT TO:<{$to}>");
        $this->sendCommand($socket, "DATA");
        $message = "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= implode("\r\n", $headers) . "\r\n";
        $message .= "\r\n{$body}\r\n.";
        $this->sendCommand($socket, $message);
        $this->sendCommand($socket, "QUIT");
        fclose($socket);
        return true;
    }

    private function sendCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->getResponse($socket);
    }

    private function getResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    private function getTemplate(string $name, array $data): string
    {
        $templatePath = __DIR__ . '/../../templates/emails/' . $name . '.php';
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template not found: {$name}");
        }
        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
