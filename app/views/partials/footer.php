<?php if (isset($_SESSION['user_id'])): ?>
            </main>

            <!-- RIGHT SIDEBAR -->
            <?php if (!isset($hideRightSidebar)): ?>
            <?php
            $sidebarContacts = [];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT u.id, u.username, u.full_name, u.profile_image
                    FROM friendships f
                    JOIN users u ON f.friend_id = u.id
                    WHERE f.user_id = ? AND f.status = 'accepted'
                    ORDER BY u.full_name
                    LIMIT 10
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $sidebarContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { /* friendships table may not exist */ }

            $sidebarSuggestions = $suggestions ?? [];
            if (empty($sidebarSuggestions)) {
                try {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        SELECT u.id, u.username, u.full_name, u.profile_image
                        FROM users u
                        WHERE u.id != ?
                          AND u.id NOT IN (
                              SELECT friend_id FROM friendships WHERE user_id = ?
                              UNION
                              SELECT user_id  FROM friendships WHERE friend_id = ?
                          )
                        ORDER BY RAND()
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
                    $sidebarSuggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) { /* ignore */ }
            }
            ?>
            <aside class="right-sidebar">
                <div class="sidebar-half">
                    <h3 class="right-sidebar-title">Suggested for you</h3>
                    <?php if (!empty($sidebarSuggestions)): ?>
                        <?php foreach ($sidebarSuggestions as $s): ?>
                        <div class="suggestion-item" id="sidebar-sug-<?= (int)$s['id'] ?>">
                            <a href="index.php?url=profile/<?= htmlspecialchars($s['username']) ?>" class="suggestion-item-link">
                                <img src="assets/uploads/<?= htmlspecialchars($s['profile_image']) ?>"
                                     alt="avatar" class="avatar-sm"
                                     onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                                <div>
                                    <p class="suggestion-name"><?= htmlspecialchars($s['full_name']) ?></p>
                                    <p class="suggestion-username">@<?= htmlspecialchars($s['username']) ?></p>
                                </div>
                            </a>
                            <button class="sidebar-add-btn js-sidebar-add" title="Add Friend"
                                    data-user-id="<?= (int)$s['id'] ?>">
                                <i class="fa fa-user-plus"></i>
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