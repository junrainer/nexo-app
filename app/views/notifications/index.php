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
        <form id="notif-bulk-form" method="POST" action="index.php?url=notifications/bulk">
            <?= Security::field() ?>
            <div class="notif-selected-bar">
                <label class="notif-select-all-wrap">
                    <input type="checkbox" id="notif-select-all">
                    <span>Select all</span>
                </label>
                <div class="notif-selected-actions">
                    <span id="notif-selected-count">0 selected</span>
                    <button type="submit" class="btn-mark-all" name="bulk_action" value="mark_read">
                        <i class="fa fa-check"></i> Mark as read
                    </button>
                    <button type="submit" class="btn-mark-all" name="bulk_action" value="mark_unread">
                        <i class="fa fa-envelope"></i> Mark as unread
                    </button>
                    <button type="submit" class="btn-mark-all btn-danger-soft" id="notif-delete-selected-btn" name="bulk_action" value="delete" style="display:none;">
                        <i class="fa fa-trash"></i> Delete selected
                    </button>
                </div>
            </div>

            <div class="notif-page-list">
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-page-item <?= $n['is_read'] ? '' : 'unread' ?>">
                        <label class="notif-item-check-wrap">
                            <input
                                type="checkbox"
                                class="notif-select-item"
                                name="selected_notifications[]"
                                value="<?= (int)$n['id'] ?>"
                                data-is-read="<?= $n['is_read'] ? '1' : '0' ?>"
                            >
                        </label>
                        <a href="<?= htmlspecialchars(notifLink($n)) ?>"
                           class="notif-page-link js-notif-page-link"
                           data-notif-id="<?= (int)$n['id'] ?>">
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
                    </div>
                <?php endforeach; ?>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('notif-select-all');
            const items = Array.from(document.querySelectorAll('.notif-select-item'));
            const countEl = document.getElementById('notif-selected-count');
            const deleteBtn = document.getElementById('notif-delete-selected-btn');

            if (!selectAll || !countEl || !deleteBtn || items.length === 0) return;

            const updateState = function () {
                const checked = items.filter(i => i.checked);
                const selectedCount = checked.length;
                countEl.textContent = selectedCount + ' selected';
                const allChecked = selectedCount === items.length;
                selectAll.checked = allChecked;
                selectAll.indeterminate = selectedCount > 0 && !allChecked;
                const everySelectedIsRead = selectedCount > 0 && checked.every(i => i.dataset.isRead === '1');
                deleteBtn.style.display = everySelectedIsRead ? 'inline-flex' : 'none';
            };

            selectAll.addEventListener('change', function () {
                items.forEach(i => { i.checked = selectAll.checked; });
                updateState();
            });
            items.forEach(i => i.addEventListener('change', updateState));

            document.querySelectorAll('.js-notif-page-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    const notifId = parseInt(link.dataset.notifId || '0', 10);
                    if (notifId > 0 && typeof markNotificationRead === 'function') {
                        markNotificationRead(notifId);
                    }
                });
            });

            updateState();
        });
        </script>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
