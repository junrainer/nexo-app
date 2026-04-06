<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Nexo' ?></title>
    <link rel="icon" href="assets/images/app-logo.png" type="image/png">
    <?php require_once __DIR__ . '/../../../lib/Security.php'; ?>
    <?= Security::meta() ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body<?php if (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 0) echo ' class="light-mode"'; ?>>

<?php if (!empty($_SESSION['toast_success'])): ?>
<div class="toast" id="main-toast">
    <i class="fa fa-circle-check toast-icon-success"></i>
    <span><?= htmlspecialchars($_SESSION['toast_success']) ?></span>
    <button class="toast-close" onclick="this.closest('.toast').classList.remove('toast-show')" aria-label="Dismiss">
        <i class="fa fa-xmark"></i>
    </button>
</div>
<script>
    (function () {
        const t = document.getElementById('main-toast');
        if (!t) return;
        requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('toast-show')));
        setTimeout(() => {
            t.classList.remove('toast-show');
            setTimeout(() => t.remove(), 380);
        }, 4500);
    })();
</script>
<?php unset($_SESSION['toast_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['warning'])): ?>
<div class="toast" id="main-toast-warning">
    <i class="fa fa-triangle-exclamation toast-icon-warning"></i>
    <span><?= htmlspecialchars($_SESSION['warning']) ?></span>
    <button class="toast-close" onclick="this.closest('.toast').classList.remove('toast-show')" aria-label="Dismiss">
        <i class="fa fa-xmark"></i>
    </button>
</div>
<script>
    (function () {
        const t = document.getElementById('main-toast-warning');
        if (!t) return;
        requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('toast-show')));
        setTimeout(() => {
            t.classList.remove('toast-show');
            setTimeout(() => t.remove(), 380);
        }, 6000);
    })();
