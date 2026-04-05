<?php
$pageTitle = 'Friends – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="friends-page">
    
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="friends-tabs">
        <button class="tab-btn active" data-tab="friends" onclick="switchFriendTab('friends')">
            <i class="fa fa-users"></i> Friends <span class="tab-count"><?= count($friends) ?></span>
        </button>
        <button class="tab-btn" data-tab="requests" onclick="switchFriendTab('requests')">
            <i class="fa fa-user-plus"></i> Requests 
            <?php if (count($pendingReceived) > 0): ?>
                <span class="tab-badge"><?= count($pendingReceived) ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" data-tab="sent" onclick="switchFriendTab('sent')">
            <i class="fa fa-paper-plane"></i> Sent
        </button>
        <button class="tab-btn" data-tab="suggestions" onclick="switchFriendTab('suggestions')">
            <i class="fa fa-user-group"></i> Suggestions
        </button>
    </div>

    <!-- Friends List -->
    <div class="tab-content active" id="tab-friends">
        <div class="friends-grid">
            <?php if (empty($friends)): ?>
                <div class="empty-state">
                    <i class="fa fa-user-group"></i>
                    <h3>No friends yet</h3>
                    <p>Add friends to see them here!</p>
                </div>
            <?php else: ?>
                <?php foreach ($friends as $friend): ?>
                <div class="friend-card" id="friend-<?= $friend['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($friend['username']) ?>" class="friend-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($friend['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </a>
                    <div class="friend-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($friend['username']) ?>" class="friend-name">
                            <?= htmlspecialchars($friend['full_name']) ?>
                        </a>
                        <span class="friend-username">@<?= htmlspecialchars($friend['username']) ?></span>
                    </div>
                    <div class="friend-actions">
                        <a href="index.php?url=message/start&user=<?= $friend['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-comment"></i> Message
                        </a>
                        <button class="btn btn-ghost btn-sm" onclick="unfriend(<?= $friend['id'] ?>)">
                            <i class="fa fa-user-minus"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Friend Requests -->
    <div class="tab-content" id="tab-requests">
        <div class="friends-grid">
            <?php if (empty($pendingReceived)): ?>
                <div class="empty-state">
                    <i class="fa fa-inbox"></i>
                    <h3>No pending requests</h3>
                    <p>When someone sends you a request, it will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendingReceived as $req): ?>
                <div class="friend-card" id="request-<?= $req['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($req['username']) ?>" class="friend-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($req['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </a>
                    <div class="friend-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($req['username']) ?>" class="friend-name">
                            <?= htmlspecialchars($req['full_name']) ?>
                        </a>
                        <span class="friend-username">@<?= htmlspecialchars($req['username']) ?></span>
                    </div>
                    <div class="friend-actions">
                        <button class="btn btn-primary btn-sm" onclick="acceptRequest(<?= $req['id'] ?>)">
                            <i class="fa fa-check"></i> Accept
                        </button>
                        <button class="btn btn-ghost btn-sm" onclick="declineRequest(<?= $req['id'] ?>)">
                            <i class="fa fa-xmark"></i> Decline
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sent Requests -->
    <div class="tab-content" id="tab-sent">
        <div class="friends-grid">
            <?php if (empty($pendingSent)): ?>
                <div class="empty-state">
                    <i class="fa fa-clock"></i>
                    <h3>No sent requests</h3>
                    <p>Requests you send will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendingSent as $sent): ?>
                <div class="friend-card" id="sent-<?= $sent['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($sent['username']) ?>" class="friend-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($sent['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </a>
                    <div class="friend-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($sent['username']) ?>" class="friend-name">
                            <?= htmlspecialchars($sent['full_name']) ?>
                        </a>
                        <span class="friend-username">@<?= htmlspecialchars($sent['username']) ?></span>
                    </div>
                    <div class="friend-actions">
                        <span class="pending-label"><i class="fa fa-clock"></i> Pending</span>
                        <button class="btn btn-ghost btn-sm" onclick="cancelRequest(<?= $sent['id'] ?>)">
                            Cancel
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Suggestions -->
    <div class="tab-content" id="tab-suggestions">
        <div class="friends-grid">
            <?php if (empty($suggestions)): ?>
                <div class="empty-state">
                    <i class="fa fa-lightbulb"></i>
                    <h3>No suggestions</h3>
                    <p>Check back later for friend suggestions!</p>
                </div>
            <?php else: ?>
                <?php foreach ($suggestions as $sug): ?>
                <div class="friend-card" id="suggestion-<?= $sug['id'] ?>">
                    <a href="index.php?url=profile/<?= htmlspecialchars($sug['username']) ?>" class="friend-avatar">
                        <img src="assets/uploads/<?= htmlspecialchars($sug['profile_image'] ?? 'default.png') ?>"
                             alt="avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                    </a>
                    <div class="friend-info">
                        <a href="index.php?url=profile/<?= htmlspecialchars($sug['username']) ?>" class="friend-name">
                            <?= htmlspecialchars($sug['full_name']) ?>
                        </a>
                        <span class="friend-username">@<?= htmlspecialchars($sug['username']) ?></span>
                    </div>
                    <div class="friend-actions">
                        <button class="btn btn-primary btn-sm" onclick="sendRequest(<?= $sug['id'] ?>)">
                            <i class="fa fa-user-plus"></i> Add Friend
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function switchFriendTab(tabName) {
    document.querySelectorAll('.friends-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function sendRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    
    fetch('index.php?url=friend/request', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('suggestion-' + userId);
                if (card) {
                    card.querySelector('.friend-actions').innerHTML = '<span class="pending-label"><i class="fa fa-check"></i> Request Sent</span>';
                }
            }
        });
}

function acceptRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    
    fetch('index.php?url=friend/accept', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
}

function declineRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    
    fetch('index.php?url=friend/decline', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('request-' + userId);
                if (card) card.remove();
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
                const card = document.getElementById('sent-' + userId);
                if (card) card.remove();
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
                const card = document.getElementById('friend-' + userId);
                if (card) card.remove();
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
