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
3. Import `sql/nexo_app.sql`

### 3. Configure database (if needed)
Open `config/database.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexo');
define('DB_PORT', '3307');   // CHANGE to 3306 if using default MySQL port
define('DB_USER', 'root');   // CHANGE if your MySQL user is different
define('DB_PASS', '');       // CHANGE if you have a MySQL password
```

### 4. Make uploads folder writable
The folder `public/assets/uploads/` must exist and be writable.
On Linux/Mac: `chmod 775 public/assets/uploads/`

### 5. Set your Apache document root to `public/`
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

### 6. Demo accounts (password: `password`)
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
│   │   └── ProfileController.php
│   ├── models/
│   │   ├── UserModel.php
│   │   ├── PostModel.php
│   │   ├── CommentModel.php
│   │   └── LikeModel.php
│   └── views/
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── posts/
│       │   ├── feed.php
│       │   └── search.php
│       ├── profile/
│       │   ├── profile.php
│       │   └── edit.php
│       └── partials/
│           ├── header.php
│           └── footer.php
├── config/
│   └── database.php
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
    └── nexo_app.sql
```