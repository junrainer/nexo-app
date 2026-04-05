<?php
$pageTitle = 'Search – Nexo';
$hideRightSidebar = true;

// Added: safe defaults to avoid undefined/count errors
$query = $query ?? '';
$users = (isset($users) && is_array($users)) ? $users : [];
$posts = (isset($posts) && is_array($posts)) ? $posts : [];

require __DIR__ . '/../partials/header.php';
?>

<div class="search-wrap">

    <!-- Search bar -->
    <form action="index.php" method="GET" class="search-bar">
        <input type="hidden" name="url" value="search">
        <i class="fa fa-search"></i>
        <input type="text" name="q" placeholder="Search..."
               value="<?= htmlspecialchars($query ?? '') ?>" autofocus>
        <?php if (!empty($query)): ?>
            <a href="index.php?url=search" class="search-clear">
                <i class="fa fa-xmark"></i>
            </a>
        <?php endif; ?>
    </form>

    <?php if (!empty($query)): ?>

        <p class="search-result-count">
            Results for "<?= htmlspecialchars($query) ?>" —
            <?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> found
        </p>

        <!-- Tabs -->
        <div class="tab-list" style="margin-bottom:20px;">
            <button class="tab-btn active" data-tab="users" onclick="switchTab('users')">
                Users (<?= count($users) ?>)
            </button>
            <button class="tab-btn" data-tab="posts" onclick="switchTab('posts')">
                Posts (<?= count($posts) ?>)
            </button>
        </div>

        <!-- Users tab -->
        <div class="tab-content active" id="tab-users">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fa fa-users"></i>
                    <p>No users found</p>
                </div>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <a href="index.php?url=profile/<?= htmlspecialchars($u['username']) ?>"
                   class="user-result-card">
                    <img src="assets/uploads/<?= htmlspecialchars($u['profile_image']) ?>"
                         alt="avatar" class="avatar-md"
                         onerror="this.onerror=null; this.src='assets/images/default.png'">
                    <div class="user-result-info">
                        <p class="user-result-name"><?= htmlspecialchars($u['full_name']) ?></p>
                        <p class="user-result-username">@<?= htmlspecialchars($u['username']) ?></p>
                    </div>
                    <button class="btn btn-primary btn-rounded btn-sm" onclick="event.preventDefault()">
                        Follow
                    </button>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Posts tab -->
        <div class="tab-content" id="tab-posts">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <i class="fa fa-file-lines"></i>
                    <p>No posts found</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <div class="post-card" style="margin-bottom:12px;">
                    <div class="post-header">
                        <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>">
                            <img src="assets/uploads/<?= htmlspecialchars($post['profile_image']) ?>"
                                 alt="avatar" class="avatar-md"
                                 onerror="this.onerror=null; this.src='assets/images/default.png'">
                        </a>
                        <div class="post-author-info">
                            <a href="index.php?url=profile/<?= htmlspecialchars($post['username']) ?>"
                               class="post-author-name">
                                <?= htmlspecialchars($post['username']) ?>
                            </a>
                            <span class="post-author-meta">
                                <?= htmlspecialchars($post['full_name']) ?> · <?= time_ago($post['created_at']) ?>
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
                        <button class="reaction-btn save-btn" onclick="toggleSave(<?= $post['id'] ?>, this)" title="Save post"
                                data-saved="<?= !empty($post['user_saved']) ? '1' : '0' ?>">
                            <i class="<?= !empty($post['user_saved']) ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <i class="fa fa-magnifying-glass"></i>
            <h3>Search for users or posts</h3>
            <p>Type in the search box above to find people or content.</p>
        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>