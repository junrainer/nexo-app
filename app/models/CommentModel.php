<?php
require_once __DIR__ . '/../../config/database.php';

class CommentModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByPost(int $postId, int $currentUserId = 0): array {
        try {
            $stmt = $this->db->prepare(
                'SELECT c.*, u.username, u.full_name, u.profile_image
                        ,(SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count
                        ,(SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = ?) AS user_liked
                 FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC'
            );
            $stmt->execute([$currentUserId, $postId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $stmt = $this->db->prepare(
                'SELECT c.*, u.username, u.full_name, u.profile_image, 0 AS like_count, 0 AS user_liked
                 FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC'
            );
            $stmt->execute([$postId]);
            return $stmt->fetchAll();
        }
    }

    public function create(int $postId, int $userId, string $content): int {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$postId, $userId, $content]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $content): bool {
        $stmt = $this->db->prepare(
            'UPDATE comments SET content = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM comments WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Returns the most-recent comment row for the given user, or null if none exist.
     * Used to enforce a posting cooldown.
     */
    public function getLastByUser(int $userId): ?array {
        $stmt = $this->db->prepare(
            'SELECT created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function toggleLike(int $commentId, int $userId): array {
        try {
            $existing = $this->db->prepare(
                'SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ? LIMIT 1'
            );
            $existing->execute([$commentId, $userId]);

            if ($existing->fetch()) {
                $this->db->prepare('DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?')
                         ->execute([$commentId, $userId]);
                $liked = false;
            } else {
                $this->db->prepare('INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)')
                         ->execute([$commentId, $userId]);
                $liked = true;
            }

            $countStmt = $this->db->prepare('SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?');
            $countStmt->execute([$commentId]);

            return ['success' => true, 'liked' => $liked, 'count' => (int)$countStmt->fetchColumn()];
        } catch (PDOException $e) {
            return ['success' => false, 'liked' => false, 'count' => 0];
        }
    }
}
