<?php
session_start();
// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/Security.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PostController.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';
require_once __DIR__ . '/../app/controllers/MessageController.php';
require_once __DIR__ . '/../app/controllers/FriendController.php';
require_once __DIR__ . '/../app/controllers/NotificationController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';

// ── Session hardening ─────────────────────────────────────────
Security::hardenSession();

// ── PHP-level security headers ────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Helper: time ago ──────────────────────────────────────────
// Approximate seconds in a year (365.25 days to account for leap years).
const SECONDS_IN_YEAR = 31557600;

function resolve_timestamp(string $datetime): ?int {
    $value = trim($datetime);
    if ($value === '') {
        return null;
    }
    if (is_numeric($value)) {
        return (int) $value;
    }

    $now = time();
    $timestamps = [];
    $zones = [
        new DateTimeZone(date_default_timezone_get()),
        new DateTimeZone('UTC'),
    ];

    foreach ($zones as $zone) {
        try {
            $dt = new DateTimeImmutable($value, $zone);
            $timestamps[] = $dt->getTimestamp();
        } catch (Exception $e) {
            continue;
        }
    }

    if (empty($timestamps)) {
        return null;
    }

    $bestTs = null;
    $bestDiff = null;
    // Prefer a non-future timestamp closest to now; if all are future, pick the closest one.
    foreach ($timestamps as $ts) {
        $diff = $now - $ts;
        if ($diff >= 0 && ($bestDiff === null || $diff < $bestDiff)) {
            $bestDiff = $diff;
            $bestTs = $ts;
        }
    }

    if ($bestTs === null) {
        $bestDiff = null;
        foreach ($timestamps as $ts) {
            $diff = abs($now - $ts);
            if ($bestDiff === null || $diff < $bestDiff) {
                $bestDiff = $diff;
                $bestTs = $ts;
            }
        }
    }

    return $bestTs;
}
function time_ago(string $datetime): string {
    $timestamp = resolve_timestamp($datetime);
    if ($timestamp === null) {
        return 'just now';
    }
    $diff = time() - $timestamp;
    if ($diff <= 0 || $diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $minutes = (int) floor($diff / 60);
        return $minutes . ' min' . ($minutes === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hr' . ($hours === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }
    $showYear = $diff >= SECONDS_IN_YEAR;
    return date($showYear ? 'M j, Y' : 'M j', $timestamp);
}

function time_iso(string $datetime): string {
    $timestamp = resolve_timestamp($datetime);
    if ($timestamp === null) {
        return '';
    }
    return date(DATE_ATOM, $timestamp);
}

// ── Helper: post visibility icon + label ─────────────────────
function post_visibility_meta(string $visibility): array {
    return match ($visibility) {
        'friends' => ['fa-user-group', 'Friends'],
        'only_me' => ['fa-lock',       'Only me'],
        default   => ['fa-globe',      'Public'],
    };
}

// ── Get current route ─────────────────────────────────────────
$url    = trim($_GET['url'] ?? 'login', '/');
$method = $_SERVER['REQUEST_METHOD'];

// ── Auth guard ────────────────────────────────────────────────
$guestRoutes = ['login', 'register', 'forgot-password', 'verify-reset', 'reset-password'];
$isGuest     = !isset($_SESSION['user_id']);

if ($isGuest && !in_array($url, $guestRoutes)) {
    header('Location: index.php?url=login');
    exit;
}

if (!$isGuest && in_array($url, $guestRoutes)) {
    header('Location: index.php?url=feed');
    exit;
}

// ── Track last active time for online status ───────────
if (!$isGuest) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE users SET last_active_at = NOW() WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // last_active_at column may not exist yet
    }
}

// ── CSRF validation for all POST requests ─────────────────────
// AJAX routes return JSON; regular routes redirect on failure.
$ajaxRoutes = [
    'post/like', 'post/save', 'post/unsave',
    'comment/like',
    'message/send', 'message/new', 'message/unread',
    'friend/request', 'friend/accept', 'friend/decline', 'friend/unfriend', 'friend/status',
    'notifications', 'notifications/count', 'notification/read', 'notifications/read',
    'settings/darkmode',
];

if ($method === 'POST' && !Security::validateToken()) {
    if (in_array($url, $ajaxRoutes)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh the page.']);
        exit;
    }
    $_SESSION['error'] = 'Security check failed. Please try again.';
    $back = $isGuest ? 'login' : 'feed';
    header('Location: index.php?url=' . $back);
    exit;
}

// ── Instantiate controllers ───────────────────────────────────
$auth     = new AuthController();
$posts    = new PostController();
$profile  = new ProfileController();
$messages = new MessageController();
$friends  = new FriendController();
$notifs   = new NotificationController();
$settings = new SettingsController();

// ── Route ─────────────────────────────────────────────────────
switch (true) {

    // Auth
    case $url === 'login'          && $method === 'GET':  $auth->showLogin();          break;
    case $url === 'login'          && $method === 'POST': $auth->login();              break;
    case $url === 'register'       && $method === 'GET':  $auth->showRegister();       break;
    case $url === 'register'       && $method === 'POST': $auth->register();           break;
    case $url === 'logout':                                $auth->logout();             break;
    case $url === 'forgot-password'&& $method === 'GET':  $auth->showForgotPassword(); break;
    case $url === 'forgot-password'&& $method === 'POST': $auth->forgotPassword();     break;
    case $url === 'verify-reset'   && $method === 'GET':  $auth->showVerifyReset();     break;
    case $url === 'verify-reset'   && $method === 'POST': $auth->verifyReset();         break;
    case $url === 'reset-password' && $method === 'GET':  $auth->showResetPassword();  break;
    case $url === 'reset-password' && $method === 'POST': $auth->resetPassword();      break;

    // Feed
    case $url === 'feed' && $method === 'GET': $posts->feed(); break;

    // Posts
    case $url === 'post/create'  && $method === 'POST': $posts->create();  break;
    case $url === 'post/update'  && $method === 'POST': $posts->update();  break;
    case $url === 'post/delete'  && $method === 'POST': $posts->delete();  break;
    case $url === 'post/like'    && $method === 'POST': $posts->like();    break;
    case $url === 'post/save'    && $method === 'POST': $posts->save();    break;
    case $url === 'post/unsave'  && $method === 'POST': $posts->unsave();  break;

    // Saved posts
    case $url === 'saved' && $method === 'GET': $posts->saved(); break;

    // Comments
    case $url === 'comment/add'    && $method === 'POST': $posts->addComment();    break;
    case $url === 'comment/update' && $method === 'POST': $posts->updateComment(); break;
    case $url === 'comment/delete' && $method === 'POST': $posts->deleteComment(); break;
    case $url === 'comment/like'   && $method === 'POST': $posts->commentLike();   break;

    // Search
    case $url === 'search' && $method === 'GET': $posts->search(); break;

    // Messages
    case $url === 'messages'         && $method === 'GET':  $messages->index();          break;
    case $url === 'message/send'     && $method === 'POST': $messages->send();           break;
    case $url === 'message/load'     && $method === 'GET':  $messages->load();           break;
    case $url === 'message/new'      && $method === 'GET':  $messages->getNew();         break;
    case $url === 'message/unread'   && $method === 'GET':  $messages->getUnreadCount(); break;
    case $url === 'message/recent'   && $method === 'GET':  $messages->getRecent();      break;
    case (bool) preg_match('#^message/start$#', $url) && isset($_GET['user']):
        $messages->startConversation($_GET['user']);
        break;

    // Friends
    case $url === 'friends'        && $method === 'GET':  $friends->index();         break;
    case $url === 'friend/request' && $method === 'POST': $friends->sendRequest();   break;
    case $url === 'friend/accept'  && $method === 'POST': $friends->acceptRequest(); break;
    case $url === 'friend/decline' && $method === 'POST': $friends->declineRequest();break;
    case $url === 'friend/unfriend'&& $method === 'POST': $friends->unfriend();      break;
    case $url === 'friend/status'  && $method === 'GET':  $friends->getStatus();     break;

    // Notifications
    case $url === 'notifications/all'   && $method === 'GET':  $notifs->getPage();        break;
    case $url === 'notifications'       && $method === 'GET':  $notifs->getAll();        break;
    case $url === 'notifications/count' && $method === 'GET':  $notifs->getUnreadCount();break;
    case $url === 'notification/read'   && $method === 'POST': $notifs->markAsRead();    break;
    case $url === 'notifications/read'  && $method === 'POST': $notifs->markAllAsRead(); break;
    case $url === 'notifications/bulk'  && $method === 'POST': $notifs->bulkUpdate();    break;

    // Settings
    case $url === 'settings'             && $method === 'GET':  $settings->index();            break;
    case $url === 'settings/account'     && $method === 'POST': $settings->updateAccount();    break;
    case $url === 'settings/preferences' && $method === 'POST': $settings->updatePreferences();break;
    case $url === 'settings/darkmode'    && $method === 'POST': $settings->toggleDarkMode();   break;

    // Profile
    case $url === 'profile/update' && $method === 'POST': $profile->update(); break;
    case $url === 'profile/update-cover' && $method === 'POST': $profile->updateCover(); break;

    // Dynamic profile: profile/{username}
    case (bool) preg_match('#^profile/([a-zA-Z0-9_]+)$#', $url, $m):
        $profile->show($m[1]);
        break;

    // Default — redirect to feed or login
    default:
        header('Location: index.php?url=' . ($isGuest ? 'login' : 'feed'));
        exit;
}
