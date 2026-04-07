<?php
$pageTitle = 'Search – Nexo';
$hideRightSidebar = true;

$query = $query ?? '';
$users = (isset($users) && is_array($users)) ? $users : [];
$posts = (isset($posts) && is_array($posts)) ? $posts : [];

require __DIR__ . '/../partials/header.php';
?>

<div class="gm-page gm-search-page">

    <!-- Search bar -->
    <form action="index.php" method="GET" class="gm-search-bar">
        <input type="hidden" name="url" value="search">
        <i class="fa fa-search gm-search-icon"></i>
        <input type="text" name="q" class="gm-search-input"
               placeholder="Search..." value="<?= htmlspecialchars($query) ?>" autofocus>
        <?php if (!empty($query)): ?>
            <a href="index.php?url=search" class="gm-search-clear">
                <i class="fa fa-xmark"></i>
            </a>
        <?php endif; ?>
    </form>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($query)): ?>

        <p class="gm-search-count">
            Results for "<?= htmlspecialchars($query) ?>" — <?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> found
        </p>

        <!-- Tabs -->
        <div class="gm-tabs">
            <div class="gm-tabs-list">
                <button class="gm-tab active" data-tab="users" onclick="switchSearchTab('users', this)">
                    Users (<?= count($users) ?>)
                </button>
                <button class="gm-tab" data-tab="posts" onclick="switchSearchTab('posts', this)">
                    Posts (<?= count($posts) ?>)
                </button>
            </div>

            <!-- Users tab -->
            <div class="gm-tab-panel active" id="tab-users">
                <?php if (empty($users)): ?>
                    <div class="gm-empty">
                        <i class="fa fa-users"></i>
                        <p>No users found</p>
                    </div>
                <?php else: ?>
                    <div class="gm-list">
                        <?php foreach ($users as $u): ?>
                        <div class="gm-user-row">
                            <a href="index.php?url=profile/<?= htmlspecialchars($u['username']) ?>" class="gm-user-avatar">
                                <img src="assets/uploads/<?= htmlspecialchars($u['profile_image']) ?>"
                                     alt="avatar"
                                     onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                            </a>
                            <div class="gm-user-info">
                                <a href="index.php?url=profile/<?= htmlspecialchars($u['username']) ?>" class="gm-user-name">
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </a>
                                <?php
                                    $isFriend = !empty($u['is_friend']);
                                    $isOnline = !empty($u['is_online']);
                                    $metaParts = [
                                        '@' . $u['username'],
                                        $isFriend ? 'Friend' : 'Not friend',
                                        $isOnline ? 'Online' : 'Offline',
                                    ];
                                ?>
                                <p class="gm-user-handle"><?= htmlspecialchars(implode(' · ', $metaParts)) ?></p>
                            </div>
                            <button class="gm-btn-primary gm-btn-sm gm-btn-rounded">Follow</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Posts tab -->
            <div class="gm-tab-panel" id="tab-posts">
                <?php if (empty($posts)): ?>
                    <div class="gm-empty">
                        <i class="fa fa-file-lines"></i>
                        <p>No posts found</p>
                    </div>
                <?php else: ?>
                    <div class="gm-list">
                        <?php foreach ($posts as $post): ?>
                        <div class="post-card" style="margin-bottom:12px;">
                            <div class="post-header">
                                <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>">
                                    <img src="assets/uploads/<?= htmlspecialchars($post['profile_image']) ?>"
                                         alt="avatar" class="avatar-md"
                                         onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                                </a>
                                <div class="post-author-info">
                                    <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>" class="post-author-name">
                                        <?= htmlspecialchars($post['username']) ?>
                                    </a>
                                    <span class="post-author-meta">
                                        <?= htmlspecialchars($post['full_name']) ?> · <time class="live-time" data-time="<?= htmlspecialchars($post['created_at']) ?>"><?= time_ago($post['created_at']) ?></time>
                                    </span>
                                </div>
                            </div>
                            <div class="post-body">
                                <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            </div>
                            <div class="post-footer">
                                <button class="reaction-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                                        onclick="toggleLike(<?= $post['id'] ?>, this)">
                                    <i class="<?= $post['user_liked'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                    <span class="like-count"><?= $post['like_count'] > 0 ? $post['like_count'] : '' ?></span>
                                </button>
                                <span class="reaction-btn">
                                    <i class="fa-regular fa-comment"></i>
                                    <?= $post['comment_count'] > 0 ? $post['comment_count'] : '' ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>

        <div class="gm-empty" style="padding-top:60px;">
            <i class="fa fa-magnifying-glass"></i>
            <p>Search for users or posts</p>
        </div>

    <?php endif; ?>

</div>

<script>
function switchSearchTab(tabName, btn) {
    document.querySelectorAll('.gm-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.gm-tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
