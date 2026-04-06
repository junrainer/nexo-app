<?php
$pageTitle = $pageTitle ?? 'Notifications – Nexo';
$hideRightSidebar = true;

require __DIR__ . '/../partials/header.php';

function notifLink($n) {
    switch ($n['type']) {
        case 'like':
        case 'comment':
            return 'index.php?url=feed#post-' . (int)$n['related_id'];
        case 'friend_request':
            return 'index.php?url=friends';
        case 'friend_accept':
            return 'index.php?url=profile/' . rawurlencode($n['actor_username']);
        default:
            return 'index.php?url=feed';
    }
}
?>

<div class="notif-page">

    <div class="notif-page-header">
        <h1><i class="fa fa-bell"></i> Notifications</h1>
        <?php if (!empty($notifications)): ?>
        <form method="POST" action="index.php?url=notifications/read">
            <?= Security::field() ?>
            <button type="submit" class="mark-read-btn btn-mark-all">
                <i class="fa fa-check-double"></i> Mark all as read
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="notif-page-empty">
            <i class="fa fa-bell-slash"></i>
            <p>You have no notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="notif-page-list">
            <?php foreach ($notifications as $n): ?>
                <a href="<?= htmlspecialchars(notifLink($n)) ?>"
                   class="notif-page-item <?= $n['is_read'] ? '' : 'unread' ?>"
                   onclick="markNotificationRead(<?= (int)$n['id'] ?>)">
                    <img src="<?= ($n['actor_image'] ?? 'default.png') !== 'default.png' ? 'assets/uploads/' . htmlspecialchars($n['actor_image']) : 'assets/images/default-profile.webp' ?>"
                         alt="avatar"
                         class="notif-avatar"
                         onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    <div class="notif-content">
                        <p><?= htmlspecialchars($n['message']) ?></p>
                        <span class="notif-time"><time class="live-time" data-time="<?= htmlspecialchars($n['created_at']) ?>"><?= time_ago($n['created_at']) ?></time></span>
                    </div>
                    <?php if (!$n['is_read']): ?>
                        <span class="notif-dot"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
