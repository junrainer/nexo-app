<?php
/**
 * Nexo – Security helpers
 * - CSRF protection for all POST forms and AJAX calls
 * - Login rate limiting (per identifier + IP)
 * - Session hardening
 */
class Security {

    // ── CSRF Protection ────────────────────────────────────────

    public static function generateToken(): string {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateToken(string $token = ''): bool {
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        if ($sessionToken === '') return false;
        $formToken = $token !== '' ? $token : ($_POST['_token'] ?? '');
        return hash_equals($sessionToken, $formToken);
    }

    public static function field(): string {
        return '<input type="hidden" name="_token" value="'
             . htmlspecialchars(self::generateToken(), ENT_QUOTES) . '">';
    }

    public static function meta(): string {
        return '<meta name="csrf-token" content="'
             . htmlspecialchars(self::generateToken(), ENT_QUOTES) . '">';
    }

    // ── Login Rate Limiting ────────────────────────────────────

    private static function rlKey(string $identifier): string {
        return '_rl_' . md5($identifier . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public static function checkRateLimit(string $identifier, int $maxAttempts = 5, int $decaySeconds = 900): bool {
        $key  = self::rlKey($identifier);
        $data = $_SESSION[$key] ?? ['n' => 0, 't' => time()];

        if (time() - $data['t'] >= $decaySeconds) {
            $_SESSION[$key] = ['n' => 0, 't' => time()];
            return true;
        }

        return $data['n'] < $maxAttempts;
    }

    public static function incrementAttempts(string $identifier): void {
        $key  = self::rlKey($identifier);
        $data = $_SESSION[$key] ?? ['n' => 0, 't' => time()];
        $data['n']++;
        $_SESSION[$key] = $data;
    }

    public static function clearAttempts(string $identifier): void {
        unset($_SESSION[self::rlKey($identifier)]);
    }

    // ── Session Hardening ──────────────────────────────────────

    public static function hardenSession(): void {
        // Regenerate ID periodically to prevent fixation
        if (empty($_SESSION['_sess_init'])) {
            session_regenerate_id(true);
            $_SESSION['_sess_init'] = true;
            $_SESSION['_sess_ip']   = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['_sess_ua']   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
    }
}
