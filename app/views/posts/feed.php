<?php
$pageTitle = 'Feed – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="feed-wrap">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- ── CREATE POST ── -->
    <div class="compose-card">
        <form action="index.php?url=post/create" method="POST" enctype="multipart/form-data">

            <div class="compose-top">
                <img src="assets/uploads/<?= htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') ?>"
                     alt="you" class="avatar-md"
                     onerror="this.onerror=null; this.src='assets/images/default.png'">
                <input type="text" name="content" class="compose-input"
                       placeholder="What's on your mind?" required>
                <label class="compose-add-btn" title="Add photo">
                    <i class="fa fa-plus"></i>
                    <input type="file" name="image" accept="image/*" hidden
                           onchange="previewPostImage(this)">
                </label>
            </div>

            <div id="image-previews" class="image-previews"></div>

            <div class="compose-footer">
                <div class="compose-actions">
                    <label class="compose-action-btn">
                        <i class="fa fa-image green"></i> Photo
                        <input type="file" name="image" accept="image/*" hidden onchange="previewPostImage(this)">
                    </label>
                    <label class="compose-action-btn">
                        <i class="fa fa-video red"></i> Video
                        <input type="file" name="image" accept="image/*" hidden onchange="previewPostImage(this)">
                    </label>
                    <button type="button" class="compose-action-btn">
                        <i class="fa fa-face-smile yellow"></i> Feeling
                    </button>
                </div>
                <button type="submit" class="btn btn-primary btn-rounded btn-sm">Post</button>
            </div>

        </form>
    </div>

    <!-- ── POSTS ── -->
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <i class="fa fa-newspaper"></i>
            <h3>No posts yet</h3>
            <p>Be the first to share something!</p>
        </div>
    <?php else: ?>

        <?php
        require_once __DIR__ . '/../../models/CommentModel.php';
        $cm = new CommentModel();
        ?>

        <?php foreach ($posts as $post): ?>
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

                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
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
                <button class="reaction-btn save-btn" onclick="toggleSave(<?= $post['id'] ?>, this)" title="Save post"
                        data-saved="<?= !empty($post['user_saved']) ? '1' : '0' ?>">
                    <i class="<?= !empty($post['user_saved']) ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                </button>
            </div>

            <!-- Comments -->
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
                            <button class="comment-action-btn">Like</button>
                            <button class="comment-action-btn">Reply</button>
                            <?php if ($c['user_id'] == $_SESSION['user_id']): ?>
                                <button class="comment-action-btn"
                                        onclick="openEditComment(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['content'])) ?>)">
                                    Edit
                                </button>
                                <form action="index.php?url=comment/delete" method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete comment?')">
                                    <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="post_id"    value="<?= $post['id'] ?>">
                                    <button type="submit" class="comment-action-btn danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Add comment -->
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
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-full"
                        onclick="closeModal('delete-post-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger btn-full">Yes, delete</button>
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

<?php require __DIR__ . '/../partials/footer.php'; ?>