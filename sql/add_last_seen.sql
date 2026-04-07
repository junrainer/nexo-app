-- Nexo – Add last_seen for online presence tracking
-- Run this after nexo_app.sql

USE nexo;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL AFTER created_at;

CREATE INDEX IF NOT EXISTS idx_users_last_seen ON users (last_seen);
