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
        $currentUserId = (int) $userId;
        $activeConversationId = $_GET['c'] ?? null;
        
        $conversations = [];
        $messages = [];
        $activeUser = null;
        
        try {
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
                
                $conversationAllowed = (bool) $activeUser;
                if (!$conversationAllowed) {
                    $stmt = $this->db->prepare("
                        SELECT 1
                        FROM conversations
                        WHERE id = ? AND (user1_id = ? OR user2_id = ?)
                    ");
                    $stmt->execute([$activeConversationId, $userId, $userId]);
                    $conversationAllowed = (bool) $stmt->fetchColumn();
                }
                
                if ($conversationAllowed) {
                    // Mark messages as read
                    $this->markConversationAsRead($activeConversationId, $userId);
                }
            }
            
            // Get all conversations
            $conversations = $this->getConversationsForUser($userId);
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
                   m.message as last_message,
                   m.created_at as last_message_time,
                   m.sender_id as last_message_sender_id,
                   m.is_read as last_message_is_read,
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = FALSE) as unread_count
            FROM conversations c
            JOIN users u ON (u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id))
            LEFT JOIN (
                SELECT conversation_id, MAX(id) AS last_message_id
                FROM messages
                GROUP BY conversation_id
            ) lm ON lm.conversation_id = c.id
            LEFT JOIN messages m ON m.id = lm.last_message_id
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY COALESCE(m.created_at, c.last_message_at) DESC
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

    /**
     * Load all messages for a conversation (AJAX – used by floating chat)
     */
    public function load() {
        header('Content-Type: application/json');
        $convId = $_GET['conversation_id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (!$convId || !is_numeric($convId)) {
            echo json_encode(['success' => false, 'messages' => []]);
            exit;
        }

        try {
            // Verify the user belongs to this conversation and fetch the other user
            $stmt = $this->db->prepare("
                SELECT u.id AS recipient_id, u.username, u.full_name, u.profile_image
                FROM conversations c
                JOIN users u ON u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id)
                WHERE c.id = ? AND (c.user1_id = ? OR c.user2_id = ?)
            ");
            $stmt->execute([$userId, $convId, $userId, $userId]);
            $other = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$other) {
                echo json_encode(['success' => false, 'messages' => []]);
                exit;
            }

            $rows = $this->getMessagesForConversation($convId, $userId);
            $messages = array_map(function ($m) use ($userId) {
                $m['is_mine'] = ($m['sender_id'] == $userId);
                return $m;
            }, $rows);

            $this->markConversationAsRead($convId, $userId);

            echo json_encode([
                'success'      => true,
                'messages'     => $messages,
                'recipient_id' => $other['recipient_id'],
                'other_user'   => $other,
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'messages' => []]);
        }
        exit;
    }

    /**
     */
    public function getRecent() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        try {
            $stmt = $this->db->prepare("
                SELECT c.id,
                       u.full_name  AS name,
                       u.profile_image AS avatar,
                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_time,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != :uid2 AND is_read = FALSE) AS unread
                FROM conversations c
                JOIN users u ON u.id = IF(c.user1_id = :uid3, c.user2_id, c.user1_id)
                WHERE c.user1_id = :uid4 OR c.user2_id = :uid5
                ORDER BY c.last_message_at DESC
                LIMIT 8
            ");
            $stmt->execute([
                ':uid2' => $userId,
                ':uid3' => $userId,
                ':uid4' => $userId,
                ':uid5' => $userId,
            ]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $conversations = array_map(function($r) {
                return [
                    'id'           => $r['id'],
                    'name'         => $r['name'],
                    'avatar'       => $r['avatar'] ?: 'default.png',
                    'last_message' => $r['last_message'] ?: '',
                    'last_time'    => $r['last_time']    ?: '',
                    'unread'       => (int)$r['unread'],
                    'online'       => false,
                ];
            }, $rows);

            echo json_encode(['success' => true, 'conversations' => $conversations]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'DB error']);
        }
        exit;
    }
}
