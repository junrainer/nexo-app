<?php
/**
 * Nexo – Lightweight Gmail SMTP Mailer
 * Sends email via Gmail using STARTTLS on port 587.
 *
 * Setup:
 *  1. Enable "2-Step Verification" in your Google account.
 *  2. Generate an App Password at https://myaccount.google.com/apppasswords
 *  3. Put your Gmail address and App Password in config/mail.php
 */
class Mailer {
    private string $host     = 'smtp.gmail.com';
    private int    $port     = 587;
    private string $username;
    private string $password;
    private string $fromName;

    public function __construct(string $username, string $password, string $fromName = 'Nexo') {
        $this->username = $username;
        $this->password = $password;
        $this->fromName = $fromName;
    }

    /**
     * Send an email.
     *
     * @param string $to      Recipient email address
     * @param string $subject Email subject
     * @param string $body    HTML email body
     * @return bool           True on success, false on failure
     */
    public function send(string $to, string $subject, string $body): bool {
        $errno   = 0;
        $errstr  = '';
        $socket  = @fsockopen('tcp://' . $this->host, $this->port, $errno, $errstr, 15);
        if (!$socket) {
            error_log("Mailer: Could not connect to {$this->host}:{$this->port} — $errstr ($errno)");
            return false;
        }

        try {
            // Server greeting
            $this->expect($socket, '220');

            // EHLO
            $this->cmd($socket, 'EHLO nexo.app');
            $this->expect($socket, '250');

            // STARTTLS
            $this->cmd($socket, 'STARTTLS');
            $this->expect($socket, '220');

            // Upgrade socket to TLS (allow best available version)
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)) {
                throw new \RuntimeException('TLS handshake failed');
            }

            // EHLO again after TLS
            $this->cmd($socket, 'EHLO nexo.app');
            $this->expect($socket, '250');

            // AUTH LOGIN
            $this->cmd($socket, 'AUTH LOGIN');
            $this->expect($socket, '334');
            $this->cmd($socket, base64_encode($this->username));
            $this->expect($socket, '334');
            $this->cmd($socket, base64_encode($this->password));
            $this->expect($socket, '235');

            // Envelope
            $this->cmd($socket, "MAIL FROM:<{$this->username}>");
            $this->expect($socket, '250');
            $this->cmd($socket, "RCPT TO:<{$to}>");
            $this->expect($socket, '250');

            // Headers + body
            $this->cmd($socket, 'DATA');
            $this->expect($socket, '354');

            $headers  = "From: {$this->fromName} <{$this->username}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: Nexo/1.0\r\n";

            fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
            $this->expect($socket, '250');

            // Quit
            $this->cmd($socket, 'QUIT');
        } catch (\Throwable $e) {
            error_log('Mailer error: ' . $e->getMessage());
            fclose($socket);
            return false;
        }

        fclose($socket);
        return true;
    }

    // ── Helpers ────────────────────────────────────────

    private function cmd($socket, string $command): void {
        fwrite($socket, $command . "\r\n");
    }

    private function read($socket): string {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            // Last line of multi-line response has a space after the code
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    private function expect($socket, string $code): string {
        $response = $this->read($socket);
        if (strncmp(ltrim($response), $code, 3) !== 0) {
            throw new \RuntimeException("SMTP expected $code, got: $response");
        }
        return $response;
    }
}
