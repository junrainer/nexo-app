<?php

class MessageController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show messages page
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $activeConversationId = $_GET['c'] ?? null;
        
        $conversations = [];
        $messages = [];
        $activeUser = null;
        
        try {
            // Get all conversations
            $conversations = $this->getConversationsForUser($userId);
            
            // If active conversation specified, get messages
            if ($activeConversationId && is_numeric($activeConversationId)) {
                $messages = $this->getMessagesForConversation($activeConversationId, $userId);
                
                // Get the other user in conversation
                $stmt = $this->db->prepare("
                    SELECT u.*
                    FROM conversations c
                    JOIN users u ON (u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id))
                    WHERE c.id = ? AND (c.user1_id = ? OR c.user2_id = ?)
                ");
                $stmt->execute([$userId, $activeConversationId, $userId, $userId]);
                $activeUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Mark messages as read
                $this->markConversationAsRead($activeConversationId, $userId);
            }
        } catch (PDOException $e) {
            // Tables might not exist
        }
        
        $pageTitle = 'Messages | Nexo';
        require_once __DIR__ . '/../views/messages/index.php';
    }

    /**
     * Get conversations for user
     */
    private function getConversationsForUser($userId) {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.id as other_user_id,
                   u.username as other_username,
                   u.full_name as other_name,
                   u.profile_image as other_image,
                   (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = FALSE) as unread_count
            FROM conversations c
            JOIN users u ON (u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id))
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.last_message_at DESC
        ");
        $stmt->execute([$userId, $userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get messages for conversation
     */
    private function getMessagesForConversation($conversationId, $userId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username, u.full_name, u.profile_image
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            JOIN conversations c ON m.conversation_id = c.id
            WHERE m.conversation_id = ? 
            AND (c.user1_id = ? OR c.user2_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversationId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark conversation messages as read
     */
    private function markConversationAsRead($conversationId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE messages 
            SET is_read = TRUE 
            WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE
        ");
        $stmt->execute([$conversationId, $userId]);
    }

    /**
     * Send message (AJAX)
     */
    public function send() {
        header('Content-Type: application/json');
        
        $recipientId = $_POST['recipient_id'] ?? null;
        $message = trim($_POST['message'] ?? '');
        $userId = $_SESSION['user_id'];
        
        if (!$recipientId || !$message || $recipientId == $userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        try {
            // Find or create conversation
            $conversationId = $this->findOrCreateConversation($userId, $recipientId);
            
            // Insert message
            $stmt = $this->db->prepare("
                INSERT INTO messages (conversation_id, sender_id, message)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$conversationId, $userId, $message]);
            $messageId = $this->db->lastInsertId();
            
            // Update conversation last_message_at
            $stmt = $this->db->prepare("
                UPDATE conversations 
                SET last_message_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$conversationId]);
            
            // Get the inserted message with user data
            $stmt = $this->db->prepare("
                SELECT m.*, u.username, u.full_name, u.profile_image
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([$messageId]);
            $newMessage = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'message' => $newMessage,
                'conversation_id' => $conversationId
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        exit;
    }

    /**
     * Find or create conversation between two users
     */
    private function findOrCreateConversation($user1Id, $user2Id) {
        // Ensure user1_id is always the smaller ID for consistency
        $smallerId = min($user1Id, $user2Id);
        $largerId = max($user1Id, $user2Id);
        
        $stmt = $this->db->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) 
            OR (user1_id = ? AND user2_id = ?)
        ");
        $stmt->execute([$smallerId, $largerId, $largerId, $smallerId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conversation) {
            return $conversation['id'];
        }
        
        // Create new conversation
        $stmt = $this->db->prepare("
            INSERT INTO conversations (user1_id, user2_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$smallerId, $largerId]);
        return $this->db->lastInsertId();
    }

    /**
     * Get unread message count (AJAX)
     */
    public function getUnreadCount() {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT m.conversation_id) as count
                FROM messages m
                JOIN conversations c ON m.conversation_id = c.id
                WHERE (c.user1_id = ? OR c.user2_id = ?)
                AND m.sender_id != ?
                AND m.is_read = FALSE
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
        } catch (PDOException $e) {
            echo json_encode(['success' => true, 'count' => 0]);
        }
        exit;
    }

    /**
     * Get new messages for active conversation (AJAX polling)
     */
    public function getNew() {
        header('Content-Type: application/json');
        
        $conversationId = $_GET['conversation_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'messages' => []]);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username, u.full_name, u.profile_image
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                JOIN conversations c ON m.conversation_id = c.id
                WHERE m.conversation_id = ? 
                AND m.id > ?
                AND (c.user1_id = ? OR c.user2_id = ?)
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$conversationId, $lastMessageId, $userId, $userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mark as read
            if (!empty($messages)) {
                $this->markConversationAsRead($conversationId, $userId);
            }
            
            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (PDOException $e) {
            echo json_encode(['success' => true, 'messages' => []]);
        }
        exit;
    }

    /**
     * Start a new conversation with a user
     */
    public function startConversation($otherUserId) {
        $userId = $_SESSION['user_id'];
        
        if (!$otherUserId || $otherUserId == $userId) {
            header('Location: index.php?url=messages');
            exit;
        }
        
        try {
            // Find or create conversation
            $conversationId = $this->findOrCreateConversation($userId, $otherUserId);
            
            header('Location: index.php?url=messages&c=' . $conversationId);
        } catch (PDOException $e) {
            header('Location: index.php?url=messages');
        }
        exit;
    }
}
