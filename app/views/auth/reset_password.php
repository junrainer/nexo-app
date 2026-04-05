<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – Nexo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-split auth-split-reverse">

    <!-- Left: Form -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h2 class="auth-form-title">Set a new password</h2>
            <p class="auth-form-sub">Choose a strong password you haven't used before.</p>

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

            <?php if (!empty($tokenValid)): ?>
            <form action="index.php?url=reset-password" method="POST" class="auth-form">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="underline-field">
                    <label>New Password</label>
                    <div class="underline-input-wrap">
                        <input type="password" name="password" id="resetPass" placeholder="At least 6 characters" required>
                        <button type="button" class="field-eye" onclick="togglePass('resetPass', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="underline-field">
                    <label>Confirm New Password</label>
                    <div class="underline-input-wrap">
                        <input type="password" name="confirm_password" id="resetPassConfirm" placeholder="Repeat password" required>
                        <button type="button" class="field-eye" onclick="togglePass('resetPassConfirm', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Update Password</button>
            </form>
            <?php else: ?>
                <p class="auth-switch" style="text-align:center; margin-top:16px;">
                    This reset link is invalid or has expired.
                    <a href="index.php?url=forgot-password">Request a new link</a>
                </p>
            <?php endif; ?>

            <p class="auth-switch" style="margin-top:16px;">
                <a href="index.php?url=login">← Back to Sign in</a>
            </p>
        </div>
    </div>

    <!-- Right: Branding -->
    <div class="auth-hero">
        <div class="auth-hero-content">
            <h1 class="auth-hero-title">ALMOST<br>THERE</h1>
            <p class="auth-hero-sub">
                Create a strong password to keep your Nexo account safe.
            </p>
        </div>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
