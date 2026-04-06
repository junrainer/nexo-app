<?php
$pageTitle = 'Saved Posts – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="gm-page">

    <div class="gm-page-header">
        <i class="fa fa-bookmark" style="color:var(--primary);"></i>
        <h1>Saved Posts</h1>
    </div>

    <?php if (empty($savedPosts)): ?>
        <div class="gm-empty">
            <i class="fa fa-bookmark"></i>
            <p>No saved posts yet</p>
        </div>
    <?php else: ?>
        <div class="gm-saved-grid">
            <?php foreach ($savedPosts as $post): ?>
            <div class="gm-saved-card" id="saved-<?= $post['id'] ?>">
                <?php if ($post['image']): ?>
                <div class="gm-saved-img">
                    <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>"
                         alt="post image"
                         onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                </div>
                <?php else: ?>
                <div class="gm-saved-img gm-saved-no-img">
                    <i class="fa fa-file-lines"></i>
                </div>
                <?php endif; ?>

                <div class="gm-saved-body">
                    <div class="gm-saved-info">
                        <p class="gm-saved-title"><?= htmlspecialchars(mb_strimwidth($post['content'], 0, 60, '…')) ?></p>
                        <p class="gm-saved-meta">
                            <?= htmlspecialchars($post['full_name']) ?> · <?= time_ago($post['created_at']) ?>
                        </p>
                    </div>
                    <button class="gm-unsave-btn" onclick="unsavePost(<?= $post['id'] ?>, this)" title="Remove from saved">
                        <i class="fa fa-bookmark-slash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
function unsavePost(postId, btn) {
    const fd = new FormData();
    fd.append('post_id', postId);
    fetch('index.php?url=post/unsave', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('saved-' + postId);
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