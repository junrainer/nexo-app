// Nexo – app.js

// ── Password eye toggle ──────────────────────────────
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

// ── Avatar dropdown ──────────────────────────────────
function toggleAvatarMenu(e) {
    if (e) e.stopPropagation();
    const dd = document.getElementById('avatar-dropdown');
    if (dd) dd.classList.toggle('open');
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
    if (e) e.preventDefault();
    if (e) e.stopPropagation();
    const dd = document.getElementById('notification-dropdown');
    if (dd) {
        dd.classList.toggle('open');
        if (dd.classList.contains('open')) {
            loadNotifications();
        }
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('notification-dropdown');
    const btn  = document.getElementById('notif-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});


// ── Messages dropdown ────────────────────────────────
function toggleMessages(e) {
    if (e) e.preventDefault();
    if (e) e.stopPropagation();
    const dd = document.getElementById('message-dropdown');
    if (dd) {
        dd.classList.toggle('open');
        if (dd.classList.contains('open')) {
            loadMessagePreviews();
        }
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('message-dropdown');
    const btn  = document.getElementById('msg-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

function loadMessagePreviews() {
    const list = document.getElementById('msg-list');
    if (!list) return;

    fetch('index.php?url=message/unread')
        .then(r => r.json())
        .then(data => {
            fetch('index.php?url=messages/preview')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.conversations && data.conversations.length > 0) {
                        list.innerHTML = data.conversations.map(c => `
                            <a href="index.php?url=messages?with=${c.user_id}" class="notif-item ${c.unread > 0 ? 'unread' : ''}">
                                <img src="assets/uploads/${c.profile_image || 'default.png'}" alt="avatar" class="notif-avatar"
                                     onerror="this.onerror=null; this.src='assets/images/default.png'">
                                <div class="notif-content">
                                    <p><strong>${escapeHtml(c.full_name)}</strong></p>
                                    <span class="notif-time">${escapeHtml(c.last_message)}</span>
                                </div>
                                ${c.unread > 0 ? `<span class="notif-dot"></span>` : ''}
                            </a>
                        `).join('');
                    } else {
                        list.innerHTML = '<div class="notif-empty"><i class="fa fa-comment-slash"></i><p>No messages yet</p></div>';
                    }
                })
                .catch(() => {
                    list.innerHTML = '<a href="index.php?url=messages" class="notif-item"><div class="notif-content"><p>Open Messages</p></div></a>';
                });
        })
        .catch(() => {
            list.innerHTML = '<a href="index.php?url=messages" class="notif-item"><div class="notif-content"><p>Open Messages</p></div></a>';
        });
}

function loadNotifications() {
    const list = document.getElementById('notif-list');
    if (!list) return;
    
    fetch('index.php?url=notifications')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.notifications) {
                if (data.notifications.length === 0) {
                    list.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i><p>No notifications yet</p></div>';
                } else {
                    list.innerHTML = data.notifications.map(n => `
                        <a href="${getNotificationLink(n)}" class="notif-item ${n.is_read ? '' : 'unread'}" onclick="markNotificationRead(${n.id})">
                            <img src="assets/uploads/${n.actor_image || 'default.png'}" alt="avatar" class="notif-avatar"
                                 onerror="this.onerror=null; this.src='assets/images/default.png'">
                            <div class="notif-content">
                                <p>${escapeHtml(n.message)}</p>
                                <span class="notif-time">${timeAgo(n.created_at)}</span>
                            </div>
                        </a>
                    `).join('');
                }
            }
        })
        .catch(() => {
            list.innerHTML = '<div class="notif-error">Failed to load notifications</div>';
        });
}

function getNotificationLink(n) {
    switch (n.type) {
        case 'like':
        case 'comment':
            return `index.php?url=feed#post-${n.related_id}`;
        case 'friend_request':
        case 'friend_accept':
            return `index.php?url=profile/${n.actor_username}`;
        default:
            return '#';
    }
}

function markNotificationRead(notifId) {
    const fd = new FormData();
    fd.append('notification_id', notifId);
    fetch('index.php?url=notification/read', { method: 'POST', body: fd });
}

