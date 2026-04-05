<?php
require_once __DIR__ . '/../../config/database.php';

class LikeModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function toggle(int $postId, int $userId): array {
        $existing = $this->db->prepare(
            'SELECT id FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1'
        );
        $existing->execute([$postId, $userId]);

        if ($existing->fetch()) {
            $del = $this->db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
            $del->execute([$postId, $userId]);
            $liked = false;
        } else {
            $ins = $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
            $ins->execute([$postId, $userId]);
            $liked = true;
        }

        $count = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $count->execute([$postId]);

        return ['liked' => $liked, 'count' => (int) $count->fetchColumn()];
    }
}