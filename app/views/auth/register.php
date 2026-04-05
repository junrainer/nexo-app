<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started – Nexo</title>
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
            <h2 class="auth-form-title">Get Started on Nexo</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fa fa-circle-exclamation"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="index.php?url=register" method="POST" class="auth-form">

                <!-- Name -->
                <div class="underline-field">
                    <label>Name</label>
                    <div class="two-col-inputs">
                        <input class="underline-input" type="text" name="first_name" placeholder="First Name" required>
                        <input class="underline-input" type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>

                <!-- Birthday -->
                <div class="underline-field">
                    <label>Birthday</label>
                    <div class="three-col-inputs">
                        <select class="underline-select" name="birth_month">
                            <option value="" disabled selected>Month</option>
                            <?php
                            $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                            foreach ($months as $m) echo "<option value='$m'>$m</option>";
                            ?>
                        </select>
                        <select class="underline-select" name="birth_day">
                            <option value="" disabled selected>Day</option>
                            <?php for ($d = 1; $d <= 31; $d++) echo "<option value='$d'>$d</option>"; ?>
                        </select>
                        <select class="underline-select" name="birth_year">
                            <option value="" disabled selected>Year</option>
                            <?php for ($y = 2026; $y >= 1960; $y--) echo "<option value='$y'>$y</option>"; ?>
                        </select>
                    </div>
                </div>

                <!-- Gender -->
                <div class="underline-field">
                    <label>Gender</label>
                    <select class="underline-select full-select" name="gender">
                        <option value="" disabled selected>Select your gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer-not">Prefer not to say</option>
                    </select>
                </div>

                <!-- Username or Email -->
                <div class="underline-field">
                    <label>Username or Email</label>
                    <input class="underline-input full-input" type="text" name="username" placeholder="Enter username or email" required>
                </div>

                <!-- Email (hidden, same as username for simplicity) -->
                <div class="underline-field">
                    <label>Email</label>
                    <input class="underline-input full-input" type="email" name="email" placeholder="Enter email address" required>
                </div>

                <!-- Mobile -->
                <div class="underline-field">
                    <label>Mobile Number</label>
                    <input class="underline-input full-input" type="tel" name="mobile" placeholder="Enter mobile number">
                </div>

                <!-- Password -->
                <div class="underline-field">
                    <label>Password</label>
                    <div class="underline-input-wrap">
                        <input type="password" name="password" id="regPass" placeholder="Enter password" required>
                        <button type="button" class="field-eye" onclick="togglePass('regPass', this)">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Submit</button>

                <p class="auth-switch">
                    Already have an account?
                    <a href="index.php?url=login">Sign in</a>
                </p>

            </form>
        </div>
    </div>

    <!-- Right: Branding -->
    <div class="auth-hero">
        <div class="auth-hero-content">
            <h1 class="auth-hero-title">WELCOME<br>BACK</h1>
            <p class="auth-hero-sub">
                Join Nexo and connect with your classmates and community today.
            </p>
        </div>
    </div>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>