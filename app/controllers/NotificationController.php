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
