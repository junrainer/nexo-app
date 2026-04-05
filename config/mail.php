<?php
/**
 * Nexo – Email Configuration
 *
 * ── For Gmail (XAMPP / VPS) ──────────────────────────────────
 *  1. Go to https://myaccount.google.com/security → enable "2-Step Verification".
 *  2. Go to https://myaccount.google.com/apppasswords → create an App Password.
 *  3. Set MAIL_ADDRESS and MAIL_PASSWORD below.
 *
 * ── For InfinityFree (free hosting) ─────────────────────────
 *  InfinityFree blocks outbound SMTP (ports 25, 465, 587).
 *  The app automatically falls back to PHP's mail() function.
 *  mail() on InfinityFree uses their own relay — delivery depends
 *  on their infrastructure.  Set MAIL_ADDRESS to your Gmail so
 *  replies come back to you; leave MAIL_PASSWORD as-is (it's not
 *  used by mail()).
 *
 * ── APP_BASE_URL ─────────────────────────────────────────────
 *  Set this to your site root (no trailing slash) so password-reset
 *  emails contain the correct link.
 *  Examples:
 *    http://localhost/nexo-app/public
 *    http://yourdomain.infinityfreeapp.com
 */

define('MAIL_ADDRESS',   'your_gmail@gmail.com');  // ← Your email address
define('MAIL_PASSWORD',  'xxxx xxxx xxxx xxxx');   // ← Gmail App Password (SMTP only)
define('MAIL_FROM_NAME', 'Nexo');

// Base URL for password-reset links. Leave empty to auto-detect.
define('APP_BASE_URL', '');   // ← e.g. 'http://yourdomain.infinityfreeapp.com'
