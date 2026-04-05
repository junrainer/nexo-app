<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password – Nexo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-split">

    <!-- Left: Branding -->
    <div class="auth-hero">
        <div class="auth-hero-content">
            <h1 class="auth-hero-title">FORGOT<br>PASSWORD?</h1>
            <p class="auth-hero-sub">
                No worries! Enter your email and we'll send you a reset link right away.
            </p>
        </div>
    </div>

    <!-- Right: Form -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h2 class="auth-form-title">Reset your password</h2>
            <p class="auth-form-sub">Enter the email address linked to your Nexo account.</p>

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

            <form action="index.php?url=forgot-password" method="POST" class="auth-form">

                <div class="underline-field">
                    <label>Email Address</label>
                    <div class="underline-input-wrap">
                        <input type="email" name="email" placeholder="Enter your email" required autocomplete="email">
                        <i class="fa fa-envelope field-icon"></i>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Send Reset Link</button>

                <p class="auth-switch">
                    Remembered your password?
                    <a href="index.php?url=login">Sign in</a>
                </p>
            </form>
        </div>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
