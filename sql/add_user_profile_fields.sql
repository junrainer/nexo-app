-- Nexo – Add optional profile fields to users table
-- Run this if you already have an existing nexo database (after nexo_app.sql was imported).

USE nexo;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS mobile  VARCHAR(20)  DEFAULT NULL AFTER profile_image,
    ADD COLUMN IF NOT EXISTS birthday DATE         DEFAULT NULL AFTER mobile,
    ADD COLUMN IF NOT EXISTS gender  VARCHAR(20)  DEFAULT NULL AFTER birthday;
