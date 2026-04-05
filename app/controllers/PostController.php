<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/CommentModel.php';
require_once __DIR__ . '/../models/LikeModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class PostController {
    private PostModel    $postModel;
    private CommentModel $commentModel;
    private LikeModel    $likeModel;
    private UserModel    $userModel;

    public function __construct() {
        $this->postModel    = new PostModel();
        $this->commentModel = new CommentModel();
        $this->likeModel    = new LikeModel();
        $this->userModel    = new UserModel();
    }

    public function feed(): void {
        $currentUserId = $_SESSION['user_id'];
        $posts         = $this->postModel->getAllForFeed($currentUserId);
        $suggestions   = $this->userModel->getSuggestions($currentUserId);
        require __DIR__ . '/../views/posts/feed.php';
    }

    public function create(): void {
        $content = trim($_POST['content'] ?? '');
        $userId  = $_SESSION['user_id'];
        $image   = null;

        if (empty($content)) {
            $_SESSION['error'] = 'Post cannot be empty.';
            header('Location: index.php?url=feed');
            exit;
        }

        if (!empty($_FILES['image']['name'])) {
            $image = $this->handleImageUpload($_FILES['image']);
            if (!$image) {
                $_SESSION['error'] = 'Invalid image. Use JPG, PNG, or GIF (max 5MB).';
                header('Location: index.php?url=feed');
                exit;
            }
        }

        $this->postModel->create($userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $image);
        header('Location: index.php?url=feed');
        exit;
    }

    public function update(): void {
        $postId  = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = $_SESSION['user_id'];

        if (!empty($content)) {
            $this->postModel->update($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        }

        header('Location: index.php?url=feed');
        exit;
    }

    public function delete(): void {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        $this->postModel->delete($postId, $userId);
        header('Location: index.php?url=feed');
        exit;
    }

    public function like(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        $result = $this->likeModel->toggle($postId, $userId);
        
        // Create notification if liked (not unliked)
        if ($result['liked']) {
            $post = $this->postModel->getById($postId);
            if ($post && $post['user_id'] != $userId) {
                require_once __DIR__ . '/NotificationController.php';
                NotificationController::create(
                    $post['user_id'],
                    'like',
                    $userId,
                    $postId,
                    $_SESSION['full_name'] . ' liked your post'
                );
            }
        }
        
        echo json_encode($result);
        exit;
    }

    public function addComment(): void {
        $postId  = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = $_SESSION['user_id'];

        if (!empty($content)) {
            $this->commentModel->create($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
            
            // Create notification
            $post = $this->postModel->getById($postId);
            if ($post && $post['user_id'] != $userId) {
                require_once __DIR__ . '/NotificationController.php';
                NotificationController::create(
                    $post['user_id'],
                    'comment',
                    $userId,
                    $postId,
                    $_SESSION['full_name'] . ' commented on your post'
                );
            }
        }

        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function updateComment(): void {
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $content   = trim($_POST['content'] ?? '');
        $userId    = $_SESSION['user_id'];

        if (!empty($content)) {
            $this->commentModel->update($commentId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        }

        header('Location: index.php?url=feed');
        exit;
    }

    public function deleteComment(): void {
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $postId    = (int) ($_POST['post_id'] ?? 0);
        $userId    = $_SESSION['user_id'];
        $this->commentModel->delete($commentId, $userId);
        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function search(): void {
        $query         = trim($_GET['q'] ?? '');
        $currentUserId = $_SESSION['user_id'];
        $posts         = $query ? $this->postModel->search($query, $currentUserId) : [];
        $users         = $query ? $this->userModel->search($query) : [];
        
        // Return JSON for AJAX requests (for message user search)
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['users' => $users, 'posts' => $posts]);
            exit;
        }
        
        require __DIR__ . '/../views/posts/search.php';
    }

    public function saved(): void {
        $userId = $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();
        
        // Get saved posts
        $stmt = $db->prepare("
            SELECT p.*, u.username, u.full_name, u.profile_image,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked
            FROM saved_posts sp
            JOIN posts p ON sp.post_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE sp.user_id = ?
            ORDER BY sp.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $savedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pageTitle = 'Saved Posts | Nexo';
        require __DIR__ . '/../views/posts/saved.php';
    }

    public function save(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$userId, $postId]);
            echo json_encode(['success' => true, 'saved' => true]);
        } catch (PDOException $e) {
            // Already saved
            echo json_encode(['success' => true, 'saved' => true, 'message' => 'Already saved']);
        }
        exit;
    }

    public function unsave(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$userId, $postId]);
        echo json_encode(['success' => true, 'saved' => false]);
        exit;
    }

    public function toggleSave(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        $db = Database::getInstance()->getConnection();
        
        // Check if already saved
        $stmt = $db->prepare("SELECT id FROM saved_posts WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$userId, $postId]);
        $saved = $stmt->fetch();
        
        if ($saved) {
            // Unsave
            $stmt = $db->prepare("DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$userId, $postId]);
            echo json_encode(['success' => true, 'saved' => false]);
        } else {
            // Save
            $stmt = $db->prepare("INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$userId, $postId]);
            echo json_encode(['success' => true, 'saved' => true]);
        }
        exit;
    }

    // CHANGE: Make sure public/assets/uploads/ is writable on your server (chmod 777 on InfinityFree)
    private function handleImageUpload(array $file): string|false {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed) || $file['size'] > $maxSize) {
            return false;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('post_', true) . '.' . $ext;
        $dest     = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return $filename;
    }
}