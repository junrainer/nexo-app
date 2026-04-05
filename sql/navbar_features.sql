-- Nexo – Navbar Features Schema
-- Run AFTER nexo_app.sql
-- Tables: friendships, conversations, messages, notifications, user_preferences, saved_posts

USE nexo;

-- ============================================
-- TABLE: friendships
-- ============================================
CREATE TABLE IF NOT EXISTS friendships (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    friend_id      INT NOT NULL,
    status         ENUM('pending','accepted','blocked') NOT NULL DEFAULT 'pending',
    action_user_id INT NOT NULL COMMENT 'Who performed the last action',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_friendship (user_id, friend_id),
    FOREIGN KEY (user_id)        REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (action_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: conversations
-- ============================================
CREATE TABLE IF NOT EXISTS conversations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user1_id        INT NOT NULL,
    user2_id        INT NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_conversation (user1_id, user2_id),
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: messages
-- ============================================
CREATE TABLE IF NOT EXISTS messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id       INT NOT NULL,
    message         TEXT NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id)       REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: notifications
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    type       VARCHAR(50) NOT NULL,
    actor_id   INT NOT NULL,
    related_id INT DEFAULT NULL,
    message    VARCHAR(255) NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: user_preferences
-- ============================================
CREATE TABLE IF NOT EXISTS user_preferences (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    user_id                 INT NOT NULL UNIQUE,
    dark_mode               TINYINT(1) NOT NULL DEFAULT 1,
    email_notifications     TINYINT(1) NOT NULL DEFAULT 1,
    push_notifications      TINYINT(1) NOT NULL DEFAULT 1,
    friend_requests_privacy ENUM('everyone','friends_of_friends','nobody') NOT NULL DEFAULT 'everyone',
    post_privacy            ENUM('public','friends','only_me') NOT NULL DEFAULT 'public',
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE: saved_posts
-- ============================================
CREATE TABLE IF NOT EXISTS saved_posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    post_id    INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- ============================================
-- SAMPLE DATA FOR TESTING (IDEMPOTENT)
-- ============================================

-- Create default preferences for existing users
INSERT INTO user_preferences (user_id, dark_mode)
SELECT u.id, 1
FROM users u
LEFT JOIN user_preferences up ON up.user_id = u.id
WHERE up.user_id IS NULL;

-- Sample friendships
INSERT INTO friendships (user_id, friend_id, status, action_user_id) VALUES
(1, 2, 'accepted', 1),
(2, 1, 'accepted', 1),
(1, 3, 'pending', 3),
(3, 1, 'pending', 3)
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    action_user_id = VALUES(action_user_id),
    updated_at = CURRENT_TIMESTAMP;

-- Sample notifications
INSERT INTO notifications (user_id, type, actor_id, related_id, message) VALUES
(1, 'like', 2, 3, 'Claire Santos liked your post'),
(1, 'comment', 3, 1, 'Javier Dela Cruz commented on your post'),
(1, 'friend_request', 3, 4, 'Javier Dela Cruz sent you a friend request'),
(2, 'like', 1, 1, 'Marcos Reyes liked your post'),
(2, 'friend_accept', 1, 1, 'Marcos Reyes accepted your friend request');

-- Sample conversation (normalize pair: smaller id first)
INSERT INTO conversations (user1_id, user2_id, last_message_at)
SELECT LEAST(1,2), GREATEST(1,2), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM conversations c
    WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
       OR (c.user1_id = GREATEST(1,2) AND c.user2_id = LEAST(1,2))
);

-- Sample messages
INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 2, 'Hey Marcos! How is the project going?', 1
FROM conversations c WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2)) LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 1, 'Going great! Almost done with all features.', 1
FROM conversations c WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2)) LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 2, 'Awesome! Let me know if you need help testing.', 0
FROM conversations c WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2)) LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 1, 'Will do, thanks!', 0
FROM conversations c WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2)) LIMIT 1;

-- Sample saved posts
INSERT INTO saved_posts (user_id, post_id) VALUES (1, 2), (2, 1)
ON DUPLICATE KEY UPDATE created_at = created_at;

-- Keep conversation timestamp fresh
UPDATE conversations c SET c.last_message_at = NOW()
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2));
