-- Add per-post visibility control
-- Run this migration against the nexo database

ALTER TABLE posts
    ADD COLUMN visibility ENUM('public','friends','only_me') NOT NULL DEFAULT 'public'
    AFTER image;
