# Nexo – Setup Guide

## Requirements
- XAMPP / WAMP / Laragon (PHP 8.1+, MySQL, Apache)
- mod_rewrite enabled

## Steps

### 1. Place the project
Your GitHub Desktop repo folder is called **NEXO APP**.
Copy/clone it into your server root:
- XAMPP → `C:/xampp/htdocs/NEXO APP`
- WAMP  → `C:/wamp64/www/NEXO APP`

### 2. Import the database
1. Open phpMyAdmin → http://localhost/phpmyadmin
2. Create a new database named `nexo`
3. Import `sql/nexo_app.sql` **(first)**
4. Import `sql/navbar_features.sql` **(second)**
5. Import `sql/forgot_password.sql` **(third – adds password reset table)**

### 3. Configure database (if needed)
Open `config/database.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexo');
define('DB_PORT', '3307');   // CHANGE to 3306 if using default MySQL port
define('DB_USER', 'root');   // CHANGE if your MySQL user is different
define('DB_PASS', '');       // CHANGE if you have a MySQL password
```

### 4. Configure Gmail for Forgot Password emails
1. Go to https://myaccount.google.com/security → enable **2-Step Verification**.
2. Go to https://myaccount.google.com/apppasswords → create a new App Password.
3. Open `config/mail.php` and fill in:
```php
define('MAIL_ADDRESS',  'your_gmail@gmail.com');  // Your Gmail address
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');   // Your 16-char App Password
```

### 5. Make uploads folder writable
The folder `public/assets/uploads/` must exist and be writable.
On Linux/Mac: `chmod 775 public/assets/uploads/`

### 6. Set your Apache document root to `public/`
In XAMPP's httpd-vhosts.conf, add:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/NEXO APP/public"
    ServerName nexo.local
    <Directory "C:/xampp/htdocs/NEXO APP/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Then add `127.0.0.1 nexo.local` to your hosts file.

**OR** (simpler): just access via http://localhost/NEXO%20APP/public/

### 7. Demo accounts (password: `password`)
- marcos_reyes / marcos@nexo.app
- claire_santos / claire@nexo.app
- javier_dc     / javier@nexo.app

## Features
- **Register** – Create a new account (saves to database)
- **Login** – Sign in with your **email or username**
- **Forgot Password** – Enter your email, receive a Gmail reset link
- **Settings** – Click the avatar (top-right) → Settings & privacy
  - Opens a tabbed frame: Account · Preferences · Privacy · Danger Zone

## Folder Structure
```
NEXO APP/               ← GitHub Desktop repo folder
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── PostController.php
│   │   ├── ProfileController.php
│   │   ├── FriendController.php
│   │   ├── MessageController.php
│   │   ├── NotificationController.php
│   │   └── SettingsController.php
│   ├── models/
│   │   ├── UserModel.php
│   │   ├── PostModel.php
│   │   ├── CommentModel.php
│   │   └── LikeModel.php
│   └── views/
│       ├── auth/
│       │   ├── login.php
│       │   ├── register.php
│       │   ├── forgot_password.php   ← NEW
│       │   └── reset_password.php    ← NEW
│       ├── posts/
│       │   ├── feed.php
│       │   ├── search.php
│       │   └── saved.php
│       ├── profile/
│       │   └── profile.php
│       ├── friends/
│       │   └── index.php
│       ├── messages/
│       │   └── index.php
│       ├── settings/
│       │   └── index.php
│       └── partials/
│           ├── header.php
│           └── footer.php
├── config/
│   ├── database.php
│   └── mail.php          ← NEW (Gmail SMTP config)
├── lib/
│   └── Mailer.php        ← NEW (lightweight SMTP mailer)
├── public/
│   ├── index.php         ← Router
│   ├── .htaccess
│   └── assets/
│       ├── css/style.css
│       ├── js/app.js
│       ├── uploads/      ← User-uploaded images go here (must be writable)
│       └── images/
│           └── default.png
└── sql/
    ├── nexo_app.sql
    ├── navbar_features.sql
    └── forgot_password.sql  ← NEW
```