<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Nexo' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<?php
$currentUrl = $_GET['url'] ?? 'feed';
$username   = $_SESSION['username'];
?>

<div class="app-shell">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-top">
            <a href="index.php?url=feed" class="sidebar-brand">
                <div class="brand-icon">N</div>
                <span>Nexo</span>
            </a>
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
            <a href="#" class="nav-item" onclick="toggleNotifications(event)">
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
            <a href="index.php?url=settings" class="nav-item <?= $currentUrl === 'settings' ? 'active' : '' ?>">
                <i class="fa fa-gear"></i> Settings
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="index.php?url=logout" class="sidebar-logout">
                <img src="assets/uploads/<?= htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') ?>"
                     alt="avatar" class="avatar-sm"
                     onerror="this.onerror=null; this.src='assets/images/default.png'">
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
                <a href="index.php?url=feed" class="mobile-brand">
                    <div class="brand-icon brand-icon-sm">N</div>
                    <span>Nexo</span>
                </a>
            </div>
            <div class="topbar-search">
                <form action="index.php" method="GET">
                    <input type="hidden" name="url" value="search">
                    <i class="fa fa-search"></i>
                    <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
            </div>
            <div class="topbar-right">
                <!-- Messages -->
                <a href="index.php?url=messages" class="icon-btn" title="Messages">
                    <i class="fa fa-comment-dots"></i>
                    <span class="icon-badge message-count" style="display:none"></span>
                </a>
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
                    </div>
                </div>
                <!-- Avatar dropdown -->
                <div class="avatar-menu">
                    <button class="avatar-trigger" onclick="toggleAvatarMenu(event)" id="avatar-btn">
                        <img src="assets/uploads/<?= htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') ?>"
                             alt="avatar" class="avatar-sm avatar-ring"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </button>
                    <div class="avatar-dropdown" id="avatar-dropdown">
                        <div class="dropdown-user">
                            <img src="assets/uploads/<?= htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') ?>"
                                 alt="avatar" class="avatar-md"
                                 onerror="this.onerror=null; this.src='assets/images/default.png'">
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

        <!-- PAGE CONTENT -->
        <div class="content-area">
            <main class="page-main">
<?php endif; ?>