<?php
session_start();

// 1. Clear session data
$_SESSION = array();

// 2. Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

/**
 * 4. REDIRECT
 * Since this file is in the ROOT, we just point to the 
 * other files in the same ROOT folder.
 */
header("Location: login.php?msg=You+have+been+successfully+logged+out");
exit();
?>