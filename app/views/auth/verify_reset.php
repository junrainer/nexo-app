<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify It’s You – Nexo</title>
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
        <div class="auth-logo">
            <img src="assets/images/app-logo.png" alt="Nexo" class="auth-logo-img"
                 onerror="this.style.display='none'; document.getElementById('auth-logo-fb-vr').style.display='flex'">
            <div id="auth-logo-fb-vr" class="auth-logo-icon" style="display:none"><i class="fa fa-shield-check"></i></div>
            <div class="auth-logo-name">Verify if it’s you</div>
            <p class="auth-logo-tag">Confirm this request before creating a new password.</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($tokenValid)): ?>
            <form action="index.php?url=verify-reset" method="POST">
                <?= $csrfField ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit" class="auth-btn">Yes, it’s me</button>
            </form>
        <?php else: ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-triangle-exclamation"></i>
                <span>This verification link is invalid or has expired.</span>
            </div>
            <a href="index.php?url=forgot-password" class="auth-btn auth-btn-linklike">
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
