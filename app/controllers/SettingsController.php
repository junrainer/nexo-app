<?php

class SettingsController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show settings page
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Get user data
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get preferences
        $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create default preferences if not exist
        if (!$preferences) {
            $stmt = $this->db->prepare("
                INSERT INTO user_preferences (user_id) VALUES (?)
            ");
            $stmt->execute([$userId]);
            
            $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $pageTitle = 'Settings | Nexo';
        require_once __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Update account settings
     */
    public function updateAccount() {
        $userId = $_SESSION['user_id'];
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email address';
            header('Location: index.php?url=settings');
            exit;
        }
        
        try {
            // Check if email is already taken by another user
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already in use';
                header('Location: index.php?url=settings');
                exit;
            }
            
            // Update email
            $stmt = $this->db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $userId]);
            
            // If changing password
            if ($newPassword) {
                // Verify current password
                $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect';
                    header('Location: index.php?url=settings');
                    exit;
                }
                
                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New passwords do not match';
                    header('Location: index.php?url=settings');
                    exit;
                }
                
                if (strlen($newPassword) < 6) {
                    $_SESSION['error'] = 'Password must be at least 6 characters';
                    header('Location: index.php?url=settings');
                    exit;
                }
                
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
            }
            
            $_SESSION['success'] = 'Account settings updated successfully';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update settings';
        }
        
        header('Location: index.php?url=settings');
        exit;
    }

    /**
     * Update preferences
     */
    public function updatePreferences() {
        $userId = $_SESSION['user_id'];
        
        $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;
        $friendRequestsPrivacy = $_POST['friend_requests_privacy'] ?? 'everyone';
        $postPrivacy = $_POST['post_privacy'] ?? 'public';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE user_preferences 
                SET dark_mode = ?, 
                    email_notifications = ?,
                    push_notifications = ?,
                    friend_requests_privacy = ?,
                    post_privacy = ?
                WHERE user_id = ?
            ");
            $stmt->execute([
                $darkMode,
                $emailNotifications,
                $pushNotifications,
                $friendRequestsPrivacy,
                $postPrivacy,
                $userId
            ]);
            
            // Update session dark mode
            $_SESSION['dark_mode'] = $darkMode;
            
            $_SESSION['success'] = 'Preferences updated successfully';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update preferences';
        }
        
        header('Location: index.php?url=settings');
        exit;
    }

    /**
     * Toggle dark mode (AJAX)
     */
    public function toggleDarkMode() {
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'];
        
        // Get current dark mode value
        $stmt = $this->db->prepare("SELECT dark_mode FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newValue = $pref ? !$pref['dark_mode'] : true;
        
        // Update
        $stmt = $this->db->prepare("
            INSERT INTO user_preferences (user_id, dark_mode) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE dark_mode = ?
        ");
        $stmt->execute([$userId, $newValue, $newValue]);
        
        // Update session
        $_SESSION['dark_mode'] = $newValue;
        
        echo json_encode(['success' => true, 'dark_mode' => $newValue]);
        exit;
    }
}
