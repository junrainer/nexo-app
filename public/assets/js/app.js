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

// ── Live timestamp refresh ───────────────────────────
function refreshLiveTimes() {
    document.querySelectorAll('time.live-time[data-time]').forEach(el => {
        el.textContent = timeAgo(el.dataset.time);
    });
}
setInterval(refreshLiveTimes, 30000);

// ── Avatar dropdown ──────────────────────────────────
const allDropdownsSelector = '.avatar-dropdown.open, .notification-dropdown.open, .message-dropdown.open';

function toggleAvatarMenu(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    const dd = document.getElementById('avatar-dropdown');
    if (!dd) return;
    const isOpen = dd.classList.contains('open');
    // Close all dropdowns first
    document.querySelectorAll(allDropdownsSelector).forEach(el => el.classList.remove('open'));
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
    document.querySelectorAll(allDropdownsSelector).forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        dd.classList.add('open');
        loadNotificationsInto('notif-list');
    }
}

document.addEventListener('click', e => {
    const menu = document.getElementById('notification-dropdown');
    const btn  = document.getElementById('notif-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

function loadNotifications() {
    loadNotificationsInto('notif-list');
}

function loadNotificationsInto(listId) {
    const list = document.getElementById(listId);
    if (!list) return;

    list.innerHTML = '<div class="notif-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';

    fetch('index.php?url=notifications')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => `
                    <a href="${escapeHtml(getNotificationLink(n))}"
                       class="notif-item ${n.is_read ? '' : 'unread'}"
                       onclick="markNotificationRead(${n.id})">
                        <img src="${n.actor_image && n.actor_image !== 'default.png' ? 'assets/uploads/' + escapeHtml(n.actor_image) : 'assets/images/default-profile.webp'}"
                             alt="avatar" class="notif-avatar"
                             onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                        <div class="notif-content">
                            <p>${escapeHtml(n.message)}</p>
                            <span class="notif-time"><time class="live-time" data-time="${escapeHtml(n.created_at)}">${timeAgo(n.created_at)}</time></span>
                        </div>
                        ${n.is_read ? '' : '<span class="notif-dot"></span>'}
                    </a>
                `).join('');
            } else {
                list.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i><p>No notifications yet</p></div>';
            }
        })
        .catch(() => {
            list.innerHTML = '<div class="notif-empty"><i class="fa fa-circle-exclamation"></i><p>Failed to load</p></div>';
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
    document.querySelectorAll(allDropdownsSelector).forEach(el => el.classList.remove('open'));
    if (!isOpen) {
        dd.classList.add('open');
        loadMessages();
    }
}

// Filter message dropdown items by name/preview
function filterMsgDropdown(q) {
    const items = document.querySelectorAll('#msg-list .msg-item');
    q = q.toLowerCase().trim();
    items.forEach(item => {
        const name = item.querySelector('.msg-name')?.textContent?.toLowerCase() || '';
        const preview = item.querySelector('.msg-preview')?.textContent?.toLowerCase() || '';
        item.style.display = (!q || name.includes(q) || preview.includes(q)) ? '' : 'none';
    });
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
                    <a href="#" class="msg-item ${c.unread > 0 ? 'unread' : ''}"
                       data-conv-id="${c.id}"
                       data-name="${escapeHtml(c.name)}"
                       data-avatar="${escapeHtml(c.avatar || '')}"
                       onclick="openFloatingChat(this.dataset.convId, this.dataset.name, this.dataset.avatar); return false;">
                        <div class="msg-avatar-wrap">
                            <img src="${c.avatar ? 'assets/uploads/' + escapeHtml(c.avatar) : 'assets/images/default-profile.webp'}"
                                 alt="avatar" class="msg-avatar"
                                 onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">
                            ${c.online ? '<div class="msg-online-dot"></div>' : ''}
                        </div>
                        <div class="msg-content">
                            <div class="msg-top">
                                <span class="msg-name ${c.unread > 0 ? 'bold' : ''}">${escapeHtml(c.name)}</span>
                                <span class="msg-time"><time class="live-time" data-time="${escapeHtml(c.last_time)}">${timeAgo(c.last_time)}</time></span>
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
function openEditPost(postId, content, visibility) {
    document.getElementById('edit-post-id').value      = postId;
    document.getElementById('edit-post-content').value = content;
    const visSelect = document.getElementById('edit-post-visibility');
    if (visSelect) visSelect.value = visibility || 'public';
    document.getElementById('edit-post-modal').style.display = 'flex';
}

// ── Audience cycle for compose form ─────────────────
const audienceOptions = [
    { value: 'public',  icon: 'fa-globe',      label: 'Public'   },
    { value: 'friends', icon: 'fa-user-group', label: 'Friends'  },
    { value: 'only_me', icon: 'fa-lock',       label: 'Only me'  },
];
function cycleAudience(btn) {
    const hidden = document.getElementById('compose-visibility');
    const icon   = document.getElementById('compose-audience-icon');
    const label  = document.getElementById('compose-audience-label');
    if (!hidden || !icon || !label) return;
    const current = audienceOptions.findIndex(o => o.value === hidden.value);
    const next    = audienceOptions[(current + 1) % audienceOptions.length];
    hidden.value   = next.value;
    icon.className = 'fa ' + next.icon;
    label.textContent = next.label;
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

// ── Media preview for compose (photos + video) ───────
const COMPOSE_MAX_PHOTOS    = 5;
const COMPOSE_MAX_VIDEO_GB  = 10;
const COMPOSE_MAX_VIDEO_BYTES = COMPOSE_MAX_VIDEO_GB * 1024 * 1024 * 1024;

function previewPostMedia(type, input) {
    const wrap = document.getElementById('media-previews');
    if (!input.files || !wrap) return;

    if (type === 'image') {
        // Cannot mix images with an already-chosen video
        if (wrap.querySelector('.preview-thumb[data-type="video"]')) {
            alert('Remove the video first before adding photos.');
            input.value = '';
            return;
        }

        const existing  = wrap.querySelectorAll('.preview-thumb[data-type="image"]').length;
        const available = COMPOSE_MAX_PHOTOS - existing;

        if (available <= 0) {
            alert(`You can only upload up to ${COMPOSE_MAX_PHOTOS} photos per post.`);
            input.value = '';
            return;
        }

        const files = Array.from(input.files).slice(0, available);
        if (input.files.length > available) {
            alert(`Only ${available} more photo(s) can be added (max ${COMPOSE_MAX_PHOTOS} total). Extra files were ignored.`);
        }

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = ev => {
                const div = document.createElement('div');
                div.className    = 'preview-thumb';
                div.dataset.type = 'image';
                div.innerHTML    = `<img src="${ev.target.result}" alt="preview">
                    <button type="button" class="preview-remove" onclick="removePostMediaPreview(this)">
                        <i class="fa fa-xmark"></i>
                    </button>`;
                wrap.appendChild(div);
                updateMediaCountHint();
            };
            reader.readAsDataURL(file);
        });

    } else if (type === 'video') {
        const file = input.files[0];
        if (!file) return;

        // Cannot mix video with already-chosen images
        if (wrap.querySelector('.preview-thumb[data-type="image"]')) {
            alert('Remove the photos first before adding a video.');
            input.value = '';
            return;
        }

        const validMime  = ['video/mp4', 'video/quicktime'];
        const validExt   = /\.(mp4|mov)$/i;

        if (file.size > COMPOSE_MAX_VIDEO_BYTES) {
            alert(`Video exceeds the ${COMPOSE_MAX_VIDEO_GB} GB size limit.`);
            input.value = '';
            return;
        }
        if (!validMime.includes(file.type) && !validExt.test(file.name)) {
            alert('Only MP4 or MOV video files are supported.');
            input.value = '';
            return;
        }

        // Replace any existing video preview
        wrap.querySelectorAll('.preview-thumb[data-type="video"]').forEach(el => el.remove());

        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.className    = 'preview-thumb preview-video-thumb';
        div.dataset.type = 'video';
        div.innerHTML    = `<video src="${url}" class="preview-video-el" preload="metadata" muted></video>
            <div class="preview-video-overlay"><i class="fa fa-play"></i></div>
            <button type="button" class="preview-remove" onclick="removePostMediaPreview(this)">
                <i class="fa fa-xmark"></i>
            </button>`;
        wrap.appendChild(div);
        updateMediaCountHint();
    }
}

function removePostMediaPreview(btn) {
    btn.parentElement.remove();
    // Reset file inputs so the user can re-select
    document.querySelectorAll('.compose-card input[type="file"]').forEach(inp => { inp.value = ''; });
    updateMediaCountHint();
}

function updateMediaCountHint() {
    const wrap      = document.getElementById('media-previews');
    const hint      = document.getElementById('media-count-hint');
    if (!wrap || !hint) return;
    const photoCount = wrap.querySelectorAll('.preview-thumb[data-type="image"]').length;
    hint.textContent = photoCount > 0 ? `${photoCount} / ${COMPOSE_MAX_PHOTOS} photo${photoCount === 1 ? '' : 's'}` : '';
}

// Keep legacy name working in case any older code calls it
function previewPostImage(input) { previewPostMedia('image', input); }

function clearFileInput() {
    document.querySelectorAll('.compose-card input[type="file"]').forEach(inp => { inp.value = ''; });
}

// ── Avatar preview (edit profile) ─────────────────────
function previewAvatar(input) {
    const img = document.getElementById('avatar-preview');
    if (!input.files || !input.files[0] || !img) return;
    const reader = new FileReader();
    reader.onload = ev => { img.src = ev.target.result; };
    reader.readAsDataURL(input.files[0]);
}

window.triggerCoverUpload = function () {
    const input = document.getElementById('cover-quick-input');
    if (!input) return;
    input.click();
};

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.querySelector('.js-cover-upload-btn');
    if (!btn) return;
    if (btn.dataset.coverBound === '1') return;
    btn.dataset.coverBound = '1';
    btn.addEventListener('click', function () {
        window.triggerCoverUpload();
    });

    const quickInput = document.getElementById('cover-quick-input');
    const quickForm = document.getElementById('cover-quick-form');
    if (!quickInput || !quickForm) return;
    if (quickInput.dataset.coverSubmitBound === '1') return;
    quickInput.dataset.coverSubmitBound = '1';
    quickInput.addEventListener('change', function () {
        if (!quickInput.files || quickInput.files.length === 0) return;
        const file = quickInput.files[0];
        if (!file) return;

        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 2 * 1024 * 1024;

        if (!allowed.includes(file.type)) {
            alert('Invalid cover image. Use JPG, PNG, GIF, or WEBP.');
            quickInput.value = '';
            return;
        }
        if (file.size > maxSize) {
            alert('Cover image is too large. Maximum size is 2MB.');
            quickInput.value = '';
            return;
        }

        quickForm.submit();
    });
});

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
    if (!btn) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('comment_id', commentId);
    fetch('index.php?url=comment/like', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (!data || data.success === false) return;
            btn.classList.toggle('liked', !!data.liked);
            const lbl = btn.querySelector('.comment-like-label');
            const cnt = btn.querySelector('.comment-like-count');
            if (lbl) lbl.textContent = data.liked ? 'Unlike' : 'Like';
            if (cnt) cnt.textContent = data.count > 0 ? data.count : '';
        })
        .catch(() => {})
        .finally(() => { btn.disabled = false; });
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
function switchFriendTab(tabName, btn) {
    document.querySelectorAll('.gm-tab-sw').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.gm-tab-panel').forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');
    const content = document.getElementById('tab-' + tabName);
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
        html += `<img src="${msg.profile_image ? 'assets/uploads/' + escapeHtml(msg.profile_image) : 'assets/images/default-profile.webp'}"
                      alt="avatar" class="message-avatar"
                      onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">`;
    }
    const sentTime = msg.created_at ? escapeHtml(msg.created_at) : '';
    const sentTimeText = msg.created_at ? timeAgo(msg.created_at) : 'just now';
    const sentTimeAttrs = msg.created_at
        ? ` class="live-time message-time" data-time="${sentTime}" datetime="${sentTime}"`
        : ' class="message-time"';
    html += `
            <div class="message-body">
                <div class="message-content">
                    <p>${escapeHtml(msg.message)}</p>
                </div>
                <time${sentTimeAttrs}>${sentTimeText}</time>
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

// ── Floating Chat Window ──────────────────────────────
let _floatingChatConvId   = null;
let _floatingChatLastMsgId = 0;
let _floatingChatPollTimer = null;

function openFloatingChat(convId, name, avatar) {
    _floatingChatConvId    = convId;
    _floatingChatLastMsgId = 0;

    // Close message dropdown
    const dd = document.getElementById('message-dropdown');
    if (dd) dd.classList.remove('open');

    // Set header info
    const nameEl   = document.getElementById('floating-chat-name');
    const avatarEl = document.getElementById('floating-chat-avatar');
    const linkEl   = document.getElementById('floating-chat-open-full');

    if (nameEl)   nameEl.textContent = name;
    if (avatarEl) avatarEl.src = avatar ? 'assets/uploads/' + avatar : 'assets/images/default-profile.webp';
    if (linkEl)   linkEl.href = 'index.php?url=messages&c=' + encodeURIComponent(convId);

    // Clear messages
    const msgs = document.getElementById('floating-chat-messages');
    if (msgs) msgs.innerHTML = '<div class="fc-loading"><i class="fa fa-spinner fa-spin"></i></div>';

    // Show window
    const overlay = document.getElementById('floating-chat-overlay');
    if (overlay) overlay.style.display = 'flex';

    // Load messages
    _loadFloatingChatMessages(convId);

    // Start polling
    clearInterval(_floatingChatPollTimer);
    _floatingChatPollTimer = setInterval(() => {
        if (_floatingChatConvId) _pollFloatingChat(_floatingChatConvId);
    }, 3000);
}

function closeFloatingChat() {
    const overlay = document.getElementById('floating-chat-overlay');
    if (overlay) overlay.style.display = 'none';
    clearInterval(_floatingChatPollTimer);
    _floatingChatConvId    = null;
    _floatingChatLastMsgId = 0;
    const recip = document.getElementById('floating-chat-recipient');
    if (recip) recip.value = '';
}

function _loadFloatingChatMessages(convId) {
    fetch('index.php?url=message/load&conversation_id=' + encodeURIComponent(convId))
        .then(r => r.json())
        .then(data => {
            const msgs = document.getElementById('floating-chat-messages');
            if (!msgs) return;
            msgs.innerHTML = '';

            if (data.success) {
                if (data.recipient_id) {
                    const recip = document.getElementById('floating-chat-recipient');
                    if (recip) recip.value = data.recipient_id;
                }
                (data.messages || []).forEach(msg => {
                    _appendFloatingMsg(msg.message, !!msg.is_mine, msg.profile_image, msg.created_at, msg.id);
                    _floatingChatLastMsgId = Math.max(_floatingChatLastMsgId, msg.id);
                });
                _scrollFloatingChatToBottom();
                const inp = document.getElementById('floating-chat-input');
                if (inp) inp.focus();
            }
        })
        .catch(() => {
            const msgs = document.getElementById('floating-chat-messages');
            if (msgs) msgs.innerHTML = '<div class="fc-loading">Failed to load</div>';
        });
}

function _pollFloatingChat(convId) {
    fetch(`index.php?url=message/new&conversation_id=${encodeURIComponent(convId)}&last_message_id=${_floatingChatLastMsgId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const shell = document.querySelector('.app-shell[data-user-id]');
                const myId  = shell ? parseInt(shell.dataset.userId, 10) : -1;
                data.messages.forEach(msg => {
                    if (parseInt(msg.sender_id) !== myId) {
                        _appendFloatingMsg(msg.message, false, msg.profile_image, msg.created_at, msg.id);
                    }
                    _floatingChatLastMsgId = Math.max(_floatingChatLastMsgId, msg.id);
                });
                _scrollFloatingChatToBottom();
            }
        })
        .catch(() => {});
}