</script>
<?php unset($_SESSION['warning']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
<?php
$currentUrl = $_GET['url'] ?? 'feed';
$username   = $_SESSION['username'];
?>

<div class="app-shell" data-user-id="<?= (int)$_SESSION['user_id'] ?>">

    <!-- MOBILE OVERLAY (closes sidebar) -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" id="main-sidebar">
        <div class="sidebar-top">
            <a href="index.php?url=feed" class="sidebar-brand">
                <img src="assets/images/app-logo.png"
                     alt="Nexo"
                     class="brand-wordmark"
                     onerror="this.style.display='none'; document.getElementById('brand-fallback-sb').style.display='flex'">
                <span class="brand-name">NEXO</span>
                <!-- Text fallback if image not found -->
                <span id="brand-fallback-sb" class="brand-fallback" style="display:none">
                    <span class="brand-icon">N</span>
                    <span>NEXO</span>
                </span>
            </a>
            <!-- Close button (mobile only) -->
            <button class="sidebar-close hide-desktop" onclick="closeSidebar()" aria-label="Close menu">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php?url=feed" class="nav-item <?= $currentUrl === 'feed' ? 'active' : '' ?>">
                <i class="fa fa-house"></i> Home
            </a>
            <a href="index.php?url=profile/<?= htmlspecialchars($username) ?>" class="nav-item <?= str_starts_with($currentUrl, 'profile') ? 'active' : '' ?>">
                <i class="fa fa-user"></i> Profile
            </a>
            <a href="index.php?url=messages" class="nav-item <?= $currentUrl === 'messages' ? 'active' : '' ?>">
                <i class="fa fa-comment-dots"></i> Messages
                <span class="nav-badge message-count" style="display:none"></span>
            </a>
            <a href="index.php?url=notifications/all" class="nav-item <?= $currentUrl === 'notifications/all' ? 'active' : '' ?>">
                <i class="fa fa-bell"></i> Notifications
                <span class="nav-badge notif-count" style="display:none"></span>
            </a>

            <div class="nav-section-label">More</div>

            <a href="index.php?url=saved" class="nav-item <?= $currentUrl === 'saved' ? 'active' : '' ?>">
                <i class="fa fa-bookmark"></i> Saved
            </a>
            <a href="index.php?url=friends" class="nav-item <?= $currentUrl === 'friends' ? 'active' : '' ?>">
                <i class="fa fa-users"></i> Friends
            </a>
            <a href="index.php?url=search" class="nav-item <?= $currentUrl === 'search' ? 'active' : '' ?>">
                <i class="fa fa-compass"></i> Explore
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="index.php?url=logout" class="sidebar-logout">
                <img src="<?= ($_SESSION['profile_image'] ?? 'default.png') !== 'default.png' ? 'assets/uploads/' . htmlspecialchars($_SESSION['profile_image']) : 'assets/images/default-profile.webp' ?>"
                     alt="avatar" class="avatar-sm"
                     onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <i class="fa fa-right-from-bracket logout-icon"></i>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-wrapper">

        <!-- TOP BAR -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Hamburger (mobile only) -->
                <button class="hamburger-btn hide-desktop" onclick="openSidebar()" aria-label="Open menu">
                    <i class="fa fa-bars"></i>
                </button>
                <a href="index.php?url=feed" class="mobile-brand">
                    <img src="assets/images/app-logo.png"
                         alt="Nexo"
                         class="brand-logo-sm"
                         onerror="this.style.display='none'; document.getElementById('brand-fallback-tb').style.display='flex'">
                    <span class="brand-name">NEXO</span>
                    <span id="brand-fallback-tb" class="brand-fallback" style="display:none">
                        <span class="brand-icon brand-icon-sm">N</span>
                        <span>NEXO</span>
                    </span>
                </a>
            </div>
            <div class="topbar-fill"></div>
            <!-- Search -->
            <form class="topbar-search-form" action="index.php" method="GET">
                <input type="hidden" name="url" value="search">
                <i class="fa fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Search..."
                       autocomplete="off"
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            </form>
            <div class="topbar-right">
                <!-- Messages dropdown -->
                <div class="message-menu">
                    <button class="icon-btn" title="Messages" onclick="toggleMessages(event)" id="msg-btn">
                        <i class="fa fa-comment-dots"></i>
                        <span class="icon-badge message-count" style="display:none"></span>
                    </button>
                    <div class="message-dropdown" id="message-dropdown">
                        <div class="msg-header">
                            <span>Messages</span>
                            <a href="index.php?url=messages" class="msg-see-all">See all</a>
                        </div>
                        <div class="msg-list" id="msg-list">
                            <div class="msg-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                        </div>
                    </div>
                </div>
                <!-- Notifications -->
                <div class="notification-menu">
                    <button class="icon-btn" title="Notifications" onclick="toggleNotifications(event)" id="notif-btn">
                        <i class="fa fa-bell"></i>
                        <span class="icon-badge notif-count" style="display:none"></span>
                    </button>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notif-header">
                            <span>Notifications</span>
                            <button onclick="markAllNotificationsRead()" class="mark-read-btn">Mark all read</button>
                        </div>
                        <div class="notif-list" id="notif-list">
                            <div class="notif-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                        </div>
                        <div class="notif-footer">
                            <a href="index.php?url=notifications/all" class="notif-see-all">See all notifications</a>
                        </div>
                    </div>
                </div>
                <!-- Avatar dropdown -->
                <div class="avatar-menu">
                    <button class="avatar-trigger" onclick="toggleAvatarMenu(event)" id="avatar-btn">
                        <img src="<?= ($_SESSION['profile_image'] ?? 'default.png') !== 'default.png' ? 'assets/uploads/' . htmlspecialchars($_SESSION['profile_image']) : 'assets/images/default-profile.webp' ?>"
                             alt="avatar" class="avatar-sm avatar-ring"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    </button>
                    <div class="avatar-dropdown" id="avatar-dropdown">
                        <div class="dropdown-user">
                            <img src="<?= ($_SESSION['profile_image'] ?? 'default.png') !== 'default.png' ? 'assets/uploads/' . htmlspecialchars($_SESSION['profile_image']) : 'assets/images/default-profile.webp' ?>"
                                 alt="avatar" class="avatar-md"
                                 onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                            <div>
                                <p class="dropdown-name"><?= htmlspecialchars($_SESSION['full_name']) ?></p>
                                <p class="dropdown-username">@<?= htmlspecialchars($username) ?></p>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="index.php?url=profile/<?= htmlspecialchars($username) ?>" class="dropdown-item">
                            <i class="fa fa-user"></i> View Profile
                        </a>
                        <a href="index.php?url=settings" class="dropdown-item">
                            <i class="fa fa-gear"></i> Settings & privacy
                        </a>
                        <a href="index.php?url=saved" class="dropdown-item">
                            <i class="fa fa-bookmark"></i> Saved posts
                        </a>
                        <button class="dropdown-item" onclick="toggleDarkMode()">
                            <i class="fa fa-moon"></i> Dark mode
                        </button>
                        <div class="dropdown-divider"></div>
                        <a href="index.php?url=logout" class="dropdown-item danger">
                            <i class="fa fa-right-from-bracket"></i> Log out
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLOATING CHAT WINDOW -->
        <div class="floating-chat-overlay" id="floating-chat-overlay" style="display:none;" onclick="if(event.target===this)closeFloatingChat()">
            <div class="floating-chat" id="floating-chat">
                <div class="floating-chat-header">
                    <div class="floating-chat-user">
                        <img id="floating-chat-avatar" src="assets/images/default-profile.webp" alt="avatar" class="avatar-sm"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                        <span id="floating-chat-name"></span>
                    </div>
                    <div class="floating-chat-actions">
                        <a id="floating-chat-open-full" href="index.php?url=messages" title="Open full chat">
                            <i class="fa fa-expand"></i>
                        </a>
                        <button type="button" onclick="closeFloatingChat()" title="Close">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="floating-chat-messages" id="floating-chat-messages"></div>
                <form class="floating-chat-input-form" id="floating-chat-form" onsubmit="sendFloatingMessage(event)">
                    <input type="hidden" id="floating-chat-recipient" name="recipient_id" value="">
                    <input type="text" id="floating-chat-input" name="message"
                           placeholder="Type a message..." autocomplete="off" required>
                    <button type="submit"><i class="fa fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div class="content-area">
            <main class="page-main">
<?php endif; ?>