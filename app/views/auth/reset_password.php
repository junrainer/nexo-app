<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – Nexo</title>
    <?php
    require_once __DIR__ . '/../../../lib/Security.php';
    $csrfField = Security::field();
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-page">

    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <img src="assets/images/logo.svg" alt="Nexo" class="auth-logo-img"
                 onerror="this.style.display='none'; document.getElementById('auth-logo-fb-rp').style.display='flex'">
            <div id="auth-logo-fb-rp" class="auth-logo-icon" style="display:none"><i class="fa fa-key"></i></div>
            <div class="auth-logo-name">Set New Password</div>
            <p class="auth-logo-tag">Choose a strong password you haven't used before.</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="auth-alert auth-alert-success">
                <i class="fa fa-circle-check"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($tokenValid)): ?>
        <form action="index.php?url=reset-password" method="POST">
            <?= $csrfField ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="auth-field">
                <label class="auth-label">New Password</label>
                <div class="auth-input-wrap">
                    <i class="fa fa-lock auth-input-icon"></i>
                    <input type="password" name="password" id="resetPass"
                           class="auth-input auth-input-icon-left auth-input-icon-right"
                           placeholder="At least 6 characters" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePass('resetPass', this)">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="auth-field">
                <label class="auth-label">Confirm New Password</label>
                <div class="auth-input-wrap">
                    <i class="fa fa-lock auth-input-icon"></i>
                    <input type="password" name="confirm_password" id="resetConfirm"
                           class="auth-input auth-input-icon-left auth-input-icon-right"
                           placeholder="Repeat your password" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePass('resetConfirm', this)">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-btn">Update Password</button>
        </form>
        <?php else: ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-triangle-exclamation"></i>
                <span>This reset link is invalid or has expired.</span>
            </div>
            <a href="index.php?url=forgot-password" class="auth-btn" style="display:block; text-align:center; text-decoration:none; line-height:44px; margin-top:0;">
                Request a new link
            </a>
        <?php endif; ?>

    </div>

    <div class="auth-footer-card">
        <a href="index.php?url=login">← Back to Sign in</a>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
