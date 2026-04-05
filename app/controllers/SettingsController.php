<?php

class SettingsController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        $userId = $_SESSION['user_id'];

        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get or create preferences
        $stmt = $this->db->prepare('SELECT * FROM user_preferences WHERE user_id = ?');
        $stmt->execute([$userId]);
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preferences) {
            $this->db->prepare('INSERT INTO user_preferences (user_id) VALUES (?)')
                     ->execute([$userId]);
            $stmt = $this->db->prepare('SELECT * FROM user_preferences WHERE user_id = ?');
            $stmt->execute([$userId]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $pageTitle = 'Settings – Nexo';
        require_once __DIR__ . '/../views/settings/index.php';
    }

    public function updateAccount() {
        $userId          = $_SESSION['user_id'];
        $email           = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: index.php?url=settings');
            exit;
        }

        try {
            // Check email uniqueness
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'That email is already in use by another account.';
                header('Location: index.php?url=settings');
                exit;
            }

            $this->db->prepare('UPDATE users SET email = ? WHERE id = ?')
                     ->execute([$email, $userId]);

            // Password change
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $_SESSION['error'] = 'Current password is required to set a new password.';
                    header('Location: index.php?url=settings');
                    exit;
                }

                $stmt = $this->db->prepare('SELECT password FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!password_verify($currentPassword, $row['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect.';
                    header('Location: index.php?url=settings');
                    exit;
                }

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New passwords do not match.';
                    header('Location: index.php?url=settings');
                    exit;
                }

                if (strlen($newPassword) < 8) {
                    $_SESSION['error'] = 'New password must be at least 8 characters.';
                    header('Location: index.php?url=settings');
                    exit;
                }

                $this->db->prepare('UPDATE users SET password = ? WHERE id = ?')
                         ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            }

            $_SESSION['toast_success'] = 'Account settings saved.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update settings. Please try again.';
        }

        header('Location: index.php?url=settings');
        exit;
    }

    public function updatePreferences() {
        $userId = $_SESSION['user_id'];

        $darkMode              = in_array($_POST['dark_mode']           ?? '0', ['on', '1', 1], true) ? 1 : 0;
        $emailNotifications    = in_array($_POST['email_notifications'] ?? '0', ['on', '1', 1], true) ? 1 : 0;
        $pushNotifications     = in_array($_POST['push_notifications']  ?? '0', ['on', '1', 1], true) ? 1 : 0;
        $friendRequestsPrivacy = $_POST['friend_requests_privacy'] ?? 'everyone';
        $postPrivacy           = $_POST['post_privacy'] ?? 'public';

        try {
            $this->db->prepare('
                UPDATE user_preferences
                SET dark_mode = ?, email_notifications = ?, push_notifications = ?,
                    friend_requests_privacy = ?, post_privacy = ?
                WHERE user_id = ?
            ')->execute([$darkMode, $emailNotifications, $pushNotifications,
                         $friendRequestsPrivacy, $postPrivacy, $userId]);

            $_SESSION['dark_mode']     = $darkMode;
            $_SESSION['toast_success'] = 'Preferences saved.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to save preferences.';
        }

        header('Location: index.php?url=settings');
        exit;
    }

    public function toggleDarkMode() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $stmt = $this->db->prepare('SELECT dark_mode FROM user_preferences WHERE user_id = ?');
        $stmt->execute([$userId]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC);

        $newValue = $pref ? (int)!$pref['dark_mode'] : 1;

        $this->db->prepare('
            INSERT INTO user_preferences (user_id, dark_mode)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE dark_mode = ?
        ')->execute([$userId, $newValue, $newValue]);

        $_SESSION['dark_mode'] = $newValue;

        echo json_encode(['success' => true, 'dark_mode' => (bool)$newValue]);
        exit;
    }
}
