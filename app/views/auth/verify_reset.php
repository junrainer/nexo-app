<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Verification Code – Nexo</title>
    <?php
    require_once __DIR__ . '/../../../lib/Security.php';
    $csrfField = Security::field();
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .otp-input {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 12px;
            font-family: monospace;
        }
    </style>
</head>
<body class="auth-body">

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="assets/images/app-logo.png" alt="Nexo" class="auth-logo-img"
                 onerror="this.style.display='none'; document.getElementById('auth-logo-fb-vr').style.display='flex'">
            <div id="auth-logo-fb-vr" class="auth-logo-icon" style="display:none"><i class="fa fa-shield-halved"></i></div>
            <div class="auth-logo-name">Enter Verification Code</div>
            <p class="auth-logo-tag">Enter the 6-digit code we sent to your email.</p>
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
            <form action="index.php?url=verify-reset" method="POST">
                <?= $csrfField ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="auth-field">
                    <div class="auth-input-wrap">
                        <input type="text" name="code" class="auth-input otp-input"
                               placeholder="000000" maxlength="6" pattern="\d{6}"
                               inputmode="numeric" autocomplete="one-time-code" required autofocus>
                    </div>
                </div>

                <button type="submit" class="auth-btn">Verify Code</button>
            </form>

            <p style="text-align:center;margin-top:14px;font-size:13px;color:var(--muted-fg);">
                Didn't receive a code?
                <a href="index.php?url=forgot-password" style="color:var(--primary);">Request a new one</a>
            </p>
        <?php else: ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-triangle-exclamation"></i>
                <span>This verification link is invalid or has expired.</span>
            </div>
            <a href="index.php?url=forgot-password" class="auth-btn auth-btn-linklike">
                Request a new code
            </a>
        <?php endif; ?>
    </div>

    <div class="auth-footer-card">
        <a href="index.php?url=login">&larr; Back to Sign in</a>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
