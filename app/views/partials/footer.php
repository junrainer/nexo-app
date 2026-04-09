<?php if (isset($_SESSION['user_id'])): ?>
            </main>

            <!-- RIGHT SIDEBAR -->
            <?php if (!isset($hideRightSidebar)): ?>
            <?php $sidebarContacts = $sidebarContacts ?? []; ?>
            <aside class="right-sidebar">
                <div class="sidebar-half">
                    <h3 class="right-sidebar-title">Suggested for you</h3>
                    <?php if (!empty($suggestions)): ?>
                        <?php foreach ($suggestions as $s): ?>
                        <?php
                            $sidebarProfileUrl = 'index.php?url=profile/' . htmlspecialchars($s['username']);
                            $sidebarAvatarFile = htmlspecialchars($s['profile_image'] ?: 'default.png');
                            $sidebarAvatar = 'assets/uploads/' . $sidebarAvatarFile;
                        ?>
                        <div class="suggestion-item" id="sidebar-sug-<?= (int)$s['id'] ?>"
                             data-user-id="<?= (int)$s['id'] ?>"
                             data-username="<?= htmlspecialchars($s['username']) ?>"
                             data-full-name="<?= htmlspecialchars($s['full_name']) ?>"
                             data-avatar="<?= $sidebarAvatar ?>"
                             data-profile-url="<?= $sidebarProfileUrl ?>">
                            <a href="<?= $sidebarProfileUrl ?>" class="suggestion-item-link">
                                <img src="<?= $sidebarAvatar ?>"
                                     alt="avatar" class="avatar-sm"
                                     onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                                <div>
                                    <p class="suggestion-name"><?= htmlspecialchars($s['full_name']) ?></p>
                                    <p class="suggestion-username">@<?= htmlspecialchars($s['username']) ?></p>
                                </div>
                            </a>
                            <button class="sidebar-add-btn js-sidebar-add" title="Add Friend"
                                    data-user-id="<?= (int)$s['id'] ?>">
                                <i class="fa fa-user-plus"></i><span>Add</span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="sidebar-empty">No suggestions</p>
                    <?php endif; ?>
                </div>
                <div class="sidebar-half sidebar-half--contacts">
                    <h3 class="right-sidebar-title">Contacts</h3>
                    <?php if (!empty($sidebarContacts)): ?>
                        <?php foreach ($sidebarContacts as $c): ?>
                        <a href="index.php?url=profile/<?= htmlspecialchars($c['username']) ?>" class="suggestion-item">
                            <img src="assets/uploads/<?= htmlspecialchars($c['profile_image']) ?>"
                                 alt="avatar" class="avatar-sm"
                                 onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                            <div>
                                <p class="suggestion-name"><?= htmlspecialchars($c['full_name']) ?></p>
                                <p class="suggestion-username">@<?= htmlspecialchars($c['username']) ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="sidebar-empty">No contacts yet</p>
                    <?php endif; ?>
                </div>
            </aside>
            <?php endif; ?>

        </div><!-- end content-area -->

        <!-- Post media viewer -->
        <div class="modal-overlay media-viewer-overlay" id="post-media-viewer" style="display:none;">
            <div class="media-viewer" role="dialog" aria-modal="true" aria-label="Post media viewer">
                <button class="media-viewer-close" type="button" aria-label="Close" onclick="closePostMediaViewer()">
                    <i class="fa fa-xmark"></i>
                </button>
                <button class="media-viewer-nav media-viewer-prev" type="button" aria-label="Previous image" onclick="showPrevPostMedia()">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <img id="post-media-viewer-img" src="" alt="Post media">
                <button class="media-viewer-nav media-viewer-next" type="button" aria-label="Next image" onclick="showNextPostMedia()">
                    <i class="fa fa-chevron-right"></i>
                </button>
                <div class="media-viewer-count" id="post-media-viewer-count"></div>
            </div>
        </div>

    </div><!-- end main-wrapper -->
</div><!-- end app-shell -->

<!-- MOBILE BOTTOM NAV — outside app-shell so position:fixed is never broken -->
<?php if (isset($_SESSION['user_id'])): ?>
<?php $currentUrl = $_GET['url'] ?? 'feed'; $username = $_SESSION['username']; ?>
<nav class="mobile-nav">
    <a href="index.php?url=feed" class="mobile-nav-item <?= $currentUrl === 'feed' ? 'active' : '' ?>">
        <i class="fa fa-house"></i>
    </a>
    <a href="index.php?url=search" class="mobile-nav-item <?= $currentUrl === 'search' ? 'active' : '' ?>">
        <i class="fa fa-search"></i>
    </a>
    <a href="index.php?url=messages" class="mobile-nav-item <?= $currentUrl === 'messages' ? 'active' : '' ?>" style="position:relative">
        <i class="fa fa-comment-dots"></i>
        <span class="mobile-badge message-count" style="display:none"></span>
    </a>
</nav>
<?php endif; ?>
<?php endif; ?>

<script src="assets/js/app.js"></script>
</body>
</html>