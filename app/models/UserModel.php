<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private PDO $db;
    // Minutes window to consider a user as online.
    private const ONLINE_WINDOW_MINUTES = 5;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        return $stmt->fetch();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($username)]);
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
        ?string $gender   = null,
        ?string $coverImage = null
    ): bool {
        if ($coverImage !== null) {
            try {
                $stmt = $this->db->prepare(
                    'UPDATE users
                     SET full_name = ?, username = ?, bio = ?, profile_image = ?,
                         mobile = ?, birthday = ?, gender = ?, cover_image = ?
                     WHERE id = ?'
                );
                return $stmt->execute([$fullName, $username, $bio, $profileImage, $mobile, $birthday, $gender, $coverImage, $id]);
            } catch (PDOException $e) {
                // cover_image column may not exist yet.
            }
        }

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

    public function updateCoverImage(int $id, string $coverImage): bool {
        try {
            $stmt = $this->db->prepare('UPDATE users SET cover_image = ? WHERE id = ?');
            return $stmt->execute([$coverImage, $id]);
        } catch (PDOException $e) {
            error_log('UserModel::updateCoverImage error: ' . $e->getMessage());
            return false;
        }
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

    public function search(string $query, ?int $currentUserId = null, bool $friendsOnly = false): array {
        $like = '%' . $query . '%';

        if ($currentUserId) {
            $friendsOnlyValue = $friendsOnly ? 1 : 0;

            try {
                $stmt = $this->db->prepare(
                    "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio,
                            CASE
                                WHEN EXISTS (
                                    SELECT 1 FROM friendships f
                                    WHERE f.user_id = :uid_friend AND f.friend_id = u.id AND f.status = 'accepted'
                                ) THEN 1 ELSE 0
                            END AS is_friend,
                            CASE
                                WHEN u.last_active_at IS NOT NULL
                                     AND u.last_active_at >= (NOW() - INTERVAL :online_window MINUTE)
                                THEN 1 ELSE 0
                            END AS is_online
                     FROM users u
                     WHERE (u.username LIKE :like OR u.full_name LIKE :like)
                       AND u.id != :uid_self
                       AND (
                           :friends_only = 0
                           OR EXISTS (
                               SELECT 1 FROM friendships f
                               WHERE f.user_id = :uid_filter
                                 AND f.friend_id = u.id
                                 AND f.status = 'accepted'
                           )
                       )
                     LIMIT 20"
                );
                $params = [
                    ':uid_friend'    => $currentUserId,
                    ':uid_self'      => $currentUserId,
                    ':uid_filter'    => $currentUserId,
                    ':friends_only'  => $friendsOnlyValue,
                    ':like'          => $like,
                    ':online_window' => self::ONLINE_WINDOW_MINUTES,
                ];
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                try {
                    $stmt = $this->db->prepare(
                        "SELECT u.id, u.username, u.full_name, u.profile_image, u.bio,
                                CASE
                                    WHEN EXISTS (
                                        SELECT 1 FROM friendships f
                                        WHERE f.user_id = :uid_friend AND f.friend_id = u.id AND f.status = 'accepted'
                                    ) THEN 1 ELSE 0
                                END AS is_friend,
                                0 AS is_online
                         FROM users u
                         WHERE (u.username LIKE :like OR u.full_name LIKE :like)
                           AND u.id != :uid_self
                           AND (
                               :friends_only = 0
                               OR EXISTS (
                                   SELECT 1 FROM friendships f
                                   WHERE f.user_id = :uid_filter
                                     AND f.friend_id = u.id
                                     AND f.status = 'accepted'
                               )
                           )
                         LIMIT 20"
                    );
                    $params = [
                        ':uid_friend'   => $currentUserId,
                        ':uid_self'     => $currentUserId,
                        ':uid_filter'   => $currentUserId,
                        ':friends_only' => $friendsOnlyValue,
                        ':like'         => $like,
                    ];
                    $stmt->execute($params);
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Fallback to basic search if database migration hasn't been applied yet.
                }
            }
        }

        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, profile_image, bio FROM users
             WHERE username LIKE ? OR full_name LIKE ? LIMIT 20'
        );
        $stmt->execute([$like, $like]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            $row['is_friend'] = 0;
            $row['is_online'] = 0;
            return $row;
        }, $rows);
    }

    public function getSuggestions(int $currentUserId): array {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, username, full_name, profile_image FROM users
                 WHERE id != ?
                   AND id NOT IN (
                       SELECT friend_id FROM friendships
                       WHERE user_id = ? AND status = \'accepted\'
                   )
                 ORDER BY created_at DESC LIMIT 5'
            );
            $stmt->execute([$currentUserId, $currentUserId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // friendships table may not exist yet — return users without friend filter
            error_log('UserModel::getSuggestions error: ' . $e->getMessage());
            $stmt = $this->db->prepare(
                'SELECT id, username, full_name, profile_image FROM users
                 WHERE id != ? ORDER BY created_at DESC LIMIT 5'
            );
            $stmt->execute([$currentUserId]);
            return $stmt->fetchAll();
        }
    }
}
