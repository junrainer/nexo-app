<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/PostMediaModel.php';
require_once __DIR__ . '/../models/CommentModel.php';
require_once __DIR__ . '/../models/LikeModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class PostController {
    private PostModel      $postModel;
    private PostMediaModel $postMediaModel;
    private CommentModel   $commentModel;
    private LikeModel      $likeModel;
    private UserModel      $userModel;
    private $db;

    /** Seconds a user must wait between creating posts (anti-spam). */
    private const POST_COOLDOWN = 20;

    /** Seconds a user must wait between posting comments (anti-spam). */
    private const COMMENT_COOLDOWN = 15;

    /** Maximum video upload size in GB. */
    private const MAX_VIDEO_SIZE_GB = 10;

    /** Maximum number of photos per post. */
    private const MAX_PHOTOS = 5;

    public function __construct() {
        $this->postModel      = new PostModel();
        $this->postMediaModel = new PostMediaModel();
        $this->commentModel   = new CommentModel();
        $this->likeModel      = new LikeModel();
        $this->userModel      = new UserModel();
        $this->db             = Database::getInstance()->getConnection();
    }

    public function feed(): void {
        $currentUserId = $_SESSION['user_id'];

        try {
            $posts = $this->postModel->getAllForFeed($currentUserId);
        } catch (PDOException $e) {
            error_log('PostController::feed getAllForFeed error: ' . $e->getMessage());
            $posts = [];
        }

        try {
            $suggestions = $this->userModel->getSuggestions($currentUserId);
        } catch (PDOException $e) {
            error_log('PostController::feed getSuggestions error: ' . $e->getMessage());
            $suggestions = [];
        }

        $sidebarContacts = $this->userModel->getSidebarContacts($currentUserId);

        $pageTitle = 'Feed – Nexo';
        require __DIR__ . '/../views/posts/feed.php';
    }

    public function create(): void {
        $content    = trim($_POST['content'] ?? '');
        $userId     = $_SESSION['user_id'];
        $visibility = $_POST['visibility'] ?? 'public';

        $imageFiles   = $_FILES['images'] ?? null;
        $imageNames   = [];
        $imageErrors  = [];
        $imageTypes   = [];
        $imageTmpNames = [];
        $imageSizes   = [];
        $imageIndexes = [];

        if ($imageFiles && !empty($imageFiles['name'])) {
            $imageNames   = is_array($imageFiles['name']) ? $imageFiles['name'] : [$imageFiles['name']];
            $imageErrors  = is_array($imageFiles['error']) ? $imageFiles['error'] : [$imageFiles['error']];
            $imageTypes   = is_array($imageFiles['type']) ? $imageFiles['type'] : [$imageFiles['type']];
            $imageTmpNames = is_array($imageFiles['tmp_name']) ? $imageFiles['tmp_name'] : [$imageFiles['tmp_name']];
            $imageSizes   = is_array($imageFiles['size']) ? $imageFiles['size'] : [$imageFiles['size']];

            foreach ($imageNames as $idx => $name) {
                if ($name !== '') {
                    $imageIndexes[] = $idx;
                }
            }
        }

        $hasImages = false;
        $firstValidImageIndex = null;
        foreach ($imageIndexes as $idx) {
            if (($imageErrors[$idx] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $hasImages = true;
                $firstValidImageIndex = $idx;
                break;
            }
        }

        $hasVideo  = !empty($_FILES['video']['name']) && $_FILES['video']['error'] === UPLOAD_ERR_OK;

        if (empty($content) && !$hasImages && !$hasVideo) {
            $_SESSION['error'] = 'Post cannot be empty.';
            header('Location: index.php?url=feed');
            exit;
        }

        // Anti-spam: enforce cooldown between posts
        $skipCooldownCheck = false;
        if (!empty($_SESSION['post_cooldown_until'])) {
            $cooldownUntil = (int) $_SESSION['post_cooldown_until'];
            if (time() < $cooldownUntil) {
                header('Location: index.php?url=feed');
                exit;
            }
            unset($_SESSION['post_cooldown_until']);
            $skipCooldownCheck = true;
        }

        if (!$skipCooldownCheck) {
            $lastPost = $this->postModel->getLastByUser($userId);
            if ($lastPost) {
                $secondsSince = $lastPost['seconds_since'] ?? null;
                if ($secondsSince !== null) {
                    $secondsSince = (int) $secondsSince;
                }
                if ($secondsSince === null) {
                    $lastPostTime = strtotime($lastPost['created_at']);
                    if ($lastPostTime !== false) {
                        $secondsSince = time() - $lastPostTime;
                    }
                }

                if ($secondsSince !== null) {
                    if ($secondsSince < 0) {
                        $wait = self::POST_COOLDOWN;
                    } else {
                        $wait = self::POST_COOLDOWN - $secondsSince;
                    }
                    if ($wait > 0) {
                        $_SESSION['post_cooldown_until'] = time() + $wait;
                        header('Location: index.php?url=feed');
                        exit;
                    }
                }
            }
        }

        $legacyImage = null;
        if ($hasImages && empty($_FILES['video']['name']) && $firstValidImageIndex !== null) {
            $legacyImage = $this->handleImageUpload([
                'name'     => $imageNames[$firstValidImageIndex] ?? '',
                'type'     => $imageTypes[$firstValidImageIndex] ?? '',
                'tmp_name' => $imageTmpNames[$firstValidImageIndex] ?? '',
                'error'    => $imageErrors[$firstValidImageIndex] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $imageSizes[$firstValidImageIndex] ?? 0,
            ]);
            if ($legacyImage === false) {
                $legacyImage = null;
            }
        }

        $postId = $this->postModel->create($userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $legacyImage, $visibility);

        // Handle multiple images (max 5)
        if (!empty($imageIndexes)) {
            $selectedCount = count($imageIndexes);
            if ($selectedCount > self::MAX_PHOTOS) {
                $_SESSION['warning'] = 'Only the first ' . self::MAX_PHOTOS . ' photos were uploaded. Extra photos were ignored.';
            }
            $imageIndexes = array_slice($imageIndexes, 0, self::MAX_PHOTOS);
            $order     = 0;
            foreach ($imageIndexes as $idx) {
                if (($imageErrors[$idx] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                $file = [
                    'name'     => $imageNames[$idx] ?? '',
                    'type'     => $imageTypes[$idx] ?? '',
                    'tmp_name' => $imageTmpNames[$idx] ?? '',
                    'error'    => $imageErrors[$idx] ?? UPLOAD_ERR_NO_FILE,
                    'size'     => $imageSizes[$idx] ?? 0,
                ];
                if ($order === 0 && $legacyImage !== null) {
                    $filename = $legacyImage;
                } else {
                    $filename = $this->handleImageUpload($file);
                }
                if ($filename) {
                    $this->postMediaModel->create($postId, $filename, 'image', $order++);
                }
            }
        }

        // Handle video (MP4 / MOV, max 10 GB)
        if (!empty($_FILES['video']['name']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $filename = $this->handleVideoUpload($_FILES['video']);
            if ($filename === false) {
                $_SESSION['error'] = 'Invalid video. Use MP4 or MOV format (max ' . self::MAX_VIDEO_SIZE_GB . ' GB).';
                $this->postModel->delete($postId, $userId);
                header('Location: index.php?url=feed');
                exit;
            }
            $this->postMediaModel->create($postId, $filename, 'video', 0);
        }

        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function update(): void {
        $postId     = (int) ($_POST['post_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');
        $userId     = $_SESSION['user_id'];
        $visibility = $_POST['visibility'] ?? 'public';

        if ($postId && !empty($content)) {
            $this->postModel->update($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $visibility);
            $_SESSION['toast_success'] = 'Post updated.';
        }

        header('Location: index.php?url=feed#post-' . $postId);
        exit;
    }

    public function delete(): void {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Remove media files from disk before deleting DB records
        $post = $this->postModel->getById($postId);
        if ($post && $post['user_id'] == $userId) {
            $uploadDir = __DIR__ . '/../../public/assets/uploads/';

            // Legacy single image
            if (!empty($post['image'])) {
                $path = $uploadDir . $post['image'];
                if (is_file($path)) unlink($path);
            }

            // post_media records
            $mediaFiles = $this->postMediaModel->deleteByPost($postId);
            foreach ($mediaFiles as $filename) {
                $path = $uploadDir . $filename;
                if (is_file($path)) unlink($path);
            }
        }

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

        if (!$postId || empty($content)) {
            header('Location: index.php?url=feed');
            exit;
        }

        // Anti-spam: enforce cooldown between comments
        $last = $this->commentModel->getLastByUser($userId);
        if ($last) {
            $elapsed = time() - strtotime($last['created_at']);
            if ($elapsed < self::COMMENT_COOLDOWN) {
                $wait = self::COMMENT_COOLDOWN - $elapsed;
                $unit = $wait === 1 ? 'second' : 'seconds';
                $_SESSION['error'] = "Please wait {$wait} {$unit} before posting another comment.";
                $ref = $_SERVER['HTTP_REFERER'] ?? '';
                if ($ref && strpos($ref, 'profile') !== false) {
                    header('Location: ' . $ref . '#post-' . $postId);
                } else {
                    header('Location: index.php?url=feed#post-' . $postId);
                }
                exit;
            }
        }

        $this->commentModel->create($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

        $post = $this->postModel->getById($postId);
        if ($post && $post['user_id'] != $userId) {
            NotificationController::create(
                $post['user_id'], 'comment', $userId, $postId,
                $_SESSION['full_name'] . ' commented on your post'
            );
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

    public function commentLike(): void {
        header('Content-Type: application/json');
        $commentId = (int)($_POST['comment_id'] ?? 0);
        $userId    = (int)($_SESSION['user_id'] ?? 0);

        if ($commentId <= 0 || $userId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid comment']);
            exit;
        }

        try {
            $result = $this->commentModel->toggleLike($commentId, $userId);
            echo json_encode($result);
        } catch (PDOException $e) {
            error_log('PostController::commentLike error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Unable to react to comment']);
        }
        exit;
    }

    public function search(): void {
        $query         = trim($_GET['q'] ?? '');
        $currentUserId = $_SESSION['user_id'];
        $friendsOnly   = !empty($_GET['friends']) && $_GET['friends'] !== '0';
        $posts         = ($query && !$friendsOnly) ? $this->postModel->search($query, $currentUserId) : [];
        $users         = $query ? $this->userModel->search($query, $currentUserId, $friendsOnly) : [];

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
            $savedPosts = $this->postModel->getSaved($userId);
        } catch (PDOException $e) {
            $savedPosts = [];
        }

        try {
            $suggestions = $this->userModel->getSuggestions($userId);
        } catch (PDOException $e) {
            $suggestions = [];
        }

        $sidebarContacts = $this->userModel->getSidebarContacts($userId);

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

    /**
     * Handles a single video upload.
     * Accepted formats: MP4 (video/mp4) and MOV (video/quicktime).
     * Max file size: 10 GB.
     * Note: ensure php.ini upload_max_filesize and post_max_size are set to at least 10G.
     */
    private function handleVideoUpload(array $file): string|false {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;

        $allowedMime = ['video/mp4', 'video/quicktime'];
        $maxSize     = self::MAX_VIDEO_SIZE_GB * 1024 * 1024 * 1024;

        if (!in_array($file['type'], $allowedMime) || $file['size'] > $maxSize) return false;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'mov'])) return false;

        $filename = uniqid('post_video_', true) . '.' . $ext;
        $dest     = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

        return $filename;
    }
}
