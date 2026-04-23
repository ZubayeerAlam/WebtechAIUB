<?php
require_once 'config.php';
session_start();

// Destroy all session data
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// NOTE: We intentionally keep the remember-email and last-login cookies
// so they persist for the next visit. Uncomment below to also clear them:
// setcookie(COOKIE_EMAIL, '', time() - 3600, '/');
// setcookie(COOKIE_LAST_LOGIN, '', time() - 3600, '/');

header("Location: login.php");
exit;
?>
