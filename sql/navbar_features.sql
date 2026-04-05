-- ...existing code...

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
    SELECT 1
    FROM conversations c
    WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
       OR (c.user1_id = GREATEST(1,2) AND c.user2_id = LEAST(1,2))
);

-- Sample messages (attach to existing conversation id)
INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 2, 'Hey Marcos! How is the project going?', 1
FROM conversations c
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 1, 'Going great! Almost done with all features.', 1
FROM conversations c
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 2, 'Awesome! Let me know if you need help testing.', 0
FROM conversations c
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
LIMIT 1;

INSERT INTO messages (conversation_id, sender_id, message, is_read)
SELECT c.id, 1, 'Will do, thanks!', 0
FROM conversations c
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2))
LIMIT 1;

-- Sample saved posts
INSERT INTO saved_posts (user_id, post_id) VALUES
(1, 2),
(2, 1)
ON DUPLICATE KEY UPDATE created_at = created_at;

-- Keep conversation timestamp fresh
UPDATE conversations c
SET c.last_message_at = NOW()
WHERE (c.user1_id = LEAST(1,2) AND c.user2_id = GREATEST(1,2));

-- ...existing code...