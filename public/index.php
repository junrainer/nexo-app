<?php
session_start();

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
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

// ── Get current route ─────────────────────────────────────────
$url    = trim($_GET['url'] ?? 'login', '/');
$method = $_SERVER['REQUEST_METHOD'];

// ── Auth guard ────────────────────────────────────────────────
$guestRoutes = ['login', 'register', 'forgot-password', 'reset-password'];
$isGuest     = !isset($_SESSION['user_id']);

if ($isGuest && !in_array($url, $guestRoutes)) {
    header('Location: index.php?url=login');
    exit;
}

if (!$isGuest && in_array($url, $guestRoutes)) {
    header('Location: index.php?url=feed');
    exit;
}

// ── CSRF validation for all POST requests ─────────────────────
// AJAX routes return JSON; regular routes redirect on failure.
$ajaxRoutes = [
    'post/like', 'post/save', 'post/unsave',
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

    // Search
    case $url === 'search' && $method === 'GET': $posts->search(); break;

    // Messages
    case $url === 'messages'      && $method === 'GET':  $messages->index();          break;
    case $url === 'message/send'  && $method === 'POST': $messages->send();           break;
    case $url === 'message/new'   && $method === 'GET':  $messages->getNew();         break;
    case $url === 'message/unread'&& $method === 'GET':  $messages->getUnreadCount(); break;
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
    case $url === 'notifications'       && $method === 'GET':  $notifs->getAll();        break;
    case $url === 'notifications/count' && $method === 'GET':  $notifs->getUnreadCount();break;
    case $url === 'notification/read'   && $method === 'POST': $notifs->markAsRead();    break;
    case $url === 'notifications/read'  && $method === 'POST': $notifs->markAllAsRead(); break;

    // Settings
    case $url === 'settings'             && $method === 'GET':  $settings->index();            break;
    case $url === 'settings/account'     && $method === 'POST': $settings->updateAccount();    break;
    case $url === 'settings/preferences' && $method === 'POST': $settings->updatePreferences();break;
    case $url === 'settings/darkmode'    && $method === 'POST': $settings->toggleDarkMode();   break;

    // Profile
    case $url === 'profile/update' && $method === 'POST': $profile->update(); break;

    // Dynamic profile: profile/{username}
    case (bool) preg_match('#^profile/([a-zA-Z0-9_]+)$#', $url, $m):
        $profile->show($m[1]);
        break;

    // Default — redirect to feed or login
    default:
        header('Location: index.php?url=' . ($isGuest ? 'login' : 'feed'));
        exit;
}