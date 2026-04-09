-- Nexo – Password Reset Table
-- Run this after nexo_app.sql and navbar_features.sql


CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(100) NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    code       CHAR(6)      NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
);

-- Migration for existing installs: add the code column if it is missing.
ALTER TABLE password_resets ADD COLUMN IF NOT EXISTS code CHAR(6) NOT NULL DEFAULT '';
