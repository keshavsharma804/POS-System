<?php
require 'config.php';

$username = 'admin1';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, remember_token = NULL, token_expires_at = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        echo "Password reset for admin1. Login with admin1/admin123 at http://localhost/POS/admin_login.php\n";
        debugLog("Password reset for admin1, user_id={$user['id']}", 'auth_debug.log');
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hash]);
        echo "Created admin1 with password admin123. Login at http://localhost/POS/admin_login.php\n";
        debugLog("Created admin1, user_id=" . $pdo->lastInsertId(), 'auth_debug.log');
    }
} catch (PDOException $e) {
    debugLog("Error resetting admin password: " . $e->getMessage(), 'auth_debug.log');
    echo "Error: " . $e->getMessage() . "\n";
}
?>