function markAllNotificationsRead() {
    fetch('index.php?url=notifications/read', { method: 'POST' })
        .then(() => {
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            updateNotificationBadge(0);
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
    // Notifications
    fetch('index.php?url=notifications/count')
        .then(r => r.json())
        .then(data => {
            if (data.success) updateNotificationBadge(data.count);
        })
        .catch(() => {});
    
    // Messages
    fetch('index.php?url=message/unread')
        .then(r => r.json())
        .then(data => {
            if (data.success) updateMessageBadge(data.count);
        })
        .catch(() => {});
}

// Fetch badges on page load and every 30 seconds
if (document.querySelector('.topbar')) {
    fetchBadgeCounts();
    setInterval(fetchBadgeCounts, 30000);
}

// ── Dark mode toggle ─────────────────────────────────
function toggleDarkMode() {
    fetch('index.php?url=settings/darkmode', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.body.classList.toggle('light-mode', !data.dark_mode);
            }
        });
}

// ── Helper: time ago ─────────────────────────────────
function timeAgo(datetime) {
    const diff = Math.floor((Date.now() - new Date(datetime).getTime()) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return new Date(datetime).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ── Helper: escape HTML ──────────────────────────────
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ── Save/Unsave post ─────────────────────────────────
function toggleSave(postId, btn) {
    const isSaved = btn.dataset.saved === '1';
    const url = isSaved ? 'index.php?url=post/unsave' : 'index.php?url=post/save';
    
    const fd = new FormData();
    fd.append('post_id', postId);
    
    fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('i');
                if (data.saved) {
                    btn.dataset.saved = '1';
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                } else {
                    btn.dataset.saved = '0';
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
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
    section.style.display = section.style.display === 'none' ? 'flex' : 'none';
}

// ── AJAX Like ────────────────────────────────────────
function toggleLike(postId, btn) {
    const fd = new FormData();
    fd.append('post_id', postId);

    fetch('index.php?url=post/like', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            const countEl = btn.querySelector('.like-count');
            if (countEl) countEl.textContent = data.count > 0 ? data.count : '';
            btn.classList.toggle('liked', data.liked);
        })
        .catch(() => {});
}

// ── Edit post modal ───────────────────────────────────
function openEditPost(postId, content) {
    document.getElementById('edit-post-id').value      = postId;
    document.getElementById('edit-post-content').value = content;
    document.getElementById('edit-post-modal').style.display = 'flex';
}

// ── Edit comment inline ───────────────────────────────
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

// Close modal on overlay click
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
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
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'preview-thumb';
            div.innerHTML = `<img src="${e.target.result}" alt="preview">
                <button type="button" class="preview-remove" onclick="this.parentElement.remove()">
                    <i class="fa fa-xmark"></i>
                </button>`;
            wrap.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// ── Avatar preview (edit profile) ─────────────────────
function previewAvatar(input) {
    const img = document.getElementById('avatar-preview');
    if (!input.files || !input.files[0] || !img) return;
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; };
    reader.readAsDataURL(input.files[0]);
}

// ── Tab switching (profile page) ──────────────────────
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

// ── Auto-dismiss alerts ───────────────────────────────
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.4s';
        alert.style.opacity    = '0';
        setTimeout(() => alert.remove(), 400);
    }, 4000);
});

// ── CSRF: auto-inject token into all POST forms & fetch calls ──
(function () {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf      = tokenMeta ? tokenMeta.content : '';
    if (!csrf) return;

    // 1. Automatically append _token to every <form method="post"> on submit
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.method && form.method.toLowerCase() === 'post') {
            if (!form.querySelector('input[name="_token"]')) {
                const hidden = document.createElement('input');
                hidden.type  = 'hidden';
                hidden.name  = '_token';
                hidden.value = csrf;
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
                if (!opts.body.has('_token')) {
                    opts.body.append('_token', csrf);
                }
            } else if (!opts.body) {
                // No body at all — create a FormData with just the token
                const fd = new FormData();
                fd.append('_token', csrf);
                opts.body = fd;
            }
        }
        return _fetch(url, opts);
    };
})();

// ── Mobile sidebar (hamburger) ────────────────────────
function openSidebar() {
    const sidebar  = document.getElementById('main-sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (sidebar)  sidebar.classList.add('sidebar-open');
    if (overlay)  overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar  = document.getElementById('main-sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (sidebar)  sidebar.classList.remove('sidebar-open');
    if (overlay)  overlay.classList.remove('active');
    document.body.style.overflow = '';
}
