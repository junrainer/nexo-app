<?php
require_once __DIR__ . '/../../config/database.php';

class PostMediaModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByPost(int $postId): array {
        if (!$this->ensureTable()) return [];
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
        if (!$this->ensureTable()) {
            error_log('PostMediaModel::create skipped for post ' . $postId . ' because post_media table is unavailable.');
            return 0;
        }
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
        if (!$this->ensureTable()) return [];
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

    private function ensureTable(): bool {
        try {
            $this->db->exec(
                $this->tableSql(true)
            );
            return true;
        } catch (PDOException $e) {
            error_log('PostMediaModel::ensureTable failed to create post_media with foreign key constraints (constraints unsupported or parent table missing): ' . $e->getMessage());
            try {
                // Some hosts block foreign keys or use engines that reject FK constraints.
                $this->db->exec(
                    $this->tableSql(false)
                );
                return true;
            } catch (PDOException $fallbackError) {
                error_log('PostMediaModel::ensureTable fallback error: ' . $fallbackError->getMessage());
                return false;
            }
        }
    }

    private function tableSql(bool $withForeignKey): string {
        $sql = "CREATE TABLE IF NOT EXISTS post_media (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
                    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        if ($withForeignKey) {
            $sql .= ",
                    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE";
        }
        $sql .= "
                )";
        return $sql;
    }
}
