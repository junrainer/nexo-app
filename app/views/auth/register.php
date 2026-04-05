<?php
require_once __DIR__ . '/../../../lib/Security.php';
$csrfField = Security::field();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Nexo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-split-body">

<div class="auth-split-page">

    <!-- ══════════════════════════════════════════════════════
         LEFT – Branding / Hero panel
         ══════════════════════════════════════════════════════ -->
    <div class="auth-hero">
        <!-- Decorative blurred shapes -->
        <!-- TODO: Replace these CSS shapes with your own design asset images if available -->
        <div class="auth-hero-shape auth-hero-shape-1"></div>
        <div class="auth-hero-shape auth-hero-shape-2"></div>
        <div class="auth-hero-shape auth-hero-shape-3"></div>

        <!-- Top-left logo -->
        <div class="auth-hero-logo">
            <!-- TODO: Swap the "N" icon for an actual logo image: <img src="assets/images/logo.svg" …> -->
            <div class="auth-hero-logo-icon">N</div>
            <span class="auth-hero-logo-name">Nexo</span>
        </div>

        <!-- Main heading + sub text + pills -->
        <div class="auth-hero-body">
            <h1 class="auth-hero-heading">Connect with your campus community</h1>
            <p class="auth-hero-sub">Share moments, exchange ideas, and stay connected with your classmates.</p>

            <div class="auth-hero-pills">
                <span class="auth-pill"><i class="fa-solid fa-bolt"></i> Post updates</span>
                <span class="auth-pill"><i class="fa-solid fa-comment-dots"></i> Comment &amp; discuss</span>
                <span class="auth-pill"><i class="fa-solid fa-heart"></i> Like &amp; react</span>
                <span class="auth-pill"><i class="fa-solid fa-user-group"></i> Discover peers</span>
            </div>
        </div>

        <p class="auth-hero-footer">Web Systems and Technologies — Final Project</p>
    </div>

    <!-- ══════════════════════════════════════════════════════
         RIGHT – Auth form panel
         ══════════════════════════════════════════════════════ -->
    <div class="auth-form-panel">
        <div class="auth-form-card">

            <!-- Toggle tabs: Create Account active -->
            <div class="auth-tab-toggle">
                <button class="auth-tab-btn" id="tabSignIn"
                        type="button" onclick="switchAuthTab('signin')">Sign In</button>
                <button class="auth-tab-btn active" id="tabCreateAccount"
                        type="button" onclick="switchAuthTab('register')">Create Account</button>
            </div>

            <!-- Flash messages (register errors land here) -->
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

            <!-- ── SIGN IN FORM (hidden on this page) ────────────── -->
            <div id="authFormSignIn" class="auth-tab-pane" style="display:none">

                <h2 class="auth-form-title">Welcome back 👋</h2>

                <form action="index.php?url=login" method="POST">
                    <?= $csrfField ?>

                    <div class="auth-field">
                        <label class="auth-label">Email address</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-envelope auth-input-icon"></i>
                            <input type="text" name="email"
                                   class="auth-input auth-input-icon-left"
                                   placeholder="Email or username" required
                                   autocomplete="username">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label class="auth-label">Password</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-lock auth-input-icon"></i>
                            <input type="password" name="password" id="loginPass"
                                   class="auth-input auth-input-icon-left auth-input-icon-right"
                                   placeholder="Password" required
                                   autocomplete="current-password">
                            <button type="button" class="auth-eye-btn"
                                    onclick="togglePass('loginPass', this)">
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

                    <button type="submit" class="auth-btn">Sign In</button>
                </form>

                <p class="auth-form-switch">
                    Don't have an account?
                    <a href="#" onclick="switchAuthTab('register'); return false;">Create one</a>
                </p>
            </div><!-- /authFormSignIn -->

            <!-- ── REGISTER FORM (visible) ────────────────────────── -->
            <div id="authFormRegister" class="auth-tab-pane">

                <h2 class="auth-form-title">Join Nexo 🚀</h2>

                <form action="index.php?url=register" method="POST"
                      enctype="multipart/form-data" id="registerForm">
                    <?= $csrfField ?>

                    <!-- Profile photo upload -->
                    <div class="auth-field reg-avatar-field">
                        <label class="auth-label">Profile photo <span class="auth-optional">(optional)</span></label>
                        <div class="reg-avatar-wrap">
                            <div class="reg-avatar-preview" id="regAvatarPreview">
                                <i class="fa fa-user reg-avatar-icon" id="regAvatarIcon"></i>
                                <img id="regAvatarImg" src="" alt="Preview" style="display:none">
                            </div>
                            <label class="reg-avatar-label" for="profileImageInput">
                                <i class="fa fa-camera"></i> Choose photo
                            </label>
                            <!-- TODO: Wire upload to your storage backend in AuthController::register() -->
                            <input type="file" name="profile_image" id="profileImageInput"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   style="display:none">
                            <span class="reg-avatar-hint">Click to upload profile photo (optional)</span>
                        </div>
                    </div>

                    <!-- First + Last name -->
                    <div class="auth-row-two">
                        <div class="auth-field">
                            <label class="auth-label">First Name</label>
                            <div class="auth-input-wrap">
                                <input type="text" name="first_name" class="auth-input"
                                       placeholder="First name" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label class="auth-label">Last Name</label>
                            <div class="auth-input-wrap">
                                <input type="text" name="last_name" class="auth-input"
                                       placeholder="Last name" required>
                            </div>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="auth-field">
                        <label class="auth-label">Username</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-at auth-input-icon"></i>
                            <input type="text" name="username"
                                   class="auth-input auth-input-icon-left"
                                   placeholder="Choose a username" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="auth-field">
                        <label class="auth-label">Email Address</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-envelope auth-input-icon"></i>
                            <input type="email" name="email"
                                   class="auth-input auth-input-icon-left"
                                   placeholder="Enter your email" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="auth-field">
                        <label class="auth-label">Password</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-lock auth-input-icon"></i>
                            <input type="password" name="password" id="regPass"
                                   class="auth-input auth-input-icon-left auth-input-icon-right"
                                   placeholder="Create a password" required
                                   autocomplete="new-password">
                            <button type="button" class="auth-eye-btn"
                                    onclick="togglePass('regPass', this)">
                                <i class="fa fa-eye-slash"></i>
                            </button>
                        </div>
                        <p class="auth-input-hint">Minimum 6 characters required</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="auth-field">
                        <label class="auth-label">Confirm Password</label>
                        <div class="auth-input-wrap">
                            <i class="fa fa-lock auth-input-icon"></i>
                            <input type="password" name="confirm_password" id="regPassConfirm"
                                   class="auth-input auth-input-icon-left auth-input-icon-right"
                                   placeholder="Repeat your password" required
                                   autocomplete="new-password">
                            <button type="button" class="auth-eye-btn"
                                    onclick="togglePass('regPassConfirm', this)">
                                <i class="fa fa-eye-slash"></i>
                            </button>
                        </div>
                        <p class="auth-input-hint" id="regPassMismatch"
                           style="display:none; color:var(--danger)">
                            Passwords do not match.
                        </p>
                    </div>

                    <!-- Bio (optional) -->
                    <!-- TODO: Add a 'bio' column to the users table and save it in AuthController::register() -->
                    <div class="auth-field">
                        <label class="auth-label">Bio <span class="auth-optional">(optional)</span></label>
                        <div class="auth-input-wrap">
                            <textarea name="bio" class="auth-input auth-textarea"
                                      placeholder="Tell people a little about yourself…"
                                      maxlength="200"></textarea>
                        </div>
                    </div>

                    <p class="auth-terms">
                        By creating an account you agree to our
                        <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </p>

                    <button type="submit" class="auth-btn">Create Account</button>
                </form>

                <p class="auth-form-switch">
                    Already have an account?
                    <a href="#" onclick="switchAuthTab('signin'); return false;">Sign in</a>
                </p>
            </div><!-- /authFormRegister -->

        </div><!-- /auth-form-card -->
    </div><!-- /auth-form-panel -->

</div><!-- /auth-split-page -->

<script src="assets/js/app.js"></script>
<script>
/* ── Avatar preview (register form) ── */
(function () {
    var input   = document.getElementById('profileImageInput');
    var img     = document.getElementById('regAvatarImg');
    var icon    = document.getElementById('regAvatarIcon');
    var hint    = document.querySelector('.reg-avatar-hint');
    if (!input) return;
    var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var maxSize = 2 * 1024 * 1024;
    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        if (!allowed.includes(file.type)) {
            hint.textContent = 'Unsupported format. Please use JPEG, PNG, GIF or WebP.';
            hint.style.color = 'var(--danger)';
            input.value = '';
            return;
        }
        if (file.size > maxSize) {
            hint.textContent = 'File too large. Maximum size is 2 MB.';
            hint.style.color = 'var(--danger)';
            input.value = '';
            return;
        }
        hint.textContent = 'Click to upload profile photo (optional)';
        hint.style.color = '';
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = 'block';
            icon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
}());

/* ── Tab switching ── */
function switchAuthTab(tab) {
    var isSignIn = tab === 'signin';
    document.getElementById('authFormSignIn').style.display    = isSignIn ? '' : 'none';
    document.getElementById('authFormRegister').style.display  = isSignIn ? 'none' : '';
    document.getElementById('tabSignIn').classList.toggle('active', isSignIn);
    document.getElementById('tabCreateAccount').classList.toggle('active', !isSignIn);
}

/* ── Confirm-password match check ── */
(function () {
    var pass    = document.getElementById('regPass');
    var confirm = document.getElementById('regPassConfirm');
    var hint    = document.getElementById('regPassMismatch');
    var form    = document.getElementById('registerForm');
    if (!form) return;
    function check() {
        if (confirm.value && pass.value !== confirm.value) {
            hint.style.display = '';
        } else {
            hint.style.display = 'none';
        }
    }
    pass.addEventListener('input', check);
    confirm.addEventListener('input', check);
    form.addEventListener('submit', function (e) {
        if (pass.value !== confirm.value) {
            e.preventDefault();
            hint.style.display = '';
            confirm.focus();
        }
    });
}());
</script>
</body>
</html>