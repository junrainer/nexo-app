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
    private $db;

    public function __construct() {
        $this->postModel    = new PostModel();
        $this->commentModel = new CommentModel();
        $this->likeModel    = new LikeModel();
        $this->userModel    = new UserModel();
        $this->db           = Database::getInstance()->getConnection();
    }

    public function feed(): void {
        $currentUserId = $_SESSION['user_id'];
        $posts         = $this->postModel->getAllForFeed($currentUserId);
        $suggestions   = $this->userModel->getSuggestions($currentUserId);
        $pageTitle     = 'Feed – Nexo';
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
                $_SESSION['error'] = 'Invalid image. Use JPG, PNG, GIF, or WEBP (max 5MB).';
                header('Location: index.php?url=feed');
                exit;
            }
        }

        $postId = $this->postModel->create($userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $image);
        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function update(): void {
        $postId  = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = $_SESSION['user_id'];

        if ($postId && !empty($content)) {
            $this->postModel->update($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
            $_SESSION['toast_success'] = 'Post updated.';
        }

        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function delete(): void {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        $this->postModel->delete($postId, $userId);
        $_SESSION['toast_success'] = 'Post deleted.';
        header('Location: index.php?url=feed');
        exit;
    }

    public function like(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        if (!$postId) {
            echo json_encode(['success' => false, 'error' => 'Invalid post']);
            exit;
        }

        $result = $this->likeModel->toggle($postId, $userId);

        // Fire notification when liked (not when unliked)
        if ($result['liked']) {
            $post = $this->postModel->getById($postId);
            if ($post && $post['user_id'] != $userId) {
                NotificationController::create(
                    $post['user_id'], 'like', $userId, $postId,
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

        if ($postId && !empty($content)) {
            $this->commentModel->create($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

            $post = $this->postModel->getById($postId);
            if ($post && $post['user_id'] != $userId) {
                NotificationController::create(
                    $post['user_id'], 'comment', $userId, $postId,
                    $_SESSION['full_name'] . ' commented on your post'
                );
            }
        }

        // Redirect back to the post anchor — works from feed AND profile
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if ($ref && strpos($ref, 'profile') !== false) {
            header('Location: ' . $ref . '#post-' . $postId);
        } else {
            header('Location: index.php?url=feed#post-' . $postId);
        }
        exit;
    }

    public function updateComment(): void {
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $content   = trim($_POST['content'] ?? '');
        $userId    = $_SESSION['user_id'];

        if ($commentId && !empty($content)) {
            $this->commentModel->update($commentId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? 'index.php?url=feed';
        header('Location: ' . $ref);
        exit;
    }

    public function deleteComment(): void {
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $postId    = (int) ($_POST['post_id'] ?? 0);
        $userId    = $_SESSION['user_id'];
        $this->commentModel->delete($commentId, $userId);

        $ref = $_SERVER['HTTP_REFERER'] ?? 'index.php?url=feed';
        header('Location: ' . $ref . '#post-' . $postId);
        exit;
    }

    public function search(): void {
        $query         = trim($_GET['q'] ?? '');
        $currentUserId = $_SESSION['user_id'];
        $posts         = $query ? $this->postModel->search($query, $currentUserId) : [];
        $users         = $query ? $this->userModel->search($query) : [];

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['users' => $users, 'posts' => $posts]);
            exit;
        }

        $pageTitle = 'Search – Nexo';
        require __DIR__ . '/../views/posts/search.php';
    }

    public function saved(): void {
        $userId = $_SESSION['user_id'];

        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.username, u.full_name, u.profile_image,
                       (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                       (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS user_liked,
                       1 AS user_saved
                FROM saved_posts sp
                JOIN posts p ON sp.post_id = p.id
                JOIN users u ON p.user_id = u.id
                WHERE sp.user_id = ?
                ORDER BY sp.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            $savedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $savedPosts = [];
        }

        $pageTitle = 'Saved – Nexo';
        require __DIR__ . '/../views/posts/saved.php';
    }

    public function save(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        if (!$postId) { echo json_encode(['success' => false]); exit; }

        try {
            $this->db->prepare('INSERT IGNORE INTO saved_posts (user_id, post_id) VALUES (?, ?)')
                     ->execute([$userId, $postId]);
            echo json_encode(['success' => true, 'saved' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => true, 'saved' => true]);
        }
        exit;
    }

    public function unsave(): void {
        header('Content-Type: application/json');
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        if (!$postId) { echo json_encode(['success' => false]); exit; }

        $this->db->prepare('DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?')
                 ->execute([$userId, $postId]);
        echo json_encode(['success' => true, 'saved' => false]);
        exit;
    }

    private function handleImageUpload(array $file): string|false {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed) || $file['size'] > $maxSize) return false;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('post_', true) . '.' . $ext;
        $dest     = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

        return $filename;
    }
}
