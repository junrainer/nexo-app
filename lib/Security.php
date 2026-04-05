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

    /** Renders a hidden form field containing the CSRF token. */
    public static function field(): string {
        return '<input type="hidden" name="_token" value="'
             . htmlspecialchars(self::generateToken(), ENT_QUOTES) . '">';
    }

    /** Renders a <meta> tag for use by JavaScript. */
    public static function meta(): string {
        return '<meta name="csrf-token" content="'
             . htmlspecialchars(self::generateToken(), ENT_QUOTES) . '">';
    }

    // ── Login Rate Limiting ────────────────────────────────────
    // Tracks failed attempts per (identifier + IP) in the session.
    // Allows up to $maxAttempts failures within a $decaySeconds window.

    private static function rlKey(string $identifier): string {
        return '_rl_' . md5($identifier . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public static function checkRateLimit(string $identifier, int $maxAttempts = 5, int $decaySeconds = 900): bool {
        $key  = self::rlKey($identifier);
        $data = $_SESSION[$key] ?? ['n' => 0, 't' => time()];

        // Reset if the window expired
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

    /** Returns the number of seconds until the lockout window resets. */
    public static function lockoutSeconds(string $identifier, int $decaySeconds = 900): int {
        $key  = self::rlKey($identifier);
        $data = $_SESSION[$key] ?? null;
        if (!$data) return 0;
        return max(0, $decaySeconds - (time() - $data['t']));
    }

    // ── Session Hardening ──────────────────────────────────────

    public static function hardenSession(): void {
        // First visit – tie the session to this browser fingerprint
        if (empty($_SESSION['_s_ua'])) {
            session_regenerate_id(true);
            $_SESSION['_s_ua']    = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['_s_start'] = time();
            return;
        }

        // Detect session hijack via changed User-Agent
        if ($_SESSION['_s_ua'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            $userId = $_SESSION['user_id'] ?? null;
            session_destroy();
            session_start();
            if ($userId) {
                // keep user_id so the auth guard redirects to login cleanly
            }
            return;
        }

        // Rotate session ID every 30 minutes
        if (time() - ($_SESSION['_s_start'] ?? 0) > 1800) {
            session_regenerate_id(true);
            $_SESSION['_s_start'] = time();
        }
    }
}
