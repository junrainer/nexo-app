<?php
$pageTitle = htmlspecialchars($user['full_name']) . ' – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="profile-wrap">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error" style="margin:16px 24px;">
            <i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success" style="margin:16px 24px;">
            <i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Cover -->
    <div class="profile-cover">
        <?php if ($isOwner): ?>
            <button class="cover-btn">
                <i class="fa fa-camera"></i>
                Add cover photo
            </button>
        <?php endif; ?>
        <!-- Avatar -->
        <div class="profile-avatar-wrap">
            <img src="assets/uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                 alt="avatar" class="profile-avatar-img"
                 onerror="this.onerror=null; this.src='assets/images/default.png'">
        </div>
    </div>

    <!-- Profile info -->
    <div class="profile-info">
        <div>
            <h1 class="profile-name"><?= htmlspecialchars($user['full_name']) ?></h1>
            <p class="profile-username">@<?= htmlspecialchars($user['username']) ?></p>
            <p class="profile-bio"><?= htmlspecialchars($user['bio'] ?? '') ?></p>
        </div>
        <?php if ($isOwner): ?>
            <button class="btn btn-ghost btn-sm" onclick="openEditProfile()">
                <i class="fa fa-pen" style="font-size:12px;"></i> Edit
            </button>
        <?php else: ?>
            <div class="profile-actions">
                <a href="index.php?url=message/start&user=<?= $user['id'] ?>" class="btn btn-ghost btn-sm">
                    <i class="fa fa-comment"></i> Message
                </a>
                <?php if ($friendshipStatus === 'friends'): ?>
                    <button class="btn btn-ghost btn-sm" id="friend-btn-<?= $user['id'] ?>" onclick="handleUnfriend(<?= $user['id'] ?>)">
                        <i class="fa fa-user-check"></i> <span>Friends</span>
                    </button>
                <?php elseif ($friendshipStatus === 'pending_sent'): ?>
                    <button class="btn btn-ghost btn-sm" id="friend-btn-<?= $user['id'] ?>" disabled>
                        <i class="fa fa-clock"></i> <span>Pending</span>
                    </button>
                <?php elseif ($friendshipStatus === 'pending_received'): ?>
                    <button class="btn btn-primary btn-sm" id="friend-btn-<?= $user['id'] ?>" onclick="handleAcceptRequest(<?= $user['id'] ?>)">
                        <i class="fa fa-user-plus"></i> <span>Accept Request</span>
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary btn-sm" id="friend-btn-<?= $user['id'] ?>" onclick="handleFriendAction(<?= $user['id'] ?>)">
                        <i class="fa fa-user-plus"></i> <span>Add Friend</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="profile-stats">
        <div class="stat-item">
            <span class="stat-num"><?= count($posts) ?></span>
            <span class="stat-lbl">Posts</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $friendCount ?></span>
            <span class="stat-lbl">Friends</span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab-list">
            <button class="tab-btn active" data-tab="posts" onclick="switchTab('posts')">Posts</button>
            <button class="tab-btn" data-tab="photos" onclick="switchTab('photos')">Photos</button>
        </div>

        <!-- Posts tab -->
        <div class="tab-content active tab-posts" id="tab-posts">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <i class="fa fa-pen-to-square"></i>
                    <p>No posts yet.</p>
                </div>
            <?php else: ?>
                <?php
                require_once __DIR__ . '/../../models/CommentModel.php';
                $cm = new CommentModel();
                ?>
                <?php foreach ($posts as $post): ?>
                <?php $comments = $cm->getByPost($post['id']); ?>

                <div class="post-card" id="post-<?= $post['id'] ?>">
                    <div class="post-header">
                        <img src="assets/uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                             alt="avatar" class="avatar-md"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div class="post-author-info">
                            <span class="post-author-name"><?= htmlspecialchars($user['username']) ?></span>
                            <span class="post-author-meta">
                                <?= htmlspecialchars($user['full_name']) ?> · <?= time_ago($post['created_at']) ?>
                            </span>
                        </div>
                        <?php if ($isOwner): ?>
                        <div style="position:relative;">
                            <button class="post-menu-btn" onclick="togglePostMenu(<?= $post['id'] ?>)">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <div class="post-dropdown" id="post-menu-<?= $post['id'] ?>">
                                <button onclick="openEditPost(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['content'])) ?>)">
                                    <i class="fa fa-pen"></i> Edit post
                                </button>
                                <button class="danger-item" onclick="confirmDeletePost(<?= $post['id'] ?>)">
                                    <i class="fa fa-trash"></i> Delete post
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-body">
                        <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        <?php if ($post['image']): ?>
                            <img src="assets/uploads/<?= htmlspecialchars($post['image']) ?>"
                                 alt="post image" class="post-image">
                        <?php endif; ?>
                    </div>

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
                        <button class="reaction-btn save-btn" onclick="toggleSave(<?= $post['id'] ?>, this)" title="Save post"
                                data-saved="<?= !empty($post['user_saved']) ? '1' : '0' ?>">
                            <i class="<?= !empty($post['user_saved']) ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                        </button>
                    </div>

                    <div class="comments-section" id="comments-<?= $post['id'] ?>" style="display:none;">
                        <?php foreach ($comments as $c): ?>
                        <div class="comment-row">
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
                                    <?php if ($c['user_id'] == $_SESSION['user_id']): ?>
                                        <button class="comment-action-btn"
                                                onclick="openEditComment(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['content'])) ?>)">Edit</button>
                                        <form action="index.php?url=comment/delete" method="POST"
                                              style="display:inline" onsubmit="return confirm('Delete comment?')">
                                            <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                            <input type="hidden" name="post_id"    value="<?= $post['id'] ?>">
                                            <button type="submit" class="comment-action-btn danger">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <form action="index.php?url=comment/add" method="POST" class="comment-input-row">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
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

        <!-- Photos tab -->
        <div class="tab-content" id="tab-photos">
            <?php $photoPosts = array_filter($posts, fn($p) => !empty($p['image'])); ?>
            <?php if (empty($photoPosts)): ?>
                <div class="empty-state">
                    <i class="fa fa-images"></i>
                    <p>No photos yet.</p>
                </div>
            <?php else: ?>
                <div class="photos-grid">
                    <?php foreach ($photoPosts as $p): ?>
                    <div class="photo-cell">
                        <img src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" alt="photo">
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── EDIT PROFILE MODAL ── -->
<?php if ($isOwner): ?>
<div class="modal-overlay" id="edit-profile-modal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit profile</span>
            <button class="modal-close" onclick="closeModal('edit-profile-modal')">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
        <form action="index.php?url=profile/update" method="POST" enctype="multipart/form-data">
            <div class="modal-body">

                <!-- Avatar upload -->
                <div class="avatar-upload-row">
                    <img src="assets/uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                         alt="avatar" class="avatar-lg" id="avatar-preview"
                         onerror="this.onerror=null; this.src='assets/images/default.png'"
                         style="width:64px;height:64px;">
                    <div>
                        <p style="font-size:13.5px;font-weight:500;margin-bottom:6px;">Profile photo</p>
                        <label class="btn btn-ghost btn-sm" style="cursor:pointer;">
                            <i class="fa fa-upload" style="font-size:12px;"></i> Upload photo
                            <input type="file" name="profile_image" accept="image/*" hidden
                                   onchange="previewAvatar(this)">
                        </label>
                    </div>
                </div>

                <div class="modal-field">
                    <label>Full name</label>
                    <input type="text" name="full_name" class="modal-input"
                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="modal-field">
                    <label>Username</label>
                    <input type="text" name="username" class="modal-input"
                           value="<?= htmlspecialchars($user['username']) ?>">
                </div>

                <div class="modal-field">
                    <label>Bio</label>
                    <textarea name="bio" rows="3" class="modal-textarea"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <div class="modal-field">
                    <label>Mobile</label>
                    <input type="tel" name="mobile" class="modal-input"
                           value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" placeholder="+63 900 000 0000">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="modal-field">
                        <label>Birthday</label>
                        <input type="date" name="birthday" class="modal-input"
                               value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                    </div>
                    <div class="modal-field">
                        <label>Gender</label>
                        <select name="gender" class="modal-input">
                            <option value="">Prefer not to say</option>
                            <option value="Male"   <?= ($user['gender'] ?? '') === 'Male'   ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other"  <?= ($user['gender'] ?? '') === 'Other'  ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-full">Save changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── EDIT POST MODAL ── -->
