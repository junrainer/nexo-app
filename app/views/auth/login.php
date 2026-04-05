<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – Nexo</title>
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
            <div class="auth-logo-icon">N</div>
            <div class="auth-logo-name">Nexo</div>
            <p class="auth-logo-tag">Connect with your community</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fa fa-circle-exclamation"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fa fa-circle-check"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form action="index.php?url=login" method="POST">
            <?= $csrfField ?>

            <div class="auth-field">
                <div class="auth-input-wrap">
                    <i class="fa fa-user auth-input-icon"></i>
                    <input type="text" name="email" class="auth-input auth-input-icon-left"
                           placeholder="Email or Username" required autocomplete="username">
                </div>
            </div>

            <div class="auth-field">
                <div class="auth-input-wrap">
                    <i class="fa fa-lock auth-input-icon"></i>
                    <input type="password" name="password" id="loginPass"
                           class="auth-input auth-input-icon-left auth-input-icon-right"
                           placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="auth-eye-btn" onclick="togglePass('loginPass', this)">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="auth-row-split">
                <label class="auth-checkbox">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="index.php?url=forgot-password" class="auth-link-sm">Forgot password?</a>
            </div>

            <button type="submit" class="auth-btn">Sign in</button>
        </form>

    </div>

    <div class="auth-footer-card">
        Don't have an account? <a href="index.php?url=register">Sign up</a>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>