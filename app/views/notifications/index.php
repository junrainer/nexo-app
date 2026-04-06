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

function notifTimeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)           return 'Just now';
    if ($diff < 3600)         return floor($diff / 60) . 'm ago';
    if ($diff < 86400)        return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)       return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}
?>

<div class="notif-page">

    <div class="notif-page-header">
        <h1><i class="fa fa-bell"></i> Notifications</h1>
        <?php if (!empty($notifications)): ?>
        <form method="POST" action="index.php?url=notifications/read">
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
                    <img src="assets/uploads/<?= htmlspecialchars($n['actor_image'] ?? 'default.png') ?>"
                         alt="avatar"
                         class="notif-avatar"
                         onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    <div class="notif-content">
                        <p><?= htmlspecialchars($n['message']) ?></p>
                        <span class="notif-time"><?= notifTimeAgo($n['created_at']) ?></span>
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
