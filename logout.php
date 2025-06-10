<?php
require 'config.php';

// Store user_id for logging
$user_id = $_SESSION['user_id'] ?? 0;

// Clear session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Clear remember me cookies
if (isset($_COOKIE['remember_client'])) {
    setcookie('remember_client', '', time() - 3600, '/', '', false, true);
    debugLog("Cleared remember_client cookie", 'auth_debug.log');
}
if (isset($_COOKIE['remember_admin'])) {
    setcookie('remember_admin', '', time() - 3600, '/', '', false, true);
    debugLog("Cleared remember_admin cookie", 'auth_debug.log');
}

// Clear remember tokens
try {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->execute([$user_id]);
    debugLog("Cleared remember tokens for user_id: " . ($user_id ?: 'unknown'), 'auth_debug.log');
} catch (PDOException $e) {
    debugLog("Error clearing remember tokens: " . $e->getMessage(), 'auth_debug.log');
}

// Log logout
debugLog("User logged out, user_id: " . ($user_id ?: 'unknown'), 'auth_debug.log');

header('Location: client_login.php');
exit;
?>