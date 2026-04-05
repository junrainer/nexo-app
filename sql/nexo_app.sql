-- Nexo Database Schema
-- Web Systems and Technologies - Final Project
-- Saint Michael College of Caraga

CREATE DATABASE IF NOT EXISTS nexo;
USE nexo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    mobile VARCHAR(20) DEFAULT NULL,
    birthday DATE DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (post_id, user_id)
);

-- Sample users (password for all: password)
INSERT INTO users (username, email, password, full_name, bio) VALUES
('marcos_reyes', 'marcos@nexo.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marcos Reyes', 'IT Student | SMCC Caraga'),
('claire_santos', 'claire@nexo.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Claire Santos', 'Web dev in progress'),
('javier_dc', 'javier@nexo.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Javier Dela Cruz', 'SQL is life. Documentation saves lives.');

INSERT INTO posts (user_id, content) VALUES
(2, 'Just submitted our Web Systems final project! Three months of late nights and debugging sessions but we finally built something we are proud of. #WebSystems #SMCC #FinalProject'),
(3, 'Study tip for IT students: Do not just copy code from Stack Overflow. Understand what each line does. Use prepared statements always!'),
(1, 'Finally got MVC architecture to click in my head. Models talk to DB, Controllers handle logic, Views display stuff. Simple when you think about it. #PHP #MVC');

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 3, 'Congrats! Your team crushed it!'),
(1, 1, 'So proud of you all! Cannot wait to see the demo.'),
(2, 2, 'This tip actually saved my project. Thanks Javier!');

INSERT INTO likes (post_id, user_id) VALUES
(1, 1), (1, 3), (2, 1), (2, 2), (3, 2), (3, 3);