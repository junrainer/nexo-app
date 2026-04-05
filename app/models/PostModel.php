<?php
require_once __DIR__ . '/../../config/database.php';

class PostModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllForFeed(int $currentUserId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                    (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
             FROM posts p
             JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$currentUserId, $currentUserId]);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, int $currentUserId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                    (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$currentUserId, $currentUserId, $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $content, ?string $image = null): int {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $image]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $content): bool {
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM posts WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function search(string $query, int $currentUserId): array {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                    (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.content LIKE ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$currentUserId, $currentUserId, $like]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}