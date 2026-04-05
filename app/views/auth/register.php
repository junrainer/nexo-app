<!DOCTYPE html>
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
    <style>
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

        .hero-circles-bg { position: absolute; inset: 0; pointer-events: none; }
        .hero-circle { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.07); }
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
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .hero-logo-icon i { color: #fff; font-size: 18px; }
        .hero-logo-name { font-size: 1.25rem; font-weight: 700; color: #fff; }

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

        .hero-pills { display: flex; flex-wrap: wrap; gap: 10px; }

        .hero-pill {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 99px;
            padding: 7px 14px;
            font-size: 13px; color: #fff; font-weight: 500;
        }

        .hero-footer { font-size: 12px; color: rgba(255,255,255,0.5); position: relative; z-index: 2; }

        /* ── Right panel ── */
        .auth-right-panel {
            width: 520px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 40px 32px;
            background: #f9fafb;
            overflow-y: auto;
        }

        .auth-right-inner { width: 100%; max-width: 420px; }

        /* Tabs */
        .auth-tabs {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
            background: #fff;
        }

        .auth-tab {
            flex: 1; padding: 12px;
            font-size: 14px; font-weight: 600;
            text-align: center; cursor: pointer;
            text-decoration: none; color: #6b7280;
            transition: background 0.18s, color 0.18s;
        }

        .auth-tab.active { background: #6d28d9; color: #fff; }
        .auth-tab:not(.active):hover { background: #f3f4f6; }

        /* Heading */
        .auth-heading { font-size: 1.3rem; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .auth-subheading { font-size: 13px; color: #6b7280; margin-bottom: 20px; }

        /* Alerts */
        .auth-msg-error {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; background: #fef2f2;
            border: 1px solid #fecaca; border-radius: 8px;
            color: #dc2626; font-size: 13.5px; margin-bottom: 16px;
        }

        /* Photo upload circle */
        .reg-photo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .reg-photo-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: #ede9fe;
            border: 2px solid #ddd6fe;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
            cursor: pointer;
            transition: border-color 0.2s;
            margin-bottom: 8px;
        }

        .reg-photo-circle:hover { border-color: #6d28d9; }

        .reg-photo-circle > i { font-size: 28px; color: #6d28d9; }

        .reg-photo-circle img {
            position: absolute; inset: 0;
            width: 100%; height: 100%; object-fit: cover;
        }

        .reg-photo-badge {
            position: absolute;
            bottom: 4px; right: 4px;
            width: 22px; height: 22px;
            background: #6d28d9; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        .reg-photo-badge i { font-size: 10px; color: #fff; }
        .reg-photo-hint { font-size: 12px; color: #9ca3af; }

        /* Form fields */
        .rf-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }

        .rf-label { font-size: 13.5px; font-weight: 600; color: #374151; }

        .rf-input-wrap { position: relative; display: flex; align-items: center; }

        .rf-input {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .rf-input:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 3px rgba(109,40,217,0.12);
        }

        .rf-input::placeholder { color: #9ca3af; }

        .rf-hint { font-size: 11.5px; color: #9ca3af; margin-top: 2px; }

        .rf-eye {
            position: absolute; right: 12px;
            color: #9ca3af; font-size: 14px;
            padding: 4px; cursor: pointer;
            background: none; border: none;
            transition: color 0.15s, opacity 0.15s;
            opacity: 0;
            pointer-events: none;
        }

        .rf-eye.visible {
            opacity: 1;
            pointer-events: auto;
        }

        .rf-eye:hover { color: #374151; }

        /* Hide browser's native password reveal button (Edge/Chrome) */
        #regPass::-ms-reveal,
        #regPass::-ms-clear { display: none; }
        #regPass2::-ms-reveal,
        #regPass2::-ms-clear { display: none; }
        input[type="password"]::-webkit-textfield-decoration-container { display: none; }

        /* Prefix icon for username */
        .rf-prefix {
            position: absolute; left: 12px;
            color: #9ca3af; font-size: 14px;
            pointer-events: none;
        }

        .rf-input-padded { padding-left: 32px; }

        /* Textarea */
        .rf-textarea {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: #111827;
            outline: none;
            resize: vertical;
            min-height: 80px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .rf-textarea:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 3px rgba(109,40,217,0.12);
        }

        .rf-textarea::placeholder { color: #9ca3af; }

        /* Terms */
        .reg-terms {
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .reg-terms a { color: #6d28d9; text-decoration: none; }
        .reg-terms a:hover { text-decoration: underline; }

        /* Create Account button */
        .rf-btn {
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
            margin-bottom: 14px;
        }

        .rf-btn:hover  { opacity: 0.92; }
        .rf-btn:active { transform: scale(0.98); }

        .rf-switch {
            text-align: center;
            font-size: 13.5px;
            color: #6b7280;
        }

        .rf-switch a { color: #6d28d9; font-weight: 700; text-decoration: none; }
        .rf-switch a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 900px) {
            .auth-hero-panel { display: none; }
            .auth-right-panel { width: 100%; min-height: 100vh; }
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
            <div class="hero-logo-icon"><i class="fa fa-users"></i></div>
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
                <a href="index.php?url=login" class="auth-tab">Sign In</a>
                <a href="index.php?url=register" class="auth-tab active">Create Account</a>
            </div>

            <h2 class="auth-heading">Join Nexo 🚀</h2>
            <p class="auth-subheading">Create your account and start connecting</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="auth-msg-error">
                    <i class="fa fa-circle-exclamation"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="index.php?url=register" method="POST" enctype="multipart/form-data">
                <?= Security::field() ?>
                <!-- Profile photo upload -->
                <div class="reg-photo-wrap">
                    <label for="profileImageInput" style="cursor:pointer;">
                        <div class="reg-photo-circle" id="regPhotoCircle">
                            <i class="fa fa-camera" id="regPhotoIcon"></i>
                            <img id="regPhotoPreview" src="" alt="" style="display:none;">
                            <div class="reg-photo-badge"><i class="fa fa-pen"></i></div>
                        </div>
                    </label>
                    <input type="file" name="profile_image" id="profileImageInput"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           style="display:none;" onchange="previewRegPhoto(this)">
                    <p class="reg-photo-hint">Click to upload profile photo (optional)</p>
                </div>

                <!-- Full Name -->
                <div class="rf-group">
                    <label class="rf-label">Full Name</label>
                    <div class="rf-input-wrap">
                        <input type="text" name="full_name" class="rf-input"
                               placeholder="Juan dela Cruz" required>
                    </div>
                </div>

                <!-- Username -->
                <div class="rf-group">
                    <label class="rf-label">Username</label>
                    <div class="rf-input-wrap">
                        <span class="rf-prefix">@</span>
                        <input type="text" name="username" class="rf-input rf-input-padded"
                               placeholder="juandelacruz" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="rf-group">
                    <label class="rf-label">Email address</label>
                    <div class="rf-input-wrap">
                        <input type="email" name="email" class="rf-input"
                               placeholder="you@smcc.edu.ph" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="rf-group">
                    <label class="rf-label">Password</label>
                    <p class="rf-hint">At least 8 characters with uppercase, number, and special character</p>
                    <div class="rf-input-wrap" style="margin-top:4px;">
                        <input type="password" name="password" id="regPass" class="rf-input"
                               placeholder="••••••••" required style="padding-right:42px;"
                               oninput="handlePassInput(this, 'regPassEye')">
                        <button type="button" class="rf-eye" id="regPassEye" onclick="togglePassR('regPass', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="rf-group">
                    <label class="rf-label">Confirm Password</label>
                    <div class="rf-input-wrap">
                        <input type="password" name="confirm_password" id="regPass2" class="rf-input"
                               placeholder="••••••••" required style="padding-right:42px;"
                               oninput="handlePassInput(this, 'regPass2Eye')">
                        <button type="button" class="rf-eye" id="regPass2Eye" onclick="togglePassR('regPass2', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Bio -->
                <div class="rf-group">
                    <label class="rf-label">Bio <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                    <textarea name="bio" class="rf-textarea"
                              placeholder="Tell your classmates a little about yourself..."></textarea>
                </div>

                <!-- Terms -->
                <p class="reg-terms">
                    By creating an account you agree to our
                    <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </p>

                <button type="submit" class="rf-btn">
                    <i class="fa fa-user-plus"></i> Create Account
                </button>

                <p class="rf-switch">
                    Already have an account? <a href="index.php?url=login">Sign in</a>
                </p>

            </form>
        </div>
    </div>

</div>

<script>
function handlePassInput(input, eyeId) {
    var eye = document.getElementById(eyeId);
    if (input.value.length > 0) {
        eye.classList.add('visible');
    } else {
        eye.classList.remove('visible');
        input.type = 'password';
        eye.querySelector('i').className = 'fa fa-eye-slash';
    }
}

function togglePassR(id, btn) {
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

function previewRegPhoto(input) {
    var preview = document.getElementById('regPhotoPreview');
    var icon    = document.getElementById('regPhotoIcon');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>