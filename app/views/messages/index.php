<?php
$pageTitle = 'Messages – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="messages-page">
    <div class="messages-container">
        
        <!-- Conversations List -->
        <div class="conversations-list <?= $activeConversationId ? 'hide-mobile' : '' ?>">
            <div class="conversations-header">
                <h2>Messages</h2>
                <button class="icon-btn-sm" title="New message" onclick="openNewMessageModal()">
                    <i class="fa fa-pen-to-square"></i>
                </button>
            </div>
            
            <?php if (empty($conversations)): ?>
                <div class="empty-conversations">
                    <i class="fa fa-comments"></i>
                    <p>No conversations yet</p>
                    <small>Start a conversation with someone!</small>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                <a href="index.php?url=messages&c=<?= $conv['id'] ?>" 
                   class="conversation-item <?= ($activeConversationId == $conv['id']) ? 'active' : '' ?>">
                    <img src="assets/uploads/<?= htmlspecialchars($conv['other_image'] ?? 'default.png') ?>"
                         alt="avatar" class="avatar-md"
                         onerror="this.onerror=null; this.src='assets/images/default.png'">
                    <div class="conversation-info">
                        <div class="conversation-top">
                            <span class="conversation-name"><?= htmlspecialchars($conv['other_name']) ?></span>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="conversation-preview">
                            <?= htmlspecialchars(mb_strimwidth($conv['last_message'] ?? '', 0, 40, '...')) ?>
                        </p>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Chat Area -->
        <div class="chat-area <?= !$activeConversationId ? 'hide-mobile' : '' ?>">
            <?php if ($activeConversationId && $activeUser): ?>
                <!-- Chat Header -->
                <div class="chat-header">
                    <a href="index.php?url=messages" class="back-btn hide-desktop">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="index.php?url=profile/<?= htmlspecialchars($activeUser['username']) ?>" class="chat-user-info">
                        <img src="assets/uploads/<?= htmlspecialchars($activeUser['profile_image'] ?? 'default.png') ?>"
                             alt="avatar" class="avatar-md"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div>
                            <span class="chat-user-name"><?= htmlspecialchars($activeUser['full_name']) ?></span>
                            <span class="chat-user-status">@<?= htmlspecialchars($activeUser['username']) ?></span>
                        </div>
                    </a>
                </div>
                
                <!-- Messages -->
                <div class="chat-messages" id="chat-messages">
                    <?php foreach ($messages as $msg): ?>
                    <div class="message <?= ($msg['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received' ?>"
                         data-message-id="<?= $msg['id'] ?>">
                        <?php if ($msg['sender_id'] != $_SESSION['user_id']): ?>
                        <img src="assets/uploads/<?= htmlspecialchars($msg['profile_image'] ?? 'default.png') ?>"
                             alt="avatar" class="message-avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <?php endif; ?>
                        <div class="message-content">
                            <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            <span class="message-time"><?= time_ago($msg['created_at']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Message Input -->
                <form class="chat-input-form" id="message-form" onsubmit="sendMessage(event)">
                    <input type="hidden" name="recipient_id" value="<?= $activeUser['id'] ?>">
                    <input type="text" name="message" id="message-input" 
                           placeholder="Type a message..." autocomplete="off" required>
                    <button type="submit" class="send-btn">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </form>
                
            <?php else: ?>
                <div class="no-chat-selected">
                    <i class="fa fa-comments"></i>
                    <h3>Select a conversation</h3>
                    <p>Choose from your existing conversations or start a new one</p>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- New Message Modal -->
<div class="modal-overlay" id="new-message-modal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">New Message</span>
            <button class="modal-close" onclick="closeModal('new-message-modal')">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="text" id="user-search" class="modal-input" 
                   placeholder="Search for a user..." oninput="searchUsers(this.value)">
            <div id="user-search-results" class="user-search-results"></div>
        </div>
    </div>
</div>

<script>
// Send message via AJAX
function sendMessage(e) {
    e.preventDefault();
    const form = e.target;
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    const fd = new FormData(form);
    
    fetch('index.php?url=message/send', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendMessage(data.message, true);
                input.value = '';
                scrollToBottom();
            }
        })
        .catch(err => console.error(err));
}

// Append message to chat
function appendMessage(msg, isSent) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.className = 'message ' + (isSent ? 'sent' : 'received');
    div.dataset.messageId = msg.id;
    
    let html = '';
    if (!isSent) {
        html += `<img src="assets/uploads/${msg.profile_image || 'default.png'}" 
                      alt="avatar" class="message-avatar"
                      onerror="this.onerror=null; this.src='assets/images/default.png'">`;
    }
    html += `<div class="message-content">
                <p>${escapeHtml(msg.message)}</p>
                <span class="message-time">just now</span>
            </div>`;
    
    div.innerHTML = html;
    container.appendChild(div);
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    if (container) container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// New message modal
function openNewMessageModal() {
    document.getElementById('new-message-modal').style.display = 'flex';
    document.getElementById('user-search').focus();
}

// Search users for new message
let searchTimeout;
function searchUsers(query) {
    clearTimeout(searchTimeout);
    const results = document.getElementById('user-search-results');
    
    if (query.length < 2) {
        results.innerHTML = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('index.php?url=search&q=' + encodeURIComponent(query) + '&ajax=1')
            .then(r => r.json())
            .then(data => {
                results.innerHTML = '';
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        results.innerHTML += `
                            <a href="index.php?url=message/start&user=${user.id}" class="user-result">
                                <img src="assets/uploads/${user.profile_image || 'default.png'}" 
                                     alt="avatar" class="avatar-sm"
                                     onerror="this.onerror=null; this.src='assets/images/default.png'">
                                <div>
                                    <span class="user-name">${escapeHtml(user.full_name)}</span>
                                    <span class="user-username">@${escapeHtml(user.username)}</span>
                                </div>
                            </a>
                        `;
                    });
                } else {
                    results.innerHTML = '<p class="no-results">No users found</p>';
                }
            });
    }, 300);
}

// Poll for new messages
<?php if ($activeConversationId): ?>
let lastMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

setInterval(() => {
    fetch(`index.php?url=message/new&conversation_id=<?= $activeConversationId ?>&last_message_id=${lastMessageId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.sender_id != <?= $_SESSION['user_id'] ?>) {
                        appendMessage(msg, false);
                    }
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                scrollToBottom();
            }
        });
}, 3000);

// Scroll to bottom on load
scrollToBottom();
<?php endif; ?>
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
