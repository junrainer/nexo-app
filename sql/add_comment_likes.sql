-- Nexo – Comment Likes
-- Run this after nexo_app.sql


CREATE TABLE IF NOT EXISTS comment_likes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id    INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
