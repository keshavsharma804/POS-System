<?php
require_once 'config.php';

function checkPermission($permission) {
    global $auth;
    
    // If user is admin, bypass permission checks
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        return true;
    }
    
    // Check if permission exists in session
    if (isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions'])) {
        return true;
    }
    
    // Fallback to database check
    if (isset($_SESSION['user_id']) && $auth->checkPermission($permission)) {
        return true;
    }
    
    return false;
}

// function requirePermission($permission) {
//     if (!checkPermission($permission)) {
//         header('Location: unauthorized.php');
//         exit;
//     }
// }

// function isAdmin() {
//     return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
// }