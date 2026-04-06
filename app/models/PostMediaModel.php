<?php
require_once __DIR__ . '/../../config/database.php';

class PostMediaModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByPost(int $postId): array {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM post_media WHERE post_id = ? ORDER BY sort_order ASC, id ASC'
            );
            $stmt->execute([$postId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('PostMediaModel::getByPost error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(int $postId, string $filename, string $type, int $sortOrder = 0): int {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO post_media (post_id, filename, media_type, sort_order) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$postId, $filename, $type, $sortOrder]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('PostMediaModel::create error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Returns the list of filenames that were deleted (so callers can remove the files).
     */
    public function deleteByPost(int $postId): array {
        try {
            $stmt = $this->db->prepare('SELECT filename FROM post_media WHERE post_id = ?');
            $stmt->execute([$postId]);
            $files = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $this->db->prepare('DELETE FROM post_media WHERE post_id = ?')->execute([$postId]);

            return $files;
        } catch (PDOException $e) {
            error_log('PostMediaModel::deleteByPost error: ' . $e->getMessage());
            return [];
        }
    }
}
