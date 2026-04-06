<?php

class NotificationController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all notifications for current user (AJAX endpoint)
     */
    public function getAll() {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT n.*, 
                       u.username as actor_username, 
                       u.full_name as actor_name,
                       u.profile_image as actor_image
                FROM notifications n
                JOIN users u ON n.actor_id = u.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'notifications' => $notifications]);
        } catch (PDOException $e) {
            echo json_encode(['success' => true, 'notifications' => [], 'error' => 'Table not found. Run sql/navbar_features.sql']);
        }
        exit;
    }

    /**
     * Get unread notification count (AJAX endpoint)
     */
    public function getUnreadCount() {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
        } catch (PDOException $e) {
            echo json_encode(['success' => true, 'count' => 0]);
        }
        exit;
    }

    /**
     * Mark notification as read (AJAX endpoint)
     */
    public function markAsRead() {
        header('Content-Type: application/json');
        
        $notificationId = $_POST['notification_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notificationId, $userId]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }

    /**
     * Mark all notifications as read (AJAX endpoint)
     */
    public function markAllAsRead() {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }

    public function bulkUpdate(): void {
        $userId   = (int) ($_SESSION['user_id'] ?? 0);
        $action   = trim((string) ($_POST['bulk_action'] ?? ''));
        $selected = $_POST['selected_notifications'] ?? [];
        $backUrl  = 'index.php?url=notifications/all';

        if ($userId <= 0) {
            header('Location: index.php?url=login');
            exit;
        }

        if (!is_array($selected) || empty($selected)) {
            $_SESSION['error'] = 'Please select at least one notification.';
            header('Location: ' . $backUrl);
            exit;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $selected), fn($id) => $id > 0)));
        if (empty($ids)) {
            $_SESSION['error'] = 'Invalid selection.';
            header('Location: ' . $backUrl);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params       = array_merge([$userId], $ids);

        try {
            if ($action === 'mark_read' || $action === 'mark_unread') {
                $isRead = $action === 'mark_read' ? 1 : 0;
                $stmt = $this->db->prepare("
                    UPDATE notifications
                    SET is_read = ?
                    WHERE user_id = ? AND id IN ($placeholders)
                ");
                $stmt->execute(array_merge([$isRead], $params));
                $_SESSION['success'] = $action === 'mark_read'
                    ? 'Selected notifications marked as read.'
                    : 'Selected notifications marked as unread.';
                header('Location: ' . $backUrl);
                exit;
            }

            if ($action === 'delete') {
                $checkStmt = $this->db->prepare("
                    SELECT COUNT(*) as unread_count
                    FROM notifications
                    WHERE user_id = ? AND id IN ($placeholders) AND is_read = 0
                ");
                $checkStmt->execute($params);
                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $unreadCount = (int) ($row['unread_count'] ?? 0);

                if ($unreadCount > 0) {
                    $_SESSION['error'] = 'Delete is only allowed for notifications that are already read.';
                    header('Location: ' . $backUrl);
                    exit;
                }

                $deleteStmt = $this->db->prepare("
                    DELETE FROM notifications
                    WHERE user_id = ? AND id IN ($placeholders)
                ");
                $deleteStmt->execute($params);
                $_SESSION['success'] = 'Selected notifications deleted.';
                header('Location: ' . $backUrl);
                exit;
            }

            $_SESSION['error'] = 'Invalid action.';
            header('Location: ' . $backUrl);
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Unable to update notifications right now.';
            header('Location: ' . $backUrl);
            exit;
        }
    }

    /**
     * Render the full notifications page
     */
    public function getPage() {
        $userId = $_SESSION['user_id'];
        $notifications = [];

        try {
            $stmt = $this->db->prepare("
                SELECT n.*,
                       u.username as actor_username,
                       u.full_name as actor_name,
                       u.profile_image as actor_image
                FROM notifications n
                JOIN users u ON n.actor_id = u.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table might not exist yet
        }

        $pageTitle = 'Notifications – Nexo';
        require __DIR__ . '/../views/notifications/index.php';
    }

    /**
     * Create notification (helper method)
     */
    public static function create($userId, $type, $actorId, $relatedId, $message) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Don't notify yourself
            if ($userId == $actorId) return;
            
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, actor_id, related_id, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $type, $actorId, $relatedId, $message]);
        } catch (PDOException $e) {
            // Table might not exist, silently fail
        }
    }
}
