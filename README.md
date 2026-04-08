# Nexo – Setup Guide

## Requirements
- **Local**: XAMPP / WAMP / Laragon (PHP 8.1+, MySQL, Apache) with mod_rewrite enabled
- **Hosted**: InfinityFree (free) or any cPanel host with PHP 8.1+ and MySQL

## Upload limits (photos)
To attach up to 5 photos in a single post (5 MB each), ensure PHP limits allow it:
- `upload_max_filesize` ≥ **5M**
- `post_max_size` ≥ **35M**

If you use Apache with `mod_php`, these are set in `public/.htaccess`. For PHP-FPM/NGINX, update your `php.ini`.

---

## Option A – Local Development (XAMPP)

### 1. Place the project
Copy/clone the repo into your server root:
- XAMPP → `C:/xampp/htdocs/nexo-app`
- WAMP  → `C:/wamp64/www/nexo-app`

### 2. Import the database (XAMPP)
1. Open phpMyAdmin → http://localhost/phpmyadmin
2. Create a new database named `nexo`
3. Import in order: `sql/nexo_app.sql` → `sql/navbar_features.sql` → `sql/forgot_password.sql` → `sql/add_post_media.sql` → `sql/add_user_profile_fields.sql` → `sql/add_comment_likes.sql`

### 3. Configure database (XAMPP)
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexo');
define('DB_PORT', '3306');   // 3307 if you changed XAMPP's MySQL port
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Configure email
Open `config/mail.php` and set your Gmail address + App Password (see file for instructions).

### 5. Run
Access via: `http://localhost/nexo-app/public/`

---

## Option B – InfinityFree Hosting

### 1. Sign up
Go to https://infinityfree.com and create a free account + website.

### 2. Upload files
- Use the **File Manager** or **FTP** (FileZilla).
- Upload the **entire project** to `htdocs/` (the web root).
- The folder structure in `htdocs/` should be:
  ```
  htdocs/
    app/
    config/
    lib/
    public/       ← THIS becomes the web root (see step 5)
    sql/
  ```

### 3. Import the database
1. In your InfinityFree cPanel → **MySQL Databases** → create a database.
2. Note your **DB host**, **DB name**, **username**, and **password**.
3. Open **phpMyAdmin** from cPanel.
4. Import in order: `sql/nexo_app.sql` → `sql/navbar_features.sql` → `sql/forgot_password.sql` → `sql/add_post_media.sql` → `sql/add_user_profile_fields.sql` → `sql/add_comment_likes.sql`

### 4. Configure database
Edit `config/database.php` with your InfinityFree credentials:
```php
define('DB_HOST', 'sql200.infinityfree.com'); // ← From cPanel (e.g. sql200.infinityfree.com)
define('DB_NAME', 'epiz_12345678_nexo');       // ← Your DB name
define('DB_PORT', '3306');                     // ← Always 3306 on InfinityFree
define('DB_USER', 'epiz_12345678');            // ← Your DB username
define('DB_PASS', 'yourpassword');             // ← Your DB password
```

### 5. Set document root to `public/`
In InfinityFree cPanel → **Subdomains** or **Addon Domains** → point the document root to `htdocs/public`.

Or alternatively, add an `.htaccess` in `htdocs/` that redirects to `public/`:
```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

### 6. Fix RewriteBase
If the app is in a subdirectory, edit `public/.htaccess` and update:
```apache
RewriteBase /        # if at domain root
# OR
RewriteBase /nexo-app/public/   # if in a subdirectory
```

### 7. Set APP_BASE_URL
Edit `config/mail.php`:
```php
define('APP_BASE_URL', 'http://yourdomain.infinityfreeapp.com');
```

### 8. Email on InfinityFree
InfinityFree blocks outbound SMTP (ports 465 and 587). The app automatically
falls back to PHP's `mail()` function, which uses InfinityFree's own relay.
Set `MAIL_ADDRESS` in `config/mail.php` to your email so reset emails have a proper from address.

---

## Security features
- **CSRF protection** on all forms and AJAX calls
- **Login rate limiting** (5 attempts per 15 minutes)
- **Session hardening** (regeneration, UA binding)
- **Security headers** (X-Frame-Options, X-Content-Type-Options, etc.) via `.htaccess`
- **Directory listing disabled**
- **PHP execution blocked** in uploads folder

## Features
- **Register** / **Login** (email or username)
- **Forgot Password** → Gmail SMTP (XAMPP) or PHP mail() (InfinityFree)
- **Responsive** – works on mobile, tablet, and desktop
- **Settings** – click the avatar (top-right) → Settings & privacy
  - Opens a tabbed frame: Account · Preferences · Privacy · Danger Zone
- Left sidebar hidden on mobile → slide-in hamburger menu
- Mobile bottom navigation bar

## Demo accounts (password: `password`)
- marcos_reyes / marcos@nexo.app
- claire_santos / claire@nexo.app
- javier_dc     / javier@nexo.app

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
