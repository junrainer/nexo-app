// Nexo – app.js  (fully fixed)

// ── Password eye toggle ──────────────────────────────
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

// ── CSRF: auto-inject token into all POST forms & fetch calls ──
(function () {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf      = tokenMeta ? tokenMeta.content : '';
    if (!csrf) return;

    // 1. Auto-append _token to every <form method="post"> on submit
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.method && form.method.toLowerCase() === 'post') {
            if (!form.querySelector('input[name="_token"]')) {
                const hidden   = document.createElement('input');
                hidden.type    = 'hidden';
                hidden.name    = '_token';
                hidden.value   = csrf;
                form.appendChild(hidden);
            }
        }
    }, true);

    // 2. Wrap window.fetch to auto-include the token in POST requests
    const _fetch = window.fetch;
    window.fetch = function (url, opts) {
        opts = opts || {};
        if (opts.method && opts.method.toUpperCase() === 'POST') {
            if (opts.body instanceof FormData) {
                if (!opts.body.has('_token')) opts.body.append('_token', csrf);
            } else if (typeof opts.body === 'string') {
                if (!opts.body.includes('_token=')) {
                    opts.body += '&_token=' + encodeURIComponent(csrf);
                }
            } else if (!opts.body) {
                const fd = new FormData();
                fd.append('_token', csrf);
                opts.body = fd;
            }
        }
        return _fetch(url, opts);
    };
})();

// ── Helper: escape HTML ──────────────────────────────
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// ── Helper: time ago ─────────────────────────────────
function timeAgo(datetime) {
    const diff = Math.floor((Date.now() - new Date(datetime).getTime()) / 1000);
    if (diff < 60)     return 'just now';
    if (diff < 3600)   return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400)  return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return new Date(datetime).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ── Avatar dropdown ──────────────────────────────────
