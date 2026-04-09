    -- Nexo – Post Media Table
    -- Supports multiple images (max 5) and a single video per post
    -- Run this after nexo_app.sql


    CREATE TABLE IF NOT EXISTS post_media (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        post_id     INT NOT NULL,
        filename    VARCHAR(255) NOT NULL,
        media_type  ENUM('image','video') NOT NULL DEFAULT 'image',
        sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    );
