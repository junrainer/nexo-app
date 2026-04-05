<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';

class ProfileController {
    private UserModel $userModel;
    private PostModel $postModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
    }

    public function show(string $username): void {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            http_response_code(404);
            $pageTitle = 'Not Found – Nexo';
            require __DIR__ . '/../views/partials/header.php';
            echo '<div style="text-align:center;padding:80px 20px;color:#888;"><h2>User not found.</h2><a href="index.php?url=feed" style="color:#6366f1;">Back to feed</a></div>';
            require __DIR__ . '/../views/partials/footer.php';
            exit;
        }

        $currentUserId = $_SESSION['user_id'];
        $posts         = $this->postModel->getByUser($user['id'], $currentUserId);
        $isOwner       = ((int)$user['id'] === (int)$currentUserId);

        $friendCount      = 0;
        $friendshipStatus = 'none';

        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM friendships WHERE user_id = ? AND status = 'accepted'");
            $stmt->execute([$user['id']]);
            $friendCount = (int)$stmt->fetchColumn();

            if (!$isOwner) {
                $stmt = $db->prepare("SELECT status, action_user_id FROM friendships WHERE user_id = ? AND friend_id = ?");
                $stmt->execute([$currentUserId, $user['id']]);
                $friendship = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($friendship) {
                    if ($friendship['status'] === 'accepted') {
                        $friendshipStatus = 'friends';
                    } elseif ($friendship['status'] === 'pending') {
                        $friendshipStatus = ($friendship['action_user_id'] == $currentUserId)
                            ? 'pending_sent'
                            : 'pending_received';
                    }
                }
            }
        } catch (PDOException $e) { /* friendships table may not exist */ }

        $pageTitle = htmlspecialchars($user['full_name']) . ' – Nexo';
        require __DIR__ . '/../views/profile/profile.php';
    }

    public function update(): void {
        $userId   = $_SESSION['user_id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $mobile   = trim($_POST['mobile'] ?? '') ?: null;
        $birthday = trim($_POST['birthday'] ?? '') ?: null;
        $gender   = trim($_POST['gender'] ?? '') ?: null;

        $user  = $this->userModel->findById($userId);
        $image = $user['profile_image'];

        if (empty($fullName)) {
            $_SESSION['error'] = 'Full name cannot be empty.';
            header('Location: index.php?url=profile/' . rawurlencode($_SESSION['username']));
            exit;
        }

        // Validate username uniqueness if changed
        $newUsername = $username ?: $user['username'];
        if ($newUsername !== $user['username']) {
            if (!preg_match('/^[a-zA-Z0-9._]+$/', $newUsername)) {
                $_SESSION['error'] = 'Username can only contain letters, numbers, dots, and underscores.';
                header('Location: index.php?url=profile/' . rawurlencode($_SESSION['username']));
                exit;
            }
            $existing = $this->userModel->findByUsername($newUsername);
            if ($existing && $existing['id'] != $userId) {
                $_SESSION['error'] = 'That username is already taken.';
                header('Location: index.php?url=profile/' . rawurlencode($_SESSION['username']));
                exit;
            }
        }

        if (!empty($_FILES['profile_image']['name'])) {
            $newImage = $this->handleImageUpload($_FILES['profile_image']);
            if ($newImage) {
                $image = $newImage;
            } else {
                $_SESSION['error'] = 'Invalid image. Use JPG, PNG, GIF, or WEBP (max 2MB).';
                header('Location: index.php?url=profile/' . rawurlencode($_SESSION['username']));
                exit;
            }
        }

        $this->userModel->updateFull(
            $userId,
            htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($newUsername, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'),
            $image,
            $mobile,
            $birthday,
            $gender
        );

        $_SESSION['full_name']     = $fullName;
        $_SESSION['profile_image'] = $image;
        $_SESSION['username']      = $newUsername;
        $_SESSION['toast_success'] = 'Profile updated successfully.';

        header('Location: index.php?url=profile/' . rawurlencode($newUsername));
        exit;
    }

    private function handleImageUpload(array $file): string|false {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if (!in_array($file['type'], $allowed) || $file['size'] > $maxSize) return false;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $dest     = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

        return $filename;
    }
}