function toggleAvatarMenu(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const dd = document.getElementById('avatar-dropdown');
    if (!dd) return;
    const isOpen = dd.classList.contains('open');
    // Close all dropdowns first
    document.querySelectorAll('.avatar-dropdown.open, .notification-dropdown.open, .message-dropdown.open, .sidebar-notification-dropdown.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) dd.classList.add('open');
}

document.addEventListener('click', e => {
    const menu = document.getElementById('avatar-dropdown');
    const btn  = document.getElementById('avatar-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// ── Notification dropdown ────────────────────────────
function toggleNotifications(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const dd = document.getElementById('notification-dropdown');
    if (!dd) return;
    const isOpen = dd.classList.contains('open');
    document.querySelectorAll('.avatar-dropdown.open, .notification-dropdown.open, .message-dropdown.open, .sidebar-notification-dropdown.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        dd.classList.add('open');
        loadNotifications();
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('notification-dropdown');
    const btn  = document.getElementById('notif-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// ── Sidebar notification dropdown ────────────────────
function toggleSidebarNotifications(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const dd = document.getElementById('sidebar-notification-dropdown');
    if (!dd) return;
    const isOpen = dd.classList.contains('open');
    document.querySelectorAll('.avatar-dropdown.open, .notification-dropdown.open, .message-dropdown.open, .sidebar-notification-dropdown.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        dd.classList.add('open');
        loadNotifications();
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('sidebar-notification-dropdown');
    const trigger = e.target.closest('.sidebar-notif-menu');
    if (menu && menu.classList.contains('open') && !menu.contains(e.target) && !trigger) {
        menu.classList.remove('open');
    }
});

function loadNotifications() {
    const lists = [
        document.getElementById('notif-list'),
        document.getElementById('sidebar-notif-list')
    ].filter(Boolean);
    if (!lists.length) return;

    const loading = '<div class="notif-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
    lists.forEach(l => { l.innerHTML = loading; });

    fetch('index.php?url=notifications')
        .then(r => r.json())
        .then(data => {
            let html;
            if (data.success && data.notifications && data.notifications.length > 0) {
                html = data.notifications.map(n => `
                    <a href="${escapeHtml(getNotificationLink(n))}"
                       class="notif-item ${n.is_read ? '' : 'unread'}"
                       onclick="markNotificationRead(${n.id})">
                        <img src="assets/uploads/${escapeHtml(n.actor_image || 'default.png')}"
                             alt="avatar" class="notif-avatar"
                             onerror="this.onerror=null; this.src='assets/images/default.png'">
                        <div class="notif-content">
                            <p>${escapeHtml(n.message)}</p>
                            <span class="notif-time">${timeAgo(n.created_at)}</span>
                        </div>
                        ${n.is_read ? '' : '<span class="notif-dot"></span>'}
                    </a>
                `).join('');
            } else {
                html = '<div class="notif-empty"><i class="fa fa-bell-slash"></i><p>No notifications yet</p></div>';
            }
            lists.forEach(l => { l.innerHTML = html; });
        })
        .catch(() => {
            const err = '<div class="notif-empty"><i class="fa fa-circle-exclamation"></i><p>Failed to load</p></div>';
            lists.forEach(l => { l.innerHTML = err; });
        });
}

function getNotificationLink(n) {
    switch (n.type) {
        case 'like':
        case 'comment':
            return 'index.php?url=feed#post-' + n.related_id;
        case 'friend_request':
            return 'index.php?url=friends';
        case 'friend_accept':
            return 'index.php?url=profile/' + encodeURIComponent(n.actor_username);
        default:
            return 'index.php?url=feed';
    }
}

function markNotificationRead(notifId) {
    const fd = new FormData();
    fd.append('notification_id', notifId);
    fetch('index.php?url=notification/read', { method: 'POST', body: fd }).catch(() => {});
}

function markAllNotificationsRead() {
    const fd = new FormData();
    fetch('index.php?url=notifications/read', { method: 'POST', body: fd })
        .then(() => {
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            document.querySelectorAll('.notif-dot').forEach(el => el.remove());
            updateNotificationBadge(0);
        })
        .catch(() => {});
}

// ── Message dropdown ─────────────────────────────────
function toggleMessages(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const dd = document.getElementById('message-dropdown');
    if (!dd) return;
    const isOpen = dd.classList.contains('open');
    document.querySelectorAll('.avatar-dropdown.open, .notification-dropdown.open, .message-dropdown.open, .sidebar-notification-dropdown.open')
        .forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        dd.classList.add('open');
        loadMessages();
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('message-dropdown');
    const btn  = document.getElementById('msg-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

function loadMessages() {
    const list = document.getElementById('msg-list');
    if (!list) return;
    list.innerHTML = '<div class="msg-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
    fetch('index.php?url=message/recent')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.conversations && data.conversations.length > 0) {
                list.innerHTML = data.conversations.map(c => `
                    <a href="index.php?url=messages&conv=${encodeURIComponent(c.id)}" class="msg-item ${c.unread > 0 ? 'unread' : ''}">
                        <div class="msg-avatar-wrap">
                            <img src="assets/uploads/${escapeHtml(c.avatar || 'default.png')}"
                                 alt="avatar" class="msg-avatar"
                                 onerror="this.onerror=null; this.src='assets/images/default.png'">
                            ${c.online ? '<div class="msg-online-dot"></div>' : ''}
                        </div>
                        <div class="msg-content">
                            <div class="msg-top">
                                <span class="msg-name ${c.unread > 0 ? 'bold' : ''}">${escapeHtml(c.name)}</span>
                                <span class="msg-time">${timeAgo(c.last_time)}</span>
                            </div>
                            <p class="msg-preview ${c.unread > 0 ? 'bold' : ''}">${escapeHtml(c.last_message)}</p>
                        </div>
                        ${c.unread > 0 ? `<span class="msg-unread-badge">${c.unread}</span>` : ''}
                    </a>
                `).join('');
            } else {
                list.innerHTML = '<div class="msg-loading">No messages yet</div>';
            }
        })
        .catch(() => {
            list.innerHTML = '<div class="msg-loading">Failed to load</div>';
        });
}

// ── Badge updates ────────────────────────────────────
function updateNotificationBadge(count) {
    document.querySelectorAll('.notif-count').forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

function updateMessageBadge(count) {
    document.querySelectorAll('.message-count').forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

function fetchBadgeCounts() {
    fetch('index.php?url=notifications/count')
        .then(r => r.json())
        .then(data => { if (data.success) updateNotificationBadge(data.count); })
        .catch(() => {});

    fetch('index.php?url=message/unread')
        .then(r => r.json())
        .then(data => { if (data.success) updateMessageBadge(data.count); })
        .catch(() => {});
}

if (document.querySelector('.topbar')) {
    fetchBadgeCounts();
    setInterval(fetchBadgeCounts, 30000);
}

// ── Dark mode toggle ─────────────────────────────────
function toggleDarkMode() {
    const fd = new FormData();
    fetch('index.php?url=settings/darkmode', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.body.classList.toggle('light-mode', !data.dark_mode);
                // Update icon in dropdown
                const icon = document.querySelector('.dropdown-item .fa-moon, .dropdown-item .fa-sun');
                if (icon) {
                    icon.classList.toggle('fa-moon', data.dark_mode);
                    icon.classList.toggle('fa-sun', !data.dark_mode);
                }
            }
        })
        .catch(() => {});
}

// ── AJAX Like ────────────────────────────────────────
function toggleLike(postId, btn) {
    btn.disabled = true;
    const fd = new FormData();
    fd.append('post_id', postId);

    fetch('index.php?url=post/like', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            const countEl = btn.querySelector('.like-count');
            if (countEl) countEl.textContent = data.count > 0 ? data.count : '';
            btn.classList.toggle('liked', !!data.liked);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = data.liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
            }
            btn.disabled = false;
        })
        .catch(() => { btn.disabled = false; });
}

// ── Save/Unsave post ─────────────────────────────────
function toggleSave(postId, btn) {
    const isSaved = btn.dataset.saved === '1';
    const url     = isSaved ? 'index.php?url=post/unsave' : 'index.php?url=post/save';

    const fd = new FormData();
    fd.append('post_id', postId);

    fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success !== false) {
                const saved = !!data.saved;
                btn.dataset.saved = saved ? '1' : '0';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = saved ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark';
                }
                btn.title = saved ? 'Unsave post' : 'Save post';
            }
        })
        .catch(() => {});
}

