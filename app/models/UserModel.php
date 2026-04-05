<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $username, string $email, string $password, string $fullName): int {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hashed, $fullName]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $fullName, string $username, string $bio, string $profileImage): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = ?, username = ?, bio = ?, profile_image = ? WHERE id = ?'
        );
        return $stmt->execute([$fullName, $username, $bio, $profileImage, $id]);
    }

    public function search(string $query): array {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, profile_image, bio FROM users
             WHERE username LIKE ? OR full_name LIKE ? LIMIT 20'
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function getSuggestions(int $currentUserId): array {
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, profile_image FROM users
             WHERE id != ? ORDER BY created_at DESC LIMIT 5'
        );
        $stmt->execute([$currentUserId]);
        return $stmt->fetchAll();
    }
}