<div class="modal-overlay" id="edit-post-modal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Post</span>
            <button class="modal-close" onclick="closeModal('edit-post-modal')">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
        <form action="index.php?url=post/update" method="POST">
            <input type="hidden" name="post_id" id="edit-post-id">
            <div class="modal-body">
                <textarea name="content" id="edit-post-content" rows="4"
                          class="modal-textarea" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm"
                        onclick="closeModal('edit-post-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ── DELETE POST MODAL ── -->
<div class="modal-overlay" id="delete-post-modal" style="display:none;">
    <div class="modal">
        <form action="index.php?url=post/delete" method="POST">
            <input type="hidden" name="post_id" id="delete-post-id">
            <div class="modal-body centered">
                <div class="delete-modal-icon"><i class="fa fa-trash"></i></div>
                <h3>Delete post?</h3>
                <p>This will permanently remove your post. This action cannot be undone.</p>
            </div>
            <div class="modal-footer" style="flex-direction:column;">
                <button type="submit" class="btn btn-danger btn-full">Yes, delete</button>
                <button type="button" class="btn btn-ghost btn-full"
                        onclick="closeModal('delete-post-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ── EDIT COMMENT MODAL ── -->
<div class="modal-overlay" id="edit-comment-modal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Comment</span>
            <button class="modal-close" onclick="closeModal('edit-comment-modal')">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
        <form action="index.php?url=comment/update" method="POST">
            <input type="hidden" name="comment_id" id="edit-comment-id">
            <div class="modal-body">
                <textarea name="content" id="edit-comment-content" rows="3"
                          class="modal-textarea" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm"
                        onclick="closeModal('edit-comment-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleFriendAction(userId) {
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    
    btn.disabled = true;
    
    fetch('index.php?url=friend/request', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'friend_id=' + userId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fa fa-clock"></i> <span>Pending</span>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-ghost');
        } else {
            btn.disabled = false;
            alert(data.message || 'Error sending request');
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Friend request error:', err);
    });
}

function handleUnfriend(userId) {
    if (!confirm('Are you sure you want to unfriend this person?')) return;
    
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    
    btn.disabled = true;
    
    fetch('index.php?url=friend/unfriend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'friend_id=' + userId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fa fa-user-plus"></i> <span>Add Friend</span>';
            btn.classList.remove('btn-ghost');
            btn.classList.add('btn-primary');
            btn.disabled = false;
            btn.onclick = function() { handleFriendAction(userId); };
        } else {
            btn.disabled = false;
            alert(data.message || 'Error unfriending');
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Unfriend error:', err);
    });
}

function handleAcceptRequest(userId) {
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    
    btn.disabled = true;
    
    fetch('index.php?url=friend/accept', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'friend_id=' + userId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fa fa-user-check"></i> <span>Friends</span>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-ghost');
            btn.onclick = function() { handleUnfriend(userId); };
            btn.disabled = false;
        } else {
            btn.disabled = false;
            alert(data.message || 'Error accepting request');
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Accept request error:', err);
    });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>