// ── Post dropdown menu ───────────────────────────────
function togglePostMenu(postId) {
    const menu = document.getElementById('post-menu-' + postId);
    if (!menu) return;
    document.querySelectorAll('.post-dropdown.open').forEach(m => {
        if (m !== menu) m.classList.remove('open');
    });
    menu.classList.toggle('open');
}

document.addEventListener('click', e => {
    if (!e.target.closest('.post-menu-btn')) {
        document.querySelectorAll('.post-dropdown.open').forEach(m => m.classList.remove('open'));
    }
});

// ── Toggle comments section ──────────────────────────
function toggleComments(postId) {
    const section = document.getElementById('comments-' + postId);
    if (!section) return;
    const isHidden = section.style.display === 'none' || section.style.display === '';
    section.style.display = isHidden ? 'flex' : 'none';
    if (isHidden) {
        const input = section.querySelector('.comment-input');
        if (input) input.focus();
    }
}

// ── Edit post modal ───────────────────────────────────
function openEditPost(postId, content) {
    document.getElementById('edit-post-id').value      = postId;
    document.getElementById('edit-post-content').value = content;
    document.getElementById('edit-post-modal').style.display = 'flex';
}

// ── Edit comment modal ────────────────────────────────
function openEditComment(commentId, content) {
    document.getElementById('edit-comment-id').value      = commentId;
    document.getElementById('edit-comment-content').value = content;
    document.getElementById('edit-comment-modal').style.display = 'flex';
}

// ── Edit profile modal ────────────────────────────────
function openEditProfile() {
    document.getElementById('edit-profile-modal').style.display = 'flex';
}

// ── Close any modal ────────────────────────────────────
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => {
            if (m.style.display !== 'none') m.style.display = 'none';
        });
    }
});

// ── Delete post confirm modal ─────────────────────────
function confirmDeletePost(postId) {
    document.getElementById('delete-post-id').value = postId;
    document.getElementById('delete-post-modal').style.display = 'flex';
}

// ── Image preview for compose ─────────────────────────
function previewPostImage(input) {
    const wrap = document.getElementById('image-previews');
    if (!input.files || !wrap) return;
    // Clear previous previews first
    wrap.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = ev => {
            const div = document.createElement('div');
            div.className = 'preview-thumb';
            div.innerHTML = `<img src="${ev.target.result}" alt="preview">
                <button type="button" class="preview-remove" onclick="this.parentElement.remove(); clearFileInput()">
                    <i class="fa fa-xmark"></i>
                </button>`;
            wrap.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function clearFileInput() {
    const inputs = document.querySelectorAll('.compose-card input[type="file"]');
    inputs.forEach(inp => { inp.value = ''; });
}

// ── Avatar preview (edit profile) ─────────────────────
function previewAvatar(input) {
    const img = document.getElementById('avatar-preview');
    if (!input.files || !input.files[0] || !img) return;
    const reader = new FileReader();
    reader.onload = ev => { img.src = ev.target.result; };
    reader.readAsDataURL(input.files[0]);
}

// ── Tab switching (profile / search page) ─────────────
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector('[data-tab="' + tabName + '"]');
    const content = document.getElementById('tab-' + tabName);
    if (btn) btn.classList.add('active');
    if (content) content.classList.add('active');
}

// ── Auto-dismiss alerts ───────────────────────────────
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.4s';
        alert.style.opacity    = '0';
        setTimeout(() => alert.remove(), 400);
    }, 4500);
});

// ── Mobile sidebar (hamburger) ────────────────────────
function openSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.add('sidebar-open');
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.remove('sidebar-open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// ── Comment like (inline AJAX) ────────────────────────
function toggleCommentLike(commentId, btn) {
    // Optimistic toggle
    const isLiked = btn.classList.contains('liked');
    btn.classList.toggle('liked', !isLiked);
    btn.textContent = isLiked ? 'Like' : 'Unlike';
    // NOTE: backend endpoint not yet implemented; remove optimistic-only if adding endpoint
}

// ── Settings panel tabs ───────────────────────────────
function showSettingsPanel(name, btn) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('panel-' + name);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
    history.replaceState(null, '', '#' + name);
}

(function () {
    const VALID = ['account', 'preferences', 'privacy', 'danger'];
    const hash  = location.hash.replace('#', '');
    if (VALID.includes(hash)) {
        const btn = document.querySelector(`.settings-tab[onclick*="'${hash}'"]`);
        if (btn) showSettingsPanel(hash, btn);
    }
})();

// ── Friend page tab switching ─────────────────────────
function switchFriendTab(tabName) {
    document.querySelectorAll('.friends-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector(`.friends-tabs [data-tab="${tabName}"]`);
    const content = document.getElementById('tab-' + tabName);
    if (btn) btn.classList.add('active');
    if (content) content.classList.add('active');
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
                    card.querySelector('.friend-actions').innerHTML =
                        '<span class="pending-label"><i class="fa fa-check"></i> Request Sent</span>';
                }
            }
        })
        .catch(() => {});
}

function acceptRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/accept', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(() => {});
}

function declineRequest(userId) {
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/decline', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('request-' + userId);
                if (card) card.style.animation = 'fadeOut .3s forwards', setTimeout(() => card.remove(), 300);
            }
        })
        .catch(() => {});
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
        })
        .catch(() => {});
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
        })
        .catch(() => {});
}

// ── Profile page: friend button handlers ─────────────
function handleFriendAction(userId) {
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/request', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fa fa-clock"></i> <span>Pending</span>';
                btn.classList.replace('btn-primary', 'btn-ghost');
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        })
        .catch(() => { btn.disabled = false; });
}

function handleUnfriend(userId) {
    if (!confirm('Are you sure you want to unfriend this person?')) return;
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/unfriend', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fa fa-user-plus"></i> <span>Add Friend</span>';
                btn.classList.replace('btn-ghost', 'btn-primary');
                btn.disabled = false;
                btn.onclick = function () { handleFriendAction(userId); };
            } else {
                btn.disabled = false;
            }
        })
        .catch(() => { btn.disabled = false; });
}