function _appendFloatingMsg(text, isMine, profileImage, createdAt, msgId) {
    const container = document.getElementById('floating-chat-messages');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'message ' + (isMine ? 'sent' : 'received');
    if (msgId) div.dataset.messageId = msgId;

    let html = '';
    if (!isMine) {
        const src = profileImage ? 'assets/uploads/' + escapeHtml(profileImage) : 'assets/images/default-profile.webp';
        html += `<img src="${src}" alt="avatar" class="message-avatar"
                      onerror="this.onerror=null; this.src='assets/images/default-profile.webp'">`;
    }
    const timeStr = createdAt ? timeAgo(createdAt) : 'just now';
    const timeAttr = createdAt ? ` class="live-time message-time" data-time="${escapeHtml(createdAt)}"` : ' class="message-time"';
    html += `
            <div class="message-body">
                <div class="message-content">
                    <p>${escapeHtml(text)}</p>
                </div>
                <time${timeAttr}>${timeStr}</time>
            </div>`;
    div.innerHTML = html;
    container.appendChild(div);
}

function sendFloatingMessage(e) {
    e.preventDefault();
    const form     = e.target;
    const input    = document.getElementById('floating-chat-input');
    const message  = input ? input.value.trim() : '';
    if (!message) return;

    const fd = new FormData(form);
    fetch('index.php?url=message/send', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.message) {
                _appendFloatingMsg(data.message.message, true, null, data.message.created_at, data.message.id);
                _floatingChatLastMsgId = Math.max(_floatingChatLastMsgId, data.message.id);
                _scrollFloatingChatToBottom();
                if (input) input.value = '';
            }
        })
        .catch(() => {});
}

function _scrollFloatingChatToBottom() {
    const c = document.getElementById('floating-chat-messages');
    if (c) c.scrollTop = c.scrollHeight;
}

// ── Topbar search typeahead ───────────────────────────
(function initTopbarTypeahead() {
    const input = document.getElementById('topbar-search-input');
    const box   = document.getElementById('topbar-search-suggestions');
    if (!input || !box) return;

    let timer = null;
    const hide = () => { box.style.display = 'none'; box.innerHTML = ''; };

    input.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(timer);
        if (q.length < 2) {
            hide();
            return;
        }
        timer = setTimeout(() => {
            fetch('index.php?url=search&q=' + encodeURIComponent(q) + '&ajax=1')
                .then(r => r.json())
                .then(data => {
                    const users = (data && data.users) ? data.users.slice(0, 6) : [];
                    const posts = (data && data.posts) ? data.posts.slice(0, 4) : [];
                    const rows  = [];

                    users.forEach(u => {
                        rows.push(
                            `<a class="topbar-suggest-item" href="index.php?url=profile/${encodeURIComponent(u.username)}">
                                <img src="${u.profile_image && u.profile_image !== 'default.png' ? 'assets/uploads/' + escapeHtml(u.profile_image) : 'assets/images/default-profile.webp'}" alt="avatar">
                                <span><strong>${escapeHtml(u.full_name)}</strong><small>@${escapeHtml(u.username)}</small></span>
                             </a>`
                        );
                    });

                    posts.forEach(p => {
                        rows.push(
                            `<a class="topbar-suggest-item" href="index.php?url=search&q=${encodeURIComponent(q)}">
                                <i class="fa fa-file-lines"></i>
                                <span><strong>Post</strong><small>${escapeHtml((p.content || '').slice(0, 70))}</small></span>
                             </a>`
                        );
                    });

                    if (rows.length === 0) {
                        hide();
                        return;
                    }

                    rows.push(`<a class="topbar-suggest-all" href="index.php?url=search&q=${encodeURIComponent(q)}">See all results for "${escapeHtml(q)}"</a>`);
                    box.innerHTML = rows.join('');
                    box.style.display = 'block';
                })
                .catch(hide);
        }, 220);
    });

    input.addEventListener('focus', function () {
        if (box.innerHTML.trim() !== '') box.style.display = 'block';
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.topbar-search-form')) hide();
    });
})();

// ── Emoji Picker ─────────────────────────────────────
(function initEmojiPickers() {
    const EMOJIS = [
        '😀','😂','😍','🥰','😎','😊','🤣','😭','😢','😡',
        '🤔','😅','😴','😤','🤗','🥺','😱','🤯','🫠','😏',
        '👍','👎','👋','👏','🙌','🙏','💪','🤝','🫶','❤️',
        '🔥','✨','🌟','🎉','💯','🎶','🍕','🎮','💀','🤩',
    ];

    function buildPicker(el, targetInputId) {
        if (!el || el.dataset.built) return;
        el.dataset.built = '1';
        EMOJIS.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = emoji;
            btn.title = emoji;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                insertEmoji(targetInputId, emoji);
                el.classList.remove('open');
            });
            el.appendChild(btn);
        });
    }

    // Build pickers on first open
    window.toggleEmojiPicker = function (pickerId) {
        const picker = document.getElementById(pickerId);
        if (!picker) return;

        const inputId = pickerId === 'main-emoji-picker' ? 'message-input' : 'floating-chat-input';
        buildPicker(picker, inputId);

        const isOpen = picker.classList.contains('open');
        document.querySelectorAll('.emoji-picker.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) picker.classList.add('open');
    };

    window.insertEmoji = function (inputId, emoji) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const start = input.selectionStart ?? input.value.length;
        const end   = input.selectionEnd   ?? input.value.length;
        input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
        input.selectionStart = input.selectionEnd = start + emoji.length;
        input.focus();
    };

    // Close emoji pickers when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.emoji-btn-wrap')) {
            document.querySelectorAll('.emoji-picker.open').forEach(p => p.classList.remove('open'));
        }
    });
})();
