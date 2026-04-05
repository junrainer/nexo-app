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
            echo '<div style="text-align:center;padding:80px 20px;color:#888;">User not found.</div>';
            require __DIR__ . '/../views/partials/footer.php';
            exit;
        }

        $currentUserId = $_SESSION['user_id'];
        $posts         = $this->postModel->getByUser($user['id'], $currentUserId);
        $isOwner       = ((int)$user['id'] === (int)$currentUserId);
        
        // Get friend count and friendship status
        $friendCount = 0;
        $friendshipStatus = 'none';
        try {
            $db = Database::getInstance()->getConnection();
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
                    } else if ($friendship['status'] === 'pending') {
                        $friendshipStatus = ($friendship['action_user_id'] == $currentUserId) ? 'pending_sent' : 'pending_received';
                    }
                }
            }
        } catch (PDOException $e) {
            // Table might not exist
        }
        
        require __DIR__ . '/../views/profile/profile.php';
    }

    public function update(): void {
        $userId   = $_SESSION['user_id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $user     = $this->userModel->findById($userId);
        $image    = $user['profile_image'];

        if (empty($fullName)) {
            $_SESSION['error'] = 'Full name cannot be empty.';
            header('Location: index.php?url=profile/' . $_SESSION['username']);
            exit;
        }

        if (!empty($_FILES['profile_image']['name'])) {
            $newImage = $this->handleImageUpload($_FILES['profile_image']);
            if ($newImage) {
                $image = $newImage;
            }
        }

        $this->userModel->update(
            $userId,
            htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($username ?: $user['username'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'),
            $image
        );

        $_SESSION['full_name']     = $fullName;
        $_SESSION['profile_image'] = $image;
        if ($username) $_SESSION['username'] = $username;

        header('Location: index.php?url=profile/' . $_SESSION['username']);
        exit;
    }

    // CHANGE: Make sure public/assets/uploads/ is writable (chmod 777 on InfinityFree)
    private function handleImageUpload(array $file): string|false {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($file['type'], $allowed) || $file['size'] > $maxSize) {
            return false;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $dest     = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return $filename;
    }
}