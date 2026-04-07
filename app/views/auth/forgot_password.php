<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password – Nexo</title>
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
            <img src="assets/images/app-logo.png" alt="Nexo" class="auth-logo-img"
                 onerror="this.style.display='none'; document.getElementById('auth-logo-fb-fp').style.display='flex'">
            <div id="auth-logo-fb-fp" class="auth-logo-icon" style="display:none"><i class="fa fa-lock"></i></div>
            <div class="auth-logo-name">Forgot Password?</div>
            <p class="auth-logo-tag">Enter your email and we'll send you a reset link.</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php
        $fpCooldown = 0;
        if (!empty($_SESSION['_fp_cooldown_seconds'])) {
            $fpCooldown = (int)$_SESSION['_fp_cooldown_seconds'];
            unset($_SESSION['_fp_cooldown_seconds']);
        }
        ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="auth-alert auth-alert-success">
                <i class="fa fa-circle-check"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form action="index.php?url=forgot-password" method="POST">
            <?= $csrfField ?>

            <div class="auth-field">
                <div class="auth-input-wrap">
                    <i class="fa fa-envelope auth-input-icon"></i>
                    <input type="text" name="email" class="auth-input auth-input-icon-left"
                           placeholder="Enter your email address" required autocomplete="email">
                </div>
            </div>

            <button type="submit" class="auth-btn" id="fp-submit-btn"
                    <?= $fpCooldown > 0 ? 'disabled' : '' ?>>Send Reset Link</button>
        </form>

        <?php if ($fpCooldown > 0): ?>
        <p class="auth-cooldown-msg" id="fp-cooldown-msg" style="text-align:center;margin-top:12px;font-size:13px;color:var(--muted-fg);">
            You can request another link in <strong id="fp-countdown"><?= $fpCooldown ?></strong> <span id="fp-countdown-label">second<?= $fpCooldown !== 1 ? 's' : '' ?></span>.
        </p>
        <script>
        (function () {
            var remaining = <?= $fpCooldown ?>;
            var el = document.getElementById('fp-countdown');
            var label = document.getElementById('fp-countdown-label');
            var btn = document.getElementById('fp-submit-btn');
            var msg = document.getElementById('fp-cooldown-msg');
            var interval = setInterval(function () {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(interval);
                    btn.disabled = false;
                    msg.style.display = 'none';
                } else {
                    el.textContent = remaining;
                    label.textContent = remaining === 1 ? 'second' : 'seconds';
                }
            }, 1000);
        })();
        </script>
        <?php endif; ?>

    </div>

    <div class="auth-footer-card">
        Remembered your password? <a href="index.php?url=login">Sign in</a>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
