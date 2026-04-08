<?php
/**
 * Nexo – Mailer
 *
 * Tries Gmail SMTP (STARTTLS, port 587) first.
 * If the connection fails (e.g. on InfinityFree which blocks outbound SMTP),
 * falls back to PHP's built-in mail() function.
 *
 * InfinityFree note:
 *  - Outbound SMTP (ports 25, 465, 587) is blocked.
 *  - PHP mail() is available but delivery depends on their relay.
 *  - For reliable delivery on InfinityFree, consider a transactional email
 *    service that provides an HTTP API (e.g. Brevo, Mailgun, SendGrid).
 *    You can call their API with file_get_contents() + stream context.
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
     * @param string $body    HTML body
     * @param array  $inlineAttachments Inline attachments (cid, mime, filename, content)
     * @return bool
     */
    public function send(string $to, string $subject, string $body, array $inlineAttachments = []): bool {
        // Try SMTP (works on XAMPP / VPS, blocked on InfinityFree free tier)
        if ($this->sendSmtp($to, $subject, $body, $inlineAttachments)) {
            return true;
        }

        // Fallback: PHP mail() — works on most shared hosting including InfinityFree
        return $this->sendMail($to, $subject, $body, $inlineAttachments);
    }

    // ── SMTP (Gmail STARTTLS) ──────────────────────────────────

    private function sendSmtp(string $to, string $subject, string $body, array $inlineAttachments): bool {
        $errno  = 0;
        $errstr = '';
        $socket = @fsockopen('tcp://' . $this->host, $this->port, $errno, $errstr, 10);
        if (!$socket) {
            return false;
        }

        try {
            $this->expect($socket, '220');

            $this->cmd($socket, 'EHLO nexo.app');
            $this->expect($socket, '250');

            $this->cmd($socket, 'STARTTLS');
            $this->expect($socket, '220');

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)) {
                throw new \RuntimeException('TLS handshake failed');
            }

            $this->cmd($socket, 'EHLO nexo.app');
            $this->expect($socket, '250');

            $this->cmd($socket, 'AUTH LOGIN');
            $this->expect($socket, '334');
            $this->cmd($socket, base64_encode($this->username));
            $this->expect($socket, '334');
            $this->cmd($socket, base64_encode($this->password));
            $this->expect($socket, '235');

            $this->cmd($socket, "MAIL FROM:<{$this->username}>");
            $this->expect($socket, '250');
            $this->cmd($socket, "RCPT TO:<{$to}>");
            $this->expect($socket, '250');

            $this->cmd($socket, 'DATA');
            $this->expect($socket, '354');

            [$contentHeaders, $messageBody] = $this->buildMessage($body, $inlineAttachments);
            $headers  = "Date: " . date('r') . "\r\n";
            $headers .= "Message-ID: <" . time() . "." . bin2hex(random_bytes(8)) . "@nexo.app>\r\n";
            $headers .= "From: {$this->fromName} <{$this->username}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= $contentHeaders;
            $headers .= "X-Mailer: Nexo/1.0\r\n";

            fwrite($socket, $headers . "\r\n" . $messageBody . "\r\n.\r\n");
            $this->expect($socket, '250');

            $this->cmd($socket, 'QUIT');
        } catch (\Throwable $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            fclose($socket);
            return false;
        }

        fclose($socket);
        return true;
    }

    // ── PHP mail() fallback ────────────────────────────────────

    private function sendMail(string $to, string $subject, string $body, array $inlineAttachments): bool {
        [$contentHeaders, $messageBody] = $this->buildMessage($body, $inlineAttachments);
        $headers  = "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <" . time() . "." . bin2hex(random_bytes(8)) . "@nexo.app>\r\n";
        $headers .= "From: {$this->fromName} <{$this->username}>\r\n";
        $headers .= "Reply-To: {$this->username}\r\n";
        $headers .= $contentHeaders;
        $headers .= "X-Mailer: Nexo/1.0\r\n";

        $result = @mail($to, $subject, $messageBody, $headers);
        if (!$result) {
            error_log("Mailer mail() failed sending to {$to}");
        }
        return (bool) $result;
    }

    // ── SMTP helpers ───────────────────────────────────────────

    private function cmd($socket, string $command): void {
        fwrite($socket, $command . "\r\n");
    }

    private function read($socket): string {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
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

    private function sanitizeMime($mime): string {
        if (!is_string($mime)) {
            return 'application/octet-stream';
        }
        $mime = str_replace(["\r", "\n"], '', $mime);
        if (!preg_match('/^[a-z0-9.+-]+\\/[a-z0-9.+-]+$/i', $mime)) {
            return 'application/octet-stream';
        }
        return $mime;
    }

    private function sanitizeFilename($filename): string {
        if (!is_string($filename)) {
            return 'attachment';
        }
        $filename = basename($filename);
        $filename = str_replace(["\r", "\n", '"'], '', $filename);
        return $filename !== '' ? $filename : 'attachment';
    }

    private function buildMessage(string $body, array $inlineAttachments): array {
        if (empty($inlineAttachments)) {
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            return [$headers, $body];
        }

        $boundary = 'nexo_' . bin2hex(random_bytes(12));
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/related; boundary=\"{$boundary}\"\r\n";

        $message  = "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $message .= quoted_printable_encode($body) . "\r\n";

        foreach ($inlineAttachments as $attachment) {
            $cid = $attachment['cid'] ?? '';
            $content = $attachment['content'] ?? null;
            if ($cid === '' || !is_string($content)) {
                continue;
            }
            $mime = $this->sanitizeMime($attachment['mime'] ?? 'application/octet-stream');
            $filename = $this->sanitizeFilename($attachment['filename'] ?? $cid);

            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: {$mime}; name=\"{$filename}\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-ID: <{$cid}>\r\n";
            $message .= "Content-Disposition: inline; filename=\"{$filename}\"\r\n\r\n";
            $message .= chunk_split(base64_encode($content)) . "\r\n";
        }

        $message .= "--{$boundary}--\r\n";
        return [$headers, $message];
    }
}
