<?php if (isset($_SESSION['user_id'])): ?>
            </main>

            <!-- RIGHT SIDEBAR -->
            <?php if (!isset($hideRightSidebar)): ?>
            <aside class="right-sidebar">
                <h3 class="right-sidebar-title">Suggested for you</h3>
                <?php if (!empty($suggestions)): ?>
                    <?php foreach ($suggestions as $s): ?>
                    <a href="index.php?url=profile/<?= htmlspecialchars($s['username']) ?>" class="suggestion-item">
                        <img src="assets/uploads/<?= htmlspecialchars($s['profile_image']) ?>"
                             alt="avatar" class="avatar-sm"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div>
                            <p class="suggestion-name"><?= htmlspecialchars($s['full_name']) ?></p>
                            <p class="suggestion-username">@<?= htmlspecialchars($s['username']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </aside>
            <?php endif; ?>

        </div><!-- end content-area -->

        <!-- MOBILE BOTTOM NAV -->
        <nav class="mobile-nav">
            <?php $currentUrl = $_GET['url'] ?? 'feed'; $username = $_SESSION['username']; ?>
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
            <a href="#" onclick="toggleNotifications(event)" class="mobile-nav-item" style="position:relative">
                <i class="fa fa-bell"></i>
                <span class="mobile-badge notif-count" style="display:none"></span>
            </a>
            <a href="index.php?url=profile/<?= htmlspecialchars($username) ?>" class="mobile-nav-item <?= str_starts_with($currentUrl, 'profile') ? 'active' : '' ?>">
                <i class="fa fa-user"></i>
            </a>
        </nav>

    </div><!-- end main-wrapper -->
</div><!-- end app-shell -->
<?php endif; ?>

<script src="assets/js/app.js"></script>
</body>
</html>