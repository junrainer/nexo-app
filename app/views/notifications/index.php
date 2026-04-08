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
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

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
                    <button type="submit" class="btn-mark-all btn-danger-soft" id="notif-delete-selected-btn" name="bulk_action" value="delete">
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
                                <span class="notif-time"><time class="live-time" data-time="<?= htmlspecialchars(time_iso($n['created_at'])) ?>"><?= time_ago($n['created_at']) ?></time></span>
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
            const bulkForm = document.getElementById('notif-bulk-form');
            const items = Array.from(document.querySelectorAll('.notif-select-item'));
            const countEl = document.getElementById('notif-selected-count');
            const deleteBtn = document.getElementById('notif-delete-selected-btn');

            if (!selectAll || !bulkForm || !countEl || !deleteBtn || items.length === 0) return;

            const updateState = function () {
                const checked = items.filter(i => i.checked);
                const selectedCount = checked.length;
                countEl.textContent = selectedCount + ' selected';
                const allChecked = selectedCount === items.length;
                selectAll.checked = allChecked;
                selectAll.indeterminate = selectedCount > 0 && !allChecked;
                const showDelete = selectedCount > 0;
                deleteBtn.style.display = showDelete ? 'inline-flex' : 'none';
                deleteBtn.disabled = !showDelete;
                deleteBtn.title = showDelete ? 'Delete selected' : 'Select notifications to delete';
            };

            selectAll.addEventListener('change', function () {
                items.forEach(i => { i.checked = selectAll.checked; });
                updateState();
            });
            items.forEach(i => i.addEventListener('change', updateState));

            document.querySelectorAll('.js-notif-page-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    const notifId = parseInt(link.dataset.notifId || '0', 10);
                    const row = link.closest('.notif-page-item');
                    const checkbox = row ? row.querySelector('.notif-select-item') : null;
                    if (selectAll.checked && checkbox && checkbox.checked) {
                        checkbox.checked = false;
                        updateState();
                    }
                    if (notifId > 0 && typeof markNotificationRead === 'function') {
                        markNotificationRead(notifId);
                    }
                });
            });

            bulkForm.addEventListener('submit', function (event) {
                const submitter = event.submitter;
                if (!submitter || submitter.value !== 'delete') return;
                if (!window.confirm('Are you sure you want to delete the selected notifications?')) {
                    event.preventDefault();
                }
            });

            updateState();
        });
        </script>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
