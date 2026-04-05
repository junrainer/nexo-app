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
    <style>
        /* ── Auth split layout ── */
        .auth-split-page {
            display: flex;
            min-height: 100vh;
            background: #fff;
        }

        /* ── Purple hero (left) ── */
        .auth-hero-panel {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #6d28d9 60%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            padding: 40px 48px;
            position: relative;
            overflow: hidden;
        }

        .hero-circles-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .hero-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }

        .hero-circle-1 { width: 380px; height: 380px; top: -80px; left: -80px; }
        .hero-circle-2 { width: 500px; height: 500px; bottom: -180px; right: -180px; }

        .hero-logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 2;
        }

        .hero-logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-logo-icon i { color: #fff; font-size: 18px; }

        .hero-logo-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }

        .hero-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 2;
            max-width: 420px;
        }

        .hero-headline {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .hero-sub {
            font-size: 15px;
            color: rgba(255,255,255,0.75);
            line-height: 1.65;
            margin-bottom: 36px;
        }

        .hero-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 99px;
            padding: 7px 14px;
            font-size: 13px;
            color: #fff;
            font-weight: 500;
        }

        .hero-footer {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            position: relative;
            z-index: 2;
        }

        /* ── Right panel ── */
        .auth-right-panel {
            width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #f9fafb;
        }

        .auth-right-inner {
            width: 100%;
            max-width: 400px;
        }

        /* Tabs */
        .auth-tabs {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 28px;
            background: #fff;
        }

        .auth-tab {
            flex: 1;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: #6b7280;
            transition: background 0.18s, color 0.18s;
        }

        .auth-tab.active {
            background: #6d28d9;
            color: #fff;
        }

        .auth-tab:not(.active):hover { background: #f3f4f6; }

        /* Heading */
        .auth-heading {
            font-size: 1.4rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .auth-subheading {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        /* Alerts */
        .auth-msg-error {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            font-size: 13.5px;
            margin-bottom: 16px;
        }

        .auth-msg-success {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            color: #16a34a;
            font-size: 13.5px;
            margin-bottom: 16px;
        }

        /* Form fields */
        .lf-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .lf-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
        }

        .lf-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .lf-input {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .lf-input:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 3px rgba(109,40,217,0.12);
        }

        .lf-input::placeholder { color: #9ca3af; }

        .lf-eye {
            position: absolute;
            right: 12px;
            color: #9ca3af;
            font-size: 14px;
            padding: 4px;
            cursor: pointer;
            transition: color 0.15s, opacity 0.15s;
            background: none;
            border: none;
            opacity: 0;
            pointer-events: none;
        }

        .lf-eye.visible {
            opacity: 1;
            pointer-events: auto;
        }

        .lf-eye:hover { color: #374151; }

        /* Hide browser's native password reveal button (Edge/Chrome) */
        #loginPass::-ms-reveal,
        #loginPass::-ms-clear { display: none; }
        #loginPass::-webkit-credentials-auto-fill-button { display: none !important; }
        input[type="password"]::-webkit-textfield-decoration-container { display: none; }

        /* Remember + forgot row */
        .lf-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .lf-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        .lf-check-label input { accent-color: #6d28d9; width: 15px; height: 15px; }

        .lf-forgot {
            font-size: 13px;
            color: #6d28d9;
            text-decoration: none;
            font-weight: 500;
        }

        .lf-forgot:hover { text-decoration: underline; }

        /* Sign in button */
        .lf-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #6d28d9, #4f46e5);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            font-family: 'Inter', sans-serif;
            margin-bottom: 16px;
        }

        .lf-btn:hover  { opacity: 0.92; }
        .lf-btn:active { transform: scale(0.98); }

        /* "Don't have an account?" */
        .lf-switch {
            text-align: center;
            font-size: 13.5px;
            color: #6b7280;
        }

        .lf-switch a {
            color: #6d28d9;
            font-weight: 700;
            text-decoration: none;
        }

        .lf-switch a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 900px) {
            .auth-hero-panel { display: none; }
            .auth-right-panel { width: 100%; min-height: 100vh; padding: 32px 20px; }
        }
    </style>
</head>
<body style="background:#fff; font-family:'Inter',sans-serif;">

<div class="auth-split-page">

    <!-- ── LEFT: Purple hero ── -->
    <div class="auth-hero-panel">
        <div class="hero-circles-bg">
            <div class="hero-circle hero-circle-1"></div>
            <div class="hero-circle hero-circle-2"></div>
        </div>

        <div class="hero-logo-row">
            <div class="hero-logo-icon">
                <i class="fa fa-users"></i>
            </div>
            <span class="hero-logo-name">Nexo</span>
        </div>

        <div class="hero-body">
            <h1 class="hero-headline">Connect with your campus community</h1>
            <p class="hero-sub">Share moments, exchange ideas, and stay connected with your classmates at Saint Michael College of Caraga.</p>
            <div class="hero-pills">
                <div class="hero-pill">✏️ Post updates</div>
                <div class="hero-pill">💬 Comment & discuss</div>
                <div class="hero-pill">❤️ Like & react</div>
                <div class="hero-pill">🔵 Discover peers</div>
            </div>
        </div>

        <div class="hero-footer">
            Web Systems and Technologies — Final Project
        </div>
    </div>

    <!-- ── RIGHT: Form ── -->
    <div class="auth-right-panel">
        <div class="auth-right-inner">

            <!-- Tabs -->
            <div class="auth-tabs">
                <a href="index.php?url=login" class="auth-tab active">Sign In</a>
                <a href="index.php?url=register" class="auth-tab">Create Account</a>
            </div>

            <h2 class="auth-heading">Welcome back 👋</h2>
            <p class="auth-subheading">Sign in to continue to your Nexo feed</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="auth-msg-error">
                    <i class="fa fa-circle-exclamation"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="auth-msg-success">
                    <i class="fa fa-circle-check"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="index.php?url=login" method="POST">
                 <?= Security::field() ?>
                <div class="lf-group">
                    <label class="lf-label">Username or Email</label>
                    <div class="lf-input-wrap">
                        <input type="text" name="email" class="lf-input"
                               placeholder="juandelacruz or you@smcc.edu.ph" required autocomplete="username">
                    </div>
                </div>

                <div class="lf-group">
                    <label class="lf-label">Password</label>
                    <div class="lf-input-wrap">
                        <input type="password" name="password" id="loginPass" class="lf-input"
                               placeholder="••••••••" required autocomplete="current-password"
                               style="padding-right:42px;" oninput="handlePassInput(this)">
                        <button type="button" class="lf-eye" id="loginPassEye" onclick="togglePassL('loginPass', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="lf-row">
                    <label class="lf-check-label">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#" class="lf-forgot">Forgot password?</a>
                </div>

                <button type="submit" class="lf-btn">
                    <i class="fa fa-right-to-bracket"></i> Sign In
                </button>

                <p class="lf-switch">
                    Don't have an account? <a href="index.php?url=register">Create one</a>
                </p>

            </form>
        </div>
    </div>

</div>

<script>
function handlePassInput(input) {
    var eye = document.getElementById('loginPassEye');
    if (input.value.length > 0) {
        eye.classList.add('visible');
    } else {
        eye.classList.remove('visible');
        input.type = 'password';
        eye.querySelector('i').className = 'fa fa-eye-slash';
    }
}

function togglePassL(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}
</script>
</body>
</html>