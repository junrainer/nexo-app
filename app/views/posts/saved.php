<?php
$pageTitle = 'Saved Posts – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="saved-page">
    
    <div class="page-header">
        <h1><i class="fa fa-bookmark"></i> Saved Posts</h1>
    </div>

    <div class="feed-wrap">
        <?php if (empty($savedPosts)): ?>
            <div class="empty-state">
                <i class="fa fa-bookmark"></i>
                <h3>No saved posts</h3>
                <p>Posts you save will appear here for easy access later.</p>
            </div>
        <?php else: ?>
            <?php
            require_once __DIR__ . '/../../models/CommentModel.php';
            $cm = new CommentModel();
            ?>

            <?php foreach ($savedPosts as $post): ?>
            <?php $comments = $cm->getByPost($post['id']); ?>

            <div class="post-card" id="post-<?= $post['id'] ?>">

                <!-- Header -->
                <div class="post-header">
                    <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>">
                        <img src="assets/uploads/<?= htmlspecialchars($post['profile_image']) ?>"
                             alt="avatar" class="avatar-md"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </a>
                    <div class="post-author-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>" class="post-author-name">
                            <?= htmlspecialchars($post['username']) ?>
                        </a>
                        <span class="post-author-meta">
                            <?= htmlspecialchars($post['full_name']) ?> · <?= time_ago($post['created_at']) ?>
                        </span>
                    </div>

                    <button class="post-menu-btn saved-btn" onclick="unsavePost(<?= $post['id'] ?>, this)" title="Remove from saved">
                        <i class="fa fa-bookmark"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="post-body">
                    <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                    <?php if ($post['image']): ?>
                        <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>"
                             alt="post image" class="post-image">
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="post-footer">
                    <button class="reaction-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                            onclick="toggleLike(<?= $post['id'] ?>, this)">
                        <i class="<?= $post['user_liked'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                        <span class="like-count"><?= $post['like_count'] > 0 ? $post['like_count'] : '' ?></span>
                    </button>
                    <button class="reaction-btn" onclick="toggleComments(<?= $post['id'] ?>)">
                        <i class="fa-regular fa-comment"></i>
                        <span><?= $post['comment_count'] > 0 ? $post['comment_count'] : '' ?></span>
                    </button>
                    <button class="reaction-btn">
                        <i class="fa-regular fa-share-from-square"></i>
                    </button>
                </div>

                <!-- Comments Section -->
                <div class="comments-section" id="comments-<?= $post['id'] ?>" style="display:none;">
                    <?php foreach ($comments as $c): ?>
                    <div class="comment-row" id="comment-<?= $c['id'] ?>">
                        <img src="assets/uploads/<?= htmlspecialchars($c['profile_image']) ?>"
                             alt="avatar" class="avatar-sm"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div style="flex:1;">
                            <div class="comment-bubble">
                                <span class="comment-author"><?= htmlspecialchars($c['full_name']) ?></span>
                                <p class="comment-text"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                            </div>
                            <div class="comment-meta-row">
                                <span class="comment-time"><?= time_ago($c['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Add comment -->
                    <form action="index.php?url=comment/add" method="POST" class="comment-input-row">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="redirect" value="saved">
                        <img src="assets/uploads/<?= htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') ?>"
                             alt="you" class="avatar-sm"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div class="comment-input-wrap">
                            <input type="text" name="content" class="comment-input"
                                   placeholder="Write a comment..." required>
                            <button type="submit" class="comment-send-btn">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function unsavePost(postId, btn) {
    const fd = new FormData();
    fd.append('post_id', postId);
    
    fetch('index.php?url=post/unsave', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = btn.closest('.post-card');
                if (card) {
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
