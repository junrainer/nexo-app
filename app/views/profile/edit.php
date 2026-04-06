<?php
$pageTitle = 'Edit Profile – Nexo';
require __DIR__ . '/../partials/header.php';
?>

<div class="form-page">
    <div class="form-card">
        <h2><i class="fa fa-user-pen"></i> Edit Profile</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="/profile/update" method="POST" enctype="multipart/form-data" class="auth-form">
            <div class="avatar-upload-wrap">
                <img src="/assets/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="avatar" class="avatar-profile" id="avatar-preview" onerror="this.onerror=null; this.src='/assets/images/default-profile.webp'">
                <label class="btn-ghost btn-sm avatar-upload-btn">
                    <i class="fa fa-camera"></i> Change Photo
                    <input type="file" name="profile_image" accept="image/*" hidden onchange="previewAvatar(this)">
                </label>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <div class="input-icon">
                    <i class="fa fa-id-card"></i>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="3" placeholder="Tell people a bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/profile/<?= htmlspecialchars($user['username']) ?>" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>