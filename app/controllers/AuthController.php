<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $userModel;

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
        $identifier = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: index.php?url=login');
            exit;
        }

        // Strip leading @ if user typed it
        $identifier = ltrim($identifier, '@');

        // Rate limit check
        if (!Security::checkRateLimit($identifier)) {
            $_SESSION['error'] = 'Too many login attempts. Please try again in 15 minutes.';
            header('Location: index.php?url=login');
            exit;
        }

        // Try finding by email first, then by username
        $user = $this->userModel->findByEmail($identifier);
        if (!$user) {
            $user = $this->userModel->findByUsername($identifier);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            Security::incrementAttempts($identifier);
            $_SESSION['error'] = 'Invalid username/email or password.';
            header('Location: index.php?url=login');
            exit;
        }

        Security::clearAttempts($identifier);

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
        $email    = trim($_POST['email'] ?? '');
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

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            try {
                $db = Database::getInstance()->getConnection();

                // Remove any previous tokens for this email
                $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

                // Generate a secure random token
                $token = bin2hex(random_bytes(32));
                $db->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)')->execute([$email, $token]);

                // Build the reset URL
                if (defined('APP_BASE_URL') && APP_BASE_URL !== '') {
                    $baseUrl = APP_BASE_URL;
                } else {
                    $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST']
                             . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                }
                $resetUrl = $baseUrl . '/index.php?url=reset-password&token=' . urlencode($token);

                // Send the reset email
                require_once __DIR__ . '/../../config/mail.php';
                require_once __DIR__ . '/../../lib/Mailer.php';
                $mailer = new Mailer(MAIL_ADDRESS, MAIL_PASSWORD, MAIL_FROM_NAME);
                $body   = '<p>Hello,</p>'
                        . '<p>We received a request to reset the password for your Nexo account. '
                        . 'Click the link below to set a new password. This link is valid for <strong>1 hour</strong>.</p>'
                        . '<p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '</a></p>'
                        . '<p>If you did not request a password reset, you can safely ignore this email.</p>'
                        . '<p>– The Nexo Team</p>';
                $mailer->send($email, 'Reset your Nexo password', $body);
            } catch (\Throwable $e) {
                error_log('forgotPassword error: ' . $e->getMessage());
            }
        }

        // Always show the same message to prevent email enumeration
        $_SESSION['toast_success'] = 'If that email exists, a reset link has been sent.';
        header('Location: index.php?url=login');
        exit;
    }

    public function showResetPassword(): void {
        $token      = trim($_GET['token'] ?? '');
        $tokenValid = false;

        if ($token !== '') {
            $tokenValid = $this->findValidResetToken($token) !== null;
        }

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

            $db = Database::getInstance()->getConnection();

            // Update the user's password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, $row['email']]);

            // Delete the consumed token
            $db->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);

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
                'SELECT email FROM password_resets WHERE token = ? AND created_at >= NOW() - INTERVAL 1 HOUR'
            );
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('findValidResetToken error: ' . $e->getMessage());
            return null;
        }
    }
}
