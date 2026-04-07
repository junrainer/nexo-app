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
        <?php
        require_once __DIR__ . '/../../models/PostMediaModel.php';
        $mediaModel = new PostMediaModel();
        ?>
        <div class="gm-saved-grid">
            <?php foreach ($savedPosts as $post): ?>
            <?php
                $mediaItems  = $mediaModel->getByPost($post['id']);
                $firstMedia  = $mediaItems[0] ?? null;
                // Fallback to legacy image column
                if (!$firstMedia && !empty($post['image'])) {
                    $firstMedia = ['filename' => $post['image'], 'media_type' => 'image'];
                }
            ?>
            <div class="gm-saved-card" id="saved-<?= $post['id'] ?>">
                <button class="gm-saved-x" onclick="unsavePost(<?= $post['id'] ?>, this)" title="Remove from saved">
                    <i class="fa fa-xmark"></i>
                </button>
                <div class="gm-saved-clickable" onclick="navigateToSavedPost(<?= $post['id'] ?>)">
                    <?php if ($firstMedia): ?>
                    <div class="gm-saved-img">
                        <?php if ($firstMedia['media_type'] === 'video'): ?>
                            <video src="assets/uploads/<?= htmlspecialchars($firstMedia['filename']) ?>"
                                   preload="metadata" muted style="width:100%;height:100%;object-fit:cover;"></video>
                        <?php else: ?>
                            <img src="assets/uploads/<?= htmlspecialchars($firstMedia['filename']) ?>"
                                 alt="post image"
                                 onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                        <?php endif; ?>
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
                                <?= htmlspecialchars($post['full_name']) ?> · <time class="live-time" data-time="<?= htmlspecialchars($post['created_at']) ?>"><?= time_ago($post['created_at']) ?></time>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>