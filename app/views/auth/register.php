<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Nexo</title>
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

    <div class="auth-card auth-card-wide">

        <!-- Logo -->
        <div class="auth-logo">
            <img src="assets/images/logo.svg" alt="Nexo" class="auth-logo-img"
                 onerror="this.style.display='none'; document.getElementById('auth-logo-fb-r').style.display='flex'">
            <div id="auth-logo-fb-r" class="auth-logo-icon" style="display:none">N</div>
            <div class="auth-logo-name">Nexo</div>
            <p class="auth-logo-tag">Create your account</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="auth-alert auth-alert-error">
                <i class="fa fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="index.php?url=register" method="POST" enctype="multipart/form-data">
            <?= $csrfField ?>

            <!-- Profile Picture -->
            <div class="auth-field reg-avatar-field">
                <label class="auth-label">Profile Picture <span class="auth-optional">(optional)</span></label>
                <div class="reg-avatar-wrap">
                    <div class="reg-avatar-preview" id="regAvatarPreview">
                        <i class="fa fa-user reg-avatar-icon" id="regAvatarIcon"></i>
                        <img id="regAvatarImg" src="" alt="Preview" style="display:none">
                    </div>
                    <label class="reg-avatar-label" for="profileImageInput">
                        <i class="fa fa-camera"></i> Choose photo
                    </label>
                    <input type="file" name="profile_image" id="profileImageInput"
                           accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">
                    <span class="reg-avatar-hint">Max 2 MB · JPEG, PNG, GIF, WebP</span>
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
                    <input type="text" name="username" class="auth-input auth-input-icon-left"
                           placeholder="Choose a username" required>
                </div>
            </div>

            <!-- Email -->
            <div class="auth-field">
                <label class="auth-label">Email Address</label>
                <div class="auth-input-wrap">
                    <i class="fa fa-envelope auth-input-icon"></i>
                    <input type="email" name="email" class="auth-input auth-input-icon-left"
                           placeholder="Enter your email" required>
                </div>
            </div>

            <!-- Mobile -->
            <div class="auth-field">
                <label class="auth-label">Mobile Number <span class="auth-optional">(optional)</span></label>
                <div class="auth-input-wrap">
                    <i class="fa fa-phone auth-input-icon"></i>
                    <input type="tel" name="mobile" class="auth-input auth-input-icon-left"
                           placeholder="Enter mobile number">
                </div>
            </div>

            <!-- Birthday -->
            <div class="auth-field">
                <label class="auth-label">Birthday</label>
                <div class="auth-row-three">
                    <select class="auth-input auth-select" name="birth_month">
                        <option value="" disabled selected>Month</option>
                        <?php
                        $months = ['January','February','March','April','May','June',
                                   'July','August','September','October','November','December'];
                        foreach ($months as $m) echo "<option value='$m'>$m</option>";
                        ?>
                    </select>
                    <select class="auth-input auth-select" name="birth_day">
                        <option value="" disabled selected>Day</option>
                        <?php for ($d = 1; $d <= 31; $d++) echo "<option value='$d'>$d</option>"; ?>
                    </select>
                    <select class="auth-input auth-select" name="birth_year">
                        <option value="" disabled selected>Year</option>
                        <?php for ($y = 2026; $y >= 1960; $y--) echo "<option value='$y'>$y</option>"; ?>
                    </select>
                </div>
            </div>

            <!-- Gender -->
            <div class="auth-field">
                <label class="auth-label">Gender</label>
                <div class="auth-input-wrap">
                    <select class="auth-input auth-select" name="gender">
                        <option value="" disabled selected>Select your gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer-not">Prefer not to say</option>
                    </select>
                </div>
            </div>

            <!-- Password -->
            <div class="auth-field">
                <label class="auth-label">Password</label>
                <div class="auth-input-wrap">
                    <i class="fa fa-lock auth-input-icon"></i>
                    <input type="password" name="password" id="regPass"
                           class="auth-input auth-input-icon-left auth-input-icon-right"
                           placeholder="Create a password (min. 6 chars)" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePass('regPass', this)">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

    </div>

    <div class="auth-footer-card">
        Already have an account? <a href="index.php?url=login">Sign in</a>
    </div>

</div>

<script src="assets/js/app.js"></script>
<script>
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
            hint.style.color = '#e53e3e';
            input.value = '';
            return;
        }
        if (file.size > maxSize) {
            hint.textContent = 'File too large. Maximum size is 2 MB.';
            hint.style.color = '#e53e3e';
            input.value = '';
            return;
        }
        hint.textContent = 'Max 2 MB \u00B7 JPEG, PNG, GIF, WebP';
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
</script>
</body>
</html>