<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – Nexo</title>
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
            <h1 class="auth-hero-title">WELCOME<br>BACK</h1>
            <p class="auth-hero-sub">
                Connect with classmates, share your thoughts, and discover
                what your community is talking about.
            </p>
        </div>
    </div>

    <!-- Right: Form -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h2 class="auth-form-title">Welcome to Nexo</h2>

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

            <form action="index.php?url=login" method="POST" class="auth-form">

                <div class="underline-field">
                    <label>Email or Username</label>
                    <div class="underline-input-wrap">
                        <input type="email" name="email" placeholder="" required autocomplete="email">
                        <i class="fa fa-user field-icon"></i>
                    </div>
                </div>

                <div class="underline-field">
                    <label>Password</label>
                    <div class="underline-input-wrap">
                        <input type="password" name="password" id="loginPass" placeholder="" required autocomplete="current-password">
                        <button type="button" class="field-eye" onclick="togglePass('loginPass', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <button type="button" class="forgot-link">Forgot password?</button>
                </div>

                <button type="submit" class="btn-auth">Sign in</button>

                <p class="auth-switch">
                    Don't have an account?
                    <a href="index.php?url=register">Sign up</a>
                </p>
            </form>
        </div>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>