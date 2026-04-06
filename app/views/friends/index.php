<?php
$pageTitle = 'Friends – Nexo';
$hideRightSidebar = true;

$friends = $friends ?? [];
$pendingReceived = $pendingReceived ?? [];
$pendingSent = $pendingSent ?? [];
$suggestions = $suggestions ?? [];

require __DIR__ . '/../partials/header.php';
?>

<div class="gm-page gm-friends-page">

    <div class="gm-page-header">
        <i class="fa fa-users" style="color:var(--primary);"></i>
        <h1>Friends</h1>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Tab switcher -->
    <div class="gm-tab-switcher">
        <button class="gm-tab-sw active" data-tab="friends" onclick="switchFriendTab('friends', this)">
            Your Friends (<?= count($friends) ?>)
        </button>
        <button class="gm-tab-sw" data-tab="requests" onclick="switchFriendTab('requests', this)">
            Requests
            <?php if (count($pendingReceived) > 0): ?>
                <span class="gm-tab-badge"><?= count($pendingReceived) ?></span>
            <?php endif; ?>
        </button>
        <button class="gm-tab-sw" data-tab="sent" onclick="switchFriendTab('sent', this)">
            Sent
        </button>
        <button class="gm-tab-sw" data-tab="suggestions" onclick="switchFriendTab('suggestions', this)">
            Suggestions
        </button>
    </div>

    <!-- Friends -->
    <div class="gm-tab-panel active" id="tab-friends">
        <?php if (empty($friends)): ?>
            <div class="gm-empty">
                <i class="fa fa-user-group"></i>
                <p>No friends yet. Check suggestions!</p>
            </div>
        <?php else: ?>
            <div class="gm-list">
                <?php foreach ($friends as $friend): ?>
                <div class="gm-person-row" id="friend-<?= $friend['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($friend['username']) ?>" class="gm-user-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($friend['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    </a>
                    <div class="gm-user-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($friend['username']) ?>" class="gm-user-name">
                            <?= htmlspecialchars($friend['full_name']) ?>
                        </a>
                        <p class="gm-user-handle">@<?= htmlspecialchars($friend['username']) ?></p>
                    </div>
                    <button class="gm-btn-ghost gm-btn-sm" onclick="unfriend(<?= $friend['id'] ?>)">
                        <i class="fa fa-user-minus"></i> Unfriend
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Requests -->
    <div class="gm-tab-panel" id="tab-requests">
        <?php if (empty($pendingReceived)): ?>
            <div class="gm-empty">
                <i class="fa fa-inbox"></i>
                <p>No pending requests</p>
            </div>
        <?php else: ?>
            <div class="gm-list">
                <?php foreach ($pendingReceived as $req): ?>
                <div class="gm-person-row" id="request-<?= $req['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($req['username']) ?>" class="gm-user-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($req['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    </a>
                    <div class="gm-user-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($req['username']) ?>" class="gm-user-name">
                            <?= htmlspecialchars($req['full_name']) ?>
                        </a>
                        <p class="gm-user-handle">@<?= htmlspecialchars($req['username']) ?></p>
                    </div>
                    <div class="gm-row-actions">
                        <button class="gm-btn-primary gm-btn-sm" onclick="acceptRequest(<?= $req['id'] ?>)">
                            <i class="fa fa-check"></i> Accept
                        </button>
                        <button class="gm-btn-ghost gm-btn-sm" onclick="declineRequest(<?= $req['id'] ?>)">
                            Decline
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sent -->
    <div class="gm-tab-panel" id="tab-sent">
        <?php if (empty($pendingSent)): ?>
            <div class="gm-empty">
                <i class="fa fa-clock"></i>
                <p>No sent requests</p>
            </div>
        <?php else: ?>
            <div class="gm-list">
                <?php foreach ($pendingSent as $sent): ?>
                <div class="gm-person-row" id="sent-<?= $sent['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($sent['username']) ?>" class="gm-user-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($sent['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    </a>
                    <div class="gm-user-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($sent['username']) ?>" class="gm-user-name">
                            <?= htmlspecialchars($sent['full_name']) ?>
                        </a>
                        <p class="gm-user-handle">@<?= htmlspecialchars($sent['username']) ?></p>
                    </div>
                    <div class="gm-row-actions">
                        <span class="gm-pending-label"><i class="fa fa-clock"></i> Pending</span>
                        <button class="gm-btn-ghost gm-btn-sm" onclick="cancelRequest(<?= $sent['id'] ?>)">Cancel</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Suggestions -->
    <div class="gm-tab-panel" id="tab-suggestions">
        <?php if (empty($suggestions)): ?>
            <div class="gm-empty">
                <i class="fa fa-lightbulb"></i>
                <p>No suggestions right now</p>
            </div>
        <?php else: ?>
            <div class="gm-list">
                <?php foreach ($suggestions as $sug): ?>
                <div class="gm-person-row" id="suggestion-<?= $sug['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($sug['username']) ?>" class="gm-user-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($sug['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                    </a>
                    <div class="gm-user-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($sug['username']) ?>" class="gm-user-name">
                            <?= htmlspecialchars($sug['full_name']) ?>
                        </a>
                        <p class="gm-user-handle">@<?= htmlspecialchars($sug['username']) ?></p>
                    </div>
                    <button class="gm-btn-primary gm-btn-sm" onclick="sendRequest(<?= $sug['id'] ?>)">
                        <i class="fa fa-user-plus"></i> Add
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function switchFriendTab(tabName, btn) {
    document.querySelectorAll('.gm-tab-sw').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.gm-tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function sendRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/request', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('suggestion-' + userId);
                if (row) row.querySelector('.gm-btn-primary').outerHTML = '<span class="gm-pending-label"><i class="fa fa-check"></i> Request Sent</span>';
            }
        });
}

function acceptRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/accept', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
}

function declineRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/decline', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('request-' + userId);
                if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
            }
        });
}

function cancelRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/decline', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('sent-' + userId);
                if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
            }
        });
}

function unfriend(userId) {
    if (!confirm('Are you sure you want to unfriend this person?')) return;
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/unfriend', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('friend-' + userId);
                if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>