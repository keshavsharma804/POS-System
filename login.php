<?php
require 'config.php';

if (checkRememberCookie('admin')) {
    header('Location: admin.php');
    exit;
}
if (checkRememberCookie('client')) {
    header('Location: client.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        debugLog("Error: Invalid CSRF token in login", 'auth_debug.log');
        $_SESSION['error'] = 'Invalid CSRF token';
        header('Location: login.php');
        exit;
    }

    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'] ?? '';
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING) ?? 'client';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';

    debugLog("$role login attempt: username=$username", 'auth_debug.log');

    if (empty($username) || empty($password) || !in_array($role, ['admin', 'client'])) {
        debugLog("Error: Invalid input for username=$username, role=$role", 'auth_debug.log');
        $_SESSION['error'] = 'Please fill in all fields correctly';
        header('Location: login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = ?");
        $stmt->execute([$username, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            regenerateCsrfToken();
            debugLog("$role login successful: user_id={$user['id']}, username=$username", 'auth_debug.log');

            if ($remember) {
                $token = generateRememberToken();
                if (storeRememberToken($user['id'], $token)) {
                    $cookie_name = $role === 'admin' ? 'remember_admin' : 'remember_client';
                    setcookie($cookie_name, $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    debugLog("Remember me token set for user_id={$user['id']}", 'auth_debug.log');
                } else {
                    debugLog("Failed to store remember token for user_id={$user['id']}", 'auth_debug.log');
                }
            }

            header('Location: ' . ($role === 'admin' ? 'admin.php' : 'client.php'));
            exit;
        } else {
            debugLog("Error: Invalid username or password for username=$username, role=$role", 'auth_debug.log');
            $_SESSION['error'] = 'Invalid username or password';
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        debugLog("Database error in login: " . $e->getMessage(), 'auth_debug.log');
        $_SESSION['error'] = 'Database error, please try again later';
        header('Location: login.php');
        exit;
    }
}
$csrf_token = getCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="/css/login.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>POS Login</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <form id="login-form" action="login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="role" class="form-label">Role:</label>
            <select id="role" name="role" class="form-select" required>
                <option value="client">Client</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-label">Remember Me</label>
            </div>
        </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>