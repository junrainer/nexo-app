<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../lib/Mailer.php';
require_once __DIR__ . '/../../lib/Security.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $userModel;
    private PDO       $db;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->db        = Database::getInstance()->getConnection();
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

        // Rate limiting: max 5 failed attempts per 15 minutes
        if (!Security::checkRateLimit($identifier)) {
            $wait = ceil(Security::lockoutSeconds($identifier) / 60);
            $_SESSION['error'] = "Too many failed attempts. Please wait {$wait} minute(s) before trying again.";
            header('Location: index.php?url=login');
            exit;
        }

        // Support login by email OR username
        $user = $this->userModel->findByEmail($identifier);
        if (!$user) {
            $user = $this->userModel->findByUsername($identifier);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            Security::incrementAttempts($identifier);
            $_SESSION['error'] = 'Invalid email/username or password.';
            header('Location: index.php?url=login');
            exit;
        }

        // Successful login – clear rate limit and regenerate session
        Security::clearAttempts($identifier);
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['profile_image'] = $user['profile_image'];
        $_SESSION['toast_success'] = 'Welcome back, ' . $user['full_name'] . '!';
        header('Location: index.php?url=feed');
        exit;
    }

    public function register(): void {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: index.php?url=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: index.php?url=register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email is already taken.';
            header('Location: index.php?url=register');
            exit;
        }

        if ($this->userModel->findByUsername($username)) {
            $_SESSION['error'] = 'Username is already taken.';
            header('Location: index.php?url=register');
            exit;
        }

        // Optional profile fields
        $mobile = trim($_POST['mobile'] ?? '');
        $mobile = $mobile !== '' ? $mobile : null;

        $birthMonth = trim($_POST['birth_month'] ?? '');
        $birthDay   = (int) ($_POST['birth_day']   ?? 0);
        $birthYear  = (int) ($_POST['birth_year']  ?? 0);
        $birthday   = null;
        if ($birthMonth !== '' && $birthDay > 0 && $birthYear > 0) {
            $monthNum = (int) date('n', strtotime("1 {$birthMonth} 2000"));
            if ($monthNum > 0 && checkdate($monthNum, $birthDay, $birthYear)) {
                $birthday = sprintf('%04d-%02d-%02d', $birthYear, $monthNum, $birthDay);
            }
        }

        $gender = trim($_POST['gender'] ?? '');
        $gender = $gender !== '' ? $gender : null;

        $fullName = $firstName . ' ' . $lastName;

        $profileImage = 'default.png';
        if (!empty($_FILES['profile_image']['name'])) {
            $uploaded = $this->handleImageUpload($_FILES['profile_image']);
            if ($uploaded) {
                $profileImage = $uploaded;
            } else {
                $_SESSION['warning'] = 'Profile picture could not be uploaded (invalid format or file too large). A default avatar was used.';
            }
        }

        $id = $this->userModel->create($username, $email, $password, $fullName, $mobile, $birthday, $gender, $profileImage);

        $_SESSION['user_id']       = $id;
        $_SESSION['username']      = $username;
        $_SESSION['full_name']     = $fullName;
        $_SESSION['profile_image'] = $profileImage;
        $_SESSION['success']       = 'Account created! Welcome to Nexo.';
        header('Location: index.php?url=feed');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php?url=login');
        exit;
    }

    // ── Forgot / Reset Password ───────────────────────

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

        // Always show the same message to prevent email enumeration
        $_SESSION['success'] = 'If that email is registered, a reset link has been sent.';

        if ($user) {
            // Delete any existing token for this email
            $stmt = $this->db->prepare('DELETE FROM password_resets WHERE email = ?');
            $stmt->execute([$email]);

            // Generate secure token
            $token = bin2hex(random_bytes(32));

            // Store token (expires in 1 hour via application logic)
            $stmt = $this->db->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)');
            $stmt->execute([$email, $token]);

            // Build reset URL – use the configured base URL when available;
            // otherwise derive it from SERVER variables and strip anything
            // that looks like a Host-header injection attempt.
            if (defined('APP_BASE_URL') && APP_BASE_URL !== '') {
                $base = rtrim(APP_BASE_URL, '/');
            } else {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                // Allow only safe hostname characters (letters, digits, dots, hyphens, brackets for IPv6, port).
                $rawHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $host    = preg_replace('/[^a-zA-Z0-9.\-\[\]:_]/', '', $rawHost);
                $path    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                $base    = "{$scheme}://{$host}{$path}";
            }
            $resetUrl = "{$base}/index.php?url=reset-password&token={$token}";

            // Send email
            $subject = 'Reset your Nexo password';
            $body    = $this->buildResetEmail($user['full_name'], $resetUrl);

            $mailer = new Mailer(MAIL_ADDRESS, MAIL_PASSWORD, MAIL_FROM_NAME);
            $sent   = $mailer->send($email, $subject, $body);

            if (!$sent) {
                error_log("Mailer: Failed to send password reset email to {$email}");
            }
        }

        header('Location: index.php?url=forgot-password');
        exit;
    }

    public function showResetPassword(): void {
        $token      = trim($_GET['token'] ?? '');
        $tokenValid = false;

        if (!empty($token)) {
            $stmt = $this->db->prepare(
                'SELECT * FROM password_resets WHERE token = ?
                 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 LIMIT 1'
            );
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            if ($row) {
                $tokenValid = true;
            }
        }

        require __DIR__ . '/../views/auth/reset_password.php';
    }

    public function resetPassword(): void {
        $token           = trim($_POST['token'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password)) {
            $_SESSION['error'] = 'Invalid request.';
            header('Location: index.php?url=login');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header("Location: index.php?url=reset-password&token=" . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match.';
            header("Location: index.php?url=reset-password&token=" . urlencode($token));
            exit;
        }

        // Validate token (1-hour window)
        $stmt = $this->db->prepare(
            'SELECT * FROM password_resets WHERE token = ?
             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION['error'] = 'This reset link is invalid or has expired.';
            header('Location: index.php?url=forgot-password');
            exit;
        }

        // Update the user's password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $this->db->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$hashed, $row['email']]);

        // Delete used token
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$row['email']]);

        $_SESSION['success'] = 'Password updated! You can now sign in with your new password.';
        header('Location: index.php?url=login');
        exit;
    }

    // ── Private helpers ───────────────────────────────

    private function handleImageUpload(array $file): string|false {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $maxSize = 2 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            return false;
        }

        // Verify actual file content, not the browser-supplied type
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!isset($mimeToExt[$mimeType])) {
            return false;
        }

        $ext      = $mimeToExt[$mimeType];
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $dir      = __DIR__ . '/../../public/assets/uploads/';

        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        $dest = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return $filename;
    }

    private function buildResetEmail(string $name, string $resetUrl): string {
        $safeUrl  = htmlspecialchars($resetUrl, ENT_QUOTES);
        $safeName = htmlspecialchars($name, ENT_QUOTES);
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Inter, Arial, sans-serif; background: #1f1f1f; color: #f2f2f2; margin: 0; padding: 20px; }
    .card { background: #262626; border-radius: 12px; max-width: 480px; margin: 0 auto; padding: 32px 28px; }
    .brand { font-size: 24px; font-weight: 800; color: hsl(262,80%,65%); margin-bottom: 24px; }
    h1 { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    p { font-size: 14px; color: #b3b3b3; line-height: 1.6; margin: 0 0 20px; }
    .btn { display: inline-block; background: hsl(262,80%,55%); color: #fff; padding: 14px 28px;
           border-radius: 8px; font-weight: 600; font-size: 15px; text-decoration: none; }
    .note { font-size: 12px; color: #8c8c8c; margin-top: 20px; }
    .url  { word-break: break-all; font-size: 12px; color: #8c8c8c; margin-top: 8px; }
  </style>
</head>
<body>
  <div class="card">
    <div class="brand">Nexo</div>
    <h1>Password Reset Request</h1>
    <p>Hi {$safeName}, we received a request to reset your Nexo password. Click the button below to choose a new one.</p>
    <a href="{$safeUrl}" class="btn">Reset Password</a>
    <p class="note">This link expires in <strong>1 hour</strong>. If you did not request this, you can safely ignore this email.</p>
    <p class="url">{$safeUrl}</p>
  </div>
</body>
</html>
HTML;
    }
}