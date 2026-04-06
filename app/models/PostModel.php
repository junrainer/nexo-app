<?php
require_once __DIR__ . '/../../config/database.php';

class PostModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Returns the SQL fragment (without a leading AND/WHERE keyword) that restricts
     * posts to those the given user may see, plus the three positional bind values
     * needed for it: [currentUserId, currentUserId, currentUserId].
     *
     * Visibility rules:
     *   public   – everyone
     *   friends  – post author's accepted friends + the author themselves
     *   only_me  – post author only
     */
    private function visibilitySql(string $alias = 'p'): string {
        return "(
                    {$alias}.user_id = ?
                    OR {$alias}.visibility = 'public'
                    OR ({$alias}.visibility = 'friends' AND EXISTS (
                        SELECT 1 FROM friendships f
                        WHERE ((f.user_id = {$alias}.user_id AND f.friend_id = ?)
                            OR (f.user_id = ? AND f.friend_id = {$alias}.user_id))
                        AND f.status = 'accepted'
                    ))
                )";
    }

    public function getAllForFeed(int $currentUserId): array {
        $visibilitySql = $this->visibilitySql();
        try {
            $stmt = $this->db->prepare(
                "SELECT p.*, u.username, u.full_name, u.profile_image,
                        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                        (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE {$visibilitySql}
                 ORDER BY p.created_at DESC"
            );
            $stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // friendships or saved_posts table may not exist yet — fall back to public posts only
            error_log('PostModel::getAllForFeed error: ' . $e->getMessage());
            $stmt = $this->db->prepare(
                "SELECT p.*, u.username, u.full_name, u.profile_image,
                        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                        0 AS user_saved
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.visibility = 'public' OR p.user_id = ?
                 ORDER BY p.created_at DESC"
            );
            $stmt->execute([$currentUserId, $currentUserId]);
            return $stmt->fetchAll();
        }
    }

    public function getByUser(int $userId, int $currentUserId): array {
        $visibilitySql = $this->visibilitySql();
        $stmt   = $this->db->prepare(
            "SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                    (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = ?
               AND {$visibilitySql}
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$currentUserId, $currentUserId, $userId, $currentUserId, $currentUserId, $currentUserId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $content, ?string $image = null, string $visibility = 'public'): int {
        $allowed = ['public', 'friends', 'only_me'];
        if (!in_array($visibility, $allowed, true)) {
            $visibility = 'public';
        }
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, image, visibility) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $image, $visibility]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $content, string $visibility = 'public'): bool {
        $allowed = ['public', 'friends', 'only_me'];
        if (!in_array($visibility, $allowed, true)) {
            $visibility = 'public';
        }
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = ?, visibility = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$content, $visibility, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM posts WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function search(string $query, int $currentUserId): array {
        $visibilitySql = $this->visibilitySql();
        $like   = '%' . $query . '%';
        $stmt   = $this->db->prepare(
            "SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS user_liked,
                    (SELECT COUNT(*) FROM saved_posts sp WHERE sp.post_id = p.id AND sp.user_id = ?) AS user_saved
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.content LIKE ?
               AND {$visibilitySql}
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$currentUserId, $currentUserId, $like, $currentUserId, $currentUserId, $currentUserId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getSaved(int $userId): array {
        $visibilitySql = $this->visibilitySql();
        $stmt   = $this->db->prepare(
            "SELECT p.*, u.username, u.full_name, u.profile_image,
                    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked,
                    1 AS user_saved
             FROM saved_posts sp
             JOIN posts p ON sp.post_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE sp.user_id = ?
               AND {$visibilitySql}
             ORDER BY sp.created_at DESC"
        );
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}