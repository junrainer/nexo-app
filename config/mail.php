<?php
/**
 * Nexo – Gmail SMTP Configuration
 *
 * How to set this up:
 *  1. Go to https://myaccount.google.com/security and enable "2-Step Verification".
 *  2. Go to https://myaccount.google.com/apppasswords
 *  3. Create a new App Password (App: Mail, Device: Other – type "Nexo").
 *  4. Copy the 16-character password and paste it below as MAIL_PASSWORD.
 *  5. Set MAIL_ADDRESS to your Gmail address.
 *  6. Set APP_BASE_URL to your site's root (no trailing slash), e.g.:
 *       http://localhost/nexo-app/public
 *       http://nexo.local
 */

define('MAIL_ADDRESS',  'your_gmail@gmail.com');  // ← Change this
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');   // ← Your 16-char App Password
define('MAIL_FROM_NAME', 'Nexo');

// Base URL used in password-reset emails. Leave empty to auto-detect.
define('APP_BASE_URL',  '');                       // ← e.g. 'http://localhost/nexo-app/public'
