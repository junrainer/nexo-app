<?php
$pageTitle = 'Settings – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="settings-page">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Settings page title -->
    <h1 class="settings-title"><i class="fa fa-gear"></i> Settings</h1>

    <!-- Horizontal tab navigation -->
    <div class="settings-tabs">
        <button class="settings-tab active" onclick="showSettingsPanel('account', this)">
            <i class="fa fa-user-shield"></i> Account
        </button>
        <button class="settings-tab" onclick="showSettingsPanel('preferences', this)">
            <i class="fa fa-sliders"></i> Preferences
        </button>
        <button class="settings-tab" onclick="showSettingsPanel('privacy', this)">
            <i class="fa fa-lock"></i> Privacy
        </button>
        <button class="settings-tab" onclick="showSettingsPanel('danger', this)">
            <i class="fa fa-triangle-exclamation"></i> Danger Zone
        </button>
    </div>

    <!-- Settings content frame (full-width, no sidebar) -->
    <div class="settings-frame">

        <!-- Account Panel -->
        <div class="settings-panel active" id="panel-account">
            <div class="settings-panel-header">
                <i class="fa fa-user-shield"></i>
                <div>
                    <h3>Account</h3>
                    <p>Update your email address and password.</p>
                </div>
            </div>
            <form action="index.php?url=settings/account" method="POST" class="settings-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-input"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-input"
                           placeholder="Required to change password">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-input"
                               placeholder="Leave blank to keep current">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input"
                               placeholder="Confirm new password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Account Settings</button>
            </form>
        </div>

        <!-- Preferences Panel -->
        <div class="settings-panel" id="panel-preferences">
            <div class="settings-panel-header">
                <i class="fa fa-sliders"></i>
                <div>
                    <h3>Preferences</h3>
                    <p>Customize how Nexo looks and behaves for you.</p>
                </div>
            </div>
            <form action="index.php?url=settings/preferences" method="POST" class="settings-form">
                <div class="setting-toggle">
                    <div class="setting-info">
                        <span class="setting-label"><i class="fa fa-moon"></i> Dark Mode</span>
                        <span class="setting-desc">Use dark theme throughout the app</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="dark_mode" <?= $preferences['dark_mode'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="setting-toggle">
                    <div class="setting-info">
                        <span class="setting-label"><i class="fa fa-envelope"></i> Email Notifications</span>
                        <span class="setting-desc">Receive email updates about activity</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="email_notifications" <?= $preferences['email_notifications'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="setting-toggle">
                    <div class="setting-info">
                        <span class="setting-label"><i class="fa fa-bell"></i> Push Notifications</span>
                        <span class="setting-desc">Receive browser notifications</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="push_notifications" <?= $preferences['push_notifications'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </form>
        </div>

        <!-- Privacy Panel -->
        <div class="settings-panel" id="panel-privacy">
            <div class="settings-panel-header">
                <i class="fa fa-lock"></i>
                <div>
                    <h3>Privacy</h3>
                    <p>Control who can interact with you and see your content.</p>
                </div>
            </div>
            <form action="index.php?url=settings/preferences" method="POST" class="settings-form">
                <input type="hidden" name="dark_mode"            value="<?= $preferences['dark_mode'] ? '1' : '0' ?>">
                <input type="hidden" name="email_notifications"  value="<?= $preferences['email_notifications'] ? '1' : '0' ?>">
                <input type="hidden" name="push_notifications"   value="<?= $preferences['push_notifications'] ? '1' : '0' ?>">
                <div class="form-group">
                    <label for="friend_requests_privacy">Who can send you friend requests?</label>
                    <select name="friend_requests_privacy" id="friend_requests_privacy" class="form-select">
                        <option value="everyone"          <?= $preferences['friend_requests_privacy'] === 'everyone'          ? 'selected' : '' ?>>Everyone</option>
                        <option value="friends_of_friends" <?= $preferences['friend_requests_privacy'] === 'friends_of_friends' ? 'selected' : '' ?>>Friends of friends</option>
                        <option value="nobody"            <?= $preferences['friend_requests_privacy'] === 'nobody'            ? 'selected' : '' ?>>Nobody</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="post_privacy">Default post visibility</label>
                    <select name="post_privacy" id="post_privacy" class="form-select">
                        <option value="public"  <?= $preferences['post_privacy'] === 'public'  ? 'selected' : '' ?>>Public – Anyone can see</option>
                        <option value="friends" <?= $preferences['post_privacy'] === 'friends' ? 'selected' : '' ?>>Friends – Only friends can see</option>
                        <option value="private" <?= $preferences['post_privacy'] === 'private' ? 'selected' : '' ?>>Private – Only you can see</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
            </form>
        </div>

        <!-- Danger Zone Panel -->
        <div class="settings-panel" id="panel-danger">
            <div class="settings-panel-header danger-header">
                <i class="fa fa-triangle-exclamation"></i>
                <div>
                    <h3>Danger Zone</h3>
                    <p>Irreversible actions — proceed with caution.</p>
                </div>
            </div>
            <div class="danger-actions">
                <div class="danger-action">
                    <div>
                        <strong>Deactivate Account</strong>
                        <p>Temporarily disable your account. You can reactivate anytime.</p>
                    </div>
                    <button class="btn btn-ghost danger" onclick="alert('Feature coming soon!')">Deactivate</button>
                </div>
                <div class="danger-action">
                    <div>
                        <strong>Delete Account</strong>
                        <p>Permanently delete your account and all data. This cannot be undone.</p>
                    </div>
                    <button class="btn btn-danger" onclick="alert('Feature coming soon!')">Delete Account</button>
                </div>
            </div>
        </div>

    </div><!-- end settings-frame -->
</div>

<script>
function showSettingsPanel(name, btn) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('panel-' + name);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
}

// Activate panel from URL hash on load
(function () {
    const VALID = ['account', 'preferences', 'privacy', 'danger'];
    const hash  = location.hash.replace('#', '');
    if (VALID.includes(hash)) {
        const btn = document.querySelector(`.settings-tab[onclick*="'${hash}'"]`);
        if (btn) showSettingsPanel(hash, btn);
    }
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