function handleAcceptRequest(userId) {
    const btn = document.getElementById('friend-btn-' + userId);
    if (!btn) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('friend_id', userId);
    fetch('index.php?url=friend/accept', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fa fa-user-check"></i> <span>Friends</span>';
                btn.classList.replace('btn-primary', 'btn-ghost');
                btn.disabled = false;
                btn.onclick = function () { handleUnfriend(userId); };
            } else {
                btn.disabled = false;
            }
        })
        .catch(() => { btn.disabled = false; });
}

// ── Messages page ─────────────────────────────────────
function sendMessage(e) {
    e.preventDefault();
    const form  = e.target;
    const input = document.getElementById('message-input');
    const message = input ? input.value.trim() : '';
    if (!message) return;

    const fd = new FormData(form);
    const sendBtn = form.querySelector('button[type="submit"]');
    if (sendBtn) sendBtn.disabled = true;

    fetch('index.php?url=message/send', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.message) {
                appendMessage(data.message, true);
                input.value = '';
                scrollChatToBottom();
                // Update last message in conversation list
                const convPreview = document.querySelector('.conversation-item.active .conversation-preview');
                if (convPreview) convPreview.textContent = message;
            }
        })
        .catch(() => {})
        .finally(() => { if (sendBtn) sendBtn.disabled = false; });
}

function appendMessage(msg, isSent) {
    const container = document.getElementById('chat-messages');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'message ' + (isSent ? 'sent' : 'received');
    div.dataset.messageId = msg.id;

    let html = '';
    if (!isSent) {
        html += `<img src="assets/uploads/${escapeHtml(msg.profile_image || 'default.png')}"
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

function scrollChatToBottom() {
    const container = document.getElementById('chat-messages');
    if (container) container.scrollTop = container.scrollHeight;
}

function openNewMessageModal() {
    const modal = document.getElementById('new-message-modal');
    if (modal) {
        modal.style.display = 'flex';
        const search = document.getElementById('user-search');
        if (search) setTimeout(() => search.focus(), 50);
    }
}

let _searchTimeout;
function searchUsers(query) {
    clearTimeout(_searchTimeout);
    const results = document.getElementById('user-search-results');
    if (!results) return;

    if (query.length < 2) {
        results.innerHTML = '';
        return;
    }

    results.innerHTML = '<div style="padding:12px;text-align:center;color:#888;"><i class="fa fa-spinner fa-spin"></i></div>';

    _searchTimeout = setTimeout(() => {
        fetch('index.php?url=search&q=' + encodeURIComponent(query) + '&ajax=1')
            .then(r => r.json())
            .then(data => {
                results.innerHTML = '';
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        const a = document.createElement('a');
                        a.href = 'index.php?url=message/start&user=' + user.id;
                        a.className = 'user-result';
                        a.innerHTML = `
                            <img src="assets/uploads/${escapeHtml(user.profile_image || 'default.png')}"
                                 alt="avatar" class="avatar-sm"
                                 onerror="this.onerror=null; this.src='assets/images/default.png'">
                            <div>
                                <span class="user-name">${escapeHtml(user.full_name)}</span>
                                <span class="user-username">@${escapeHtml(user.username)}</span>
                            </div>`;
                        results.appendChild(a);
                    });
                } else {
                    results.innerHTML = '<p style="padding:12px;color:#888;text-align:center;">No users found</p>';
                }
            })
            .catch(() => { results.innerHTML = ''; });
    }, 300);
}

// Auto-scroll chat on load
document.addEventListener('DOMContentLoaded', () => {
    scrollChatToBottom();
});

// ── Auto-grow textarea ────────────────────────────────
document.querySelectorAll('textarea').forEach(ta => {
    ta.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
    });
});

// ── Toast auto-show (set via PHP session) ─────────────
(function () {
    const t = document.getElementById('main-toast');
    if (t) {
        requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('toast-show')));
        setTimeout(() => {
            t.classList.remove('toast-show');
            setTimeout(() => t.remove(), 380);
        }, 4500);
    }
    const tw = document.getElementById('main-toast-warning');
    if (tw) {
        requestAnimationFrame(() => requestAnimationFrame(() => tw.classList.add('toast-show')));
        setTimeout(() => {
            tw.classList.remove('toast-show');
            setTimeout(() => tw.remove(), 380);
        }, 6000);
    }
})();