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

    public function create(
        string $username,
        string $email,
        string $password,
        string $fullName,
        ?string $mobile       = null,
        ?string $birthday     = null,
        ?string $gender       = null,
        string  $profileImage = 'default.png'
    ): int {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, full_name, mobile, birthday, gender, profile_image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hashed, $fullName, $mobile, $birthday, $gender, $profileImage]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update basic profile: name, username, bio, image.
     * For full update (mobile/birthday/gender) use updateFull().
     */
    public function update(int $id, string $fullName, string $username, string $bio, string $profileImage): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = ?, username = ?, bio = ?, profile_image = ? WHERE id = ?'
        );
        return $stmt->execute([$fullName, $username, $bio, $profileImage, $id]);
    }

    /**
     * Update all profile fields including mobile, birthday, gender.
     */
    public function updateFull(
        int $id,
        string $fullName,
        string $username,
        string $bio,
        string $profileImage,
        ?string $mobile   = null,
        ?string $birthday = null,
        ?string $gender   = null
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET full_name = ?, username = ?, bio = ?, profile_image = ?,
                 mobile = ?, birthday = ?, gender = ?
             WHERE id = ?'
        );
        return $stmt->execute([$fullName, $username, $bio, $profileImage, $mobile, $birthday, $gender, $id]);
    }

    public function updateEmail(int $id, string $email): bool {
        $stmt = $this->db->prepare('UPDATE users SET email = ? WHERE id = ?');
        return $stmt->execute([$email, $id]);
    }

    public function updatePassword(int $id, string $newPassword): bool {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hashed, $id]);
    }

    public function verifyPassword(int $id, string $password): bool {
        $stmt = $this->db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row && password_verify($password, $row['password']);
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
