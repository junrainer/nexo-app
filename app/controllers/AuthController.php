<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $userModel;
    private const RESET_VERIFY_TTL_SECONDS = 3600;
    private const FORGOT_PASSWORD_COOLDOWN_SECONDS = 60;
    private const FORGOT_PASSWORD_COOLDOWN_KEY = '_forgot_password_cooldown';

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLogin(): void {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister(): void {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        $identifier = ltrim(trim($_POST['username'] ?? ''), '@');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: index.php?url=login');
            exit;
        }

        // Rate limit check
        if (!Security::checkRateLimit($identifier)) {
            $_SESSION['error'] = 'Too many login attempts. Please try again in 15 minutes.';
            header('Location: index.php?url=login');
            exit;
        }

        $user = $this->userModel->findByUsername($identifier);

        // If no user found by username, try treating the identifier as an email
        if (!$user && str_contains($identifier, '@')) {
            $user = $this->userModel->findByEmail($identifier);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            Security::incrementAttempts($identifier);
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: index.php?url=login');
            exit;
        }

        Security::clearAttempts($identifier);

        // Regenerate session ID to bind a fresh session to this authenticated user
        // (prevents session fixation and ensures the browser cookie matches)
        session_regenerate_id(true);

        // Load dark_mode from user_preferences into session
        $darkMode = 1; // default dark
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT dark_mode FROM user_preferences WHERE user_id = ?');
            $stmt->execute([$user['id']]);
            $pref = $stmt->fetch();
            if ($pref !== false) {
                $darkMode = (int)$pref['dark_mode'];
            } else {
                // Create default preferences row
                $db->prepare('INSERT IGNORE INTO user_preferences (user_id, dark_mode) VALUES (?, 1)')
                   ->execute([$user['id']]);
            }
        } catch (PDOException $e) {
            // user_preferences table may not exist yet — ignore
        }

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['profile_image'] = $user['profile_image'];
        $_SESSION['dark_mode']     = $darkMode;

        header('Location: index.php?url=feed');
        exit;
    }

    public function register(): void {
        $fullName = trim($_POST['full_name'] ?? '');
        $username = ltrim(trim($_POST['username'] ?? ''), '@');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $bio      = trim($_POST['bio'] ?? '');

        if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Full name, username, email, and password are required.';
            header('Location: index.php?url=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: index.php?url=register');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
            $_SESSION['error'] = 'Username can only contain letters, numbers, dots, and underscores.';
            header('Location: index.php?url=register');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'That email is already registered.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByUsername($username)) {
            $_SESSION['error'] = 'That username is already taken.';
            header('Location: index.php?url=register');
            exit;
        }

        $image = 'default.png';

        // Handle optional profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024;
            if (in_array($_FILES['profile_image']['type'], $allowed) && $_FILES['profile_image']['size'] <= $maxSize) {
                $ext   = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $fname = uniqid('avatar_', true) . '.' . $ext;
                $dest  = __DIR__ . '/../../public/assets/uploads/' . $fname;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                    $image = $fname;
                }
            }
        }

        $id = $this->userModel->create(
            $username,
            $email,
            $password,
            htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            null, null, null,
            $image
        );

        // Save bio if provided
        if (!empty($bio)) {
            $this->userModel->update(
                $id,
                htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
                $username,
                htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'),
                $image
            );
        }

        // Create default preferences
        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare('INSERT IGNORE INTO user_preferences (user_id, dark_mode) VALUES (?, 1)')
               ->execute([$id]);
        } catch (PDOException $e) { /* ignore */ }

        $_SESSION['user_id']       = $id;
        $_SESSION['username']      = $username;
        $_SESSION['full_name']     = $fullName;
        $_SESSION['profile_image'] = $image;
        $_SESSION['dark_mode']     = 1;
        $_SESSION['toast_success'] = 'Welcome to Nexo, ' . htmlspecialchars($fullName) . '!';

        header('Location: index.php?url=feed');
        exit;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 3600, // expire the cookie in the past
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: index.php?url=login');
        exit;
    }

    public function showForgotPassword(): void {
        require __DIR__ . '/../views/auth/forgot_password.php';
    }

    public function forgotPassword(): void {
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        $cooldownRemaining = $this->getForgotPasswordCooldownRemaining($email);
        if ($cooldownRemaining > 0) {
            $_SESSION['error'] = 'Please wait ' . $cooldownRemaining . ' seconds before requesting another reset email.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->setForgotPasswordCooldown($email);
            $_SESSION['error'] = 'Email not found. Please enter your registered email.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Remove any previous tokens for this email
            $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

            // Generate a secure random token and a 6-digit verification code
            $token = bin2hex(random_bytes(32));
            $code  = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $db->prepare('INSERT INTO password_resets (email, token, code) VALUES (?, ?, ?)')->execute([$email, $token, $code]);

            // Build the verify URL so the user lands directly on the code-entry page
            if (defined('APP_BASE_URL') && APP_BASE_URL !== '') {
                $baseUrl = APP_BASE_URL;
            } else {
                $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $basePath = dirname($_SERVER['SCRIPT_NAME']);
                if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
                    $basePath = '';
                } else {
                    $basePath = rtrim($basePath, '/\\');
                }
                $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath;
            }
            $verifyUrl        = $baseUrl . '/index.php?url=verify-reset&token=' . urlencode($token);
            $verificationCode = $code;

            // Send the reset email with the 6-digit code
            require_once __DIR__ . '/../../config/mail.php';
            require_once __DIR__ . '/../../lib/Mailer.php';
            $mailer = new Mailer(MAIL_ADDRESS, MAIL_PASSWORD, MAIL_FROM_NAME);
            ob_start();
            require __DIR__ . '/../views/auth/email_reset_body.php';
            $body = ob_get_clean();

            if (!$mailer->send($email, 'Your Nexo password reset code', $body)) {
                $this->setForgotPasswordCooldown($email);
                $_SESSION['error'] = 'Unable to send the verification email. Please try again in a moment.';
                header('Location: index.php?url=forgot-password');
                exit;
            }
        } catch (\Throwable $e) {
            error_log('forgotPassword error: ' . $e->getMessage());
            $this->setForgotPasswordCooldown($email);
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        $this->setForgotPasswordCooldown($email);
        $_SESSION['success'] = 'A 6-digit verification code has been sent to your email.';
        header('Location: index.php?url=verify-reset&token=' . urlencode($token));
        exit;
    }

    public function showVerifyReset(): void {
        $token      = trim($_GET['token'] ?? '');
        $tokenValid = false;

        if ($token !== '') {
            $tokenValid = $this->findValidResetToken($token) !== null;
        }

        require __DIR__ . '/../views/auth/verify_reset.php';
    }

    public function verifyReset(): void {
        $token = trim($_POST['token'] ?? '');
        $code  = trim($_POST['code'] ?? '');

        if ($token === '') {
            $_SESSION['error'] = 'This verification link is invalid or has expired.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            $_SESSION['error'] = 'Please enter the 6-digit code from your email.';
            header('Location: index.php?url=verify-reset&token=' . urlencode($token));
            exit;
        }

        $row = $this->findValidResetToken($token);
        if (!$row) {
            $_SESSION['error'] = 'This verification link is invalid or has expired.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        if (!hash_equals($row['code'], $code)) {
            $_SESSION['error'] = 'Incorrect verification code. Please try again.';
            header('Location: index.php?url=verify-reset&token=' . urlencode($token));
            exit;
        }

        if (!isset($_SESSION['password_reset_verified']) || !is_array($_SESSION['password_reset_verified'])) {
            $_SESSION['password_reset_verified'] = [];
        }
        $this->cleanupExpiredVerifications();
        $_SESSION['password_reset_verified'][$token] = time();
        header('Location: index.php?url=reset-password&token=' . urlencode($token));
        exit;
    }

    public function showResetPassword(): void {
        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $_SESSION['error'] = 'Invalid reset link.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        $tokenRow = $this->findValidResetToken($token);
        if (!$tokenRow) {
            $tokenValid = false;
            require __DIR__ . '/../views/auth/reset_password.php';
            return;
        }

        $this->cleanupExpiredVerifications();
        $verifiedAt = $_SESSION['password_reset_verified'][$token] ?? null;
        if ($this->isVerificationExpired($verifiedAt)) {
            $_SESSION['error'] = 'Please verify it’s you before resetting your password.';
            header('Location: index.php?url=verify-reset&token=' . urlencode($token));
            exit;
        }

        $tokenValid = true;
        require __DIR__ . '/../views/auth/reset_password.php';
    }

    public function resetPassword(): void {
        $token    = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = 'Invalid reset link.';
            header('Location: index.php?url=login');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters.';
            header('Location: index.php?url=reset-password&token=' . urlencode($token));
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: index.php?url=reset-password&token=' . urlencode($token));
            exit;
        }

        try {
            $row = $this->findValidResetToken($token);

            if (!$row) {
                $_SESSION['error'] = 'This reset link is invalid or has expired.';
                header('Location: index.php?url=forgot-password');
                exit;
            }

            $this->cleanupExpiredVerifications();
            $verifiedAt = $_SESSION['password_reset_verified'][$token] ?? null;
            if ($this->isVerificationExpired($verifiedAt)) {
                $_SESSION['error'] = 'Please verify it’s you before creating a new password.';
                header('Location: index.php?url=verify-reset&token=' . urlencode($token));
                exit;
            }

            $db = Database::getInstance()->getConnection();

            // Update the user's password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, $row['email']]);

            // Delete the consumed token
            $db->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
            if (isset($_SESSION['password_reset_verified'][$token])) {
                unset($_SESSION['password_reset_verified'][$token]);
            }

            $_SESSION['toast_success'] = 'Password updated! You can now sign in with your new password.';
            header('Location: index.php?url=login');
            exit;
        } catch (\Throwable $e) {
            error_log('resetPassword error: ' . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: index.php?url=forgot-password');
            exit;
        }
    }

    private function findValidResetToken(string $token): ?array {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'SELECT email, code FROM password_resets WHERE token = ? AND created_at >= NOW() - INTERVAL 1 HOUR'
            );
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('findValidResetToken error: ' . $e->getMessage());
            return null;
        }
    }

    private function cleanupExpiredVerifications(): void {
        if (!isset($_SESSION['password_reset_verified']) || !is_array($_SESSION['password_reset_verified'])) {
            return;
        }
        foreach ($_SESSION['password_reset_verified'] as $k => $ts) {
            if ($this->isVerificationExpired($ts)) {
                unset($_SESSION['password_reset_verified'][$k]);
            }
        }
    }

    /**
     * Returns true when verification timestamp is missing/invalid or outside TTL.
     * Non-integer and null values are treated as expired for safety.
     */
    private function isVerificationExpired($verifiedAt): bool {
        return !is_int($verifiedAt) || (time() - $verifiedAt > self::RESET_VERIFY_TTL_SECONDS);
    }

    private function isForgotPasswordCooldownActive(string $email): bool {
        $key = self::FORGOT_PASSWORD_COOLDOWN_KEY . ':' . md5(strtolower($email));
        $last = $_SESSION[$key] ?? null;
        return is_int($last) && (time() - $last < self::FORGOT_PASSWORD_COOLDOWN_SECONDS);
    }

    private function getForgotPasswordCooldownRemaining(string $email): int {
        $key = self::FORGOT_PASSWORD_COOLDOWN_KEY . ':' . md5(strtolower($email));
        $last = $_SESSION[$key] ?? null;
        if (!is_int($last)) {
            return 0;
        }
        $remaining = self::FORGOT_PASSWORD_COOLDOWN_SECONDS - (time() - $last);
        return max(0, $remaining);
    }

    private function setForgotPasswordCooldown(string $email): void {
        $key = self::FORGOT_PASSWORD_COOLDOWN_KEY . ':' . md5(strtolower($email));
        $_SESSION[$key] = time();
    }
}
