<?php
require 'config.php';

if (checkRememberCookie('admin')) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        debugLog("Error: Invalid CSRF token in admin login", 'auth_debug.log');
        $_SESSION['error'] = 'Invalid CSRF token';
        header('Location: admin_login.php');
        exit;
    }

    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';

    debugLog("Admin login attempt: username=$username", 'auth_debug.log');

    if (empty($username) || empty($password)) {
        debugLog("Error: Username or password empty", 'auth_debug.log');
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: admin_login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            regenerateCsrfToken();
            debugLog("Admin login successful: user_id={$user['id']}, username=$username", 'auth_debug.log');

            if ($remember) {
                $token = generateRememberToken();
                if (storeRememberToken($user['id'], $token)) {
                    setcookie('remember_admin', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    debugLog("Remember me token set for user_id={$user['id']}", 'auth_debug.log');
                } else {
                    debugLog("Failed to store remember token for user_id={$user['id']}", 'auth_debug.log');
                }
            }

            header('Location: admin.php');
            exit;
        } else {
            debugLog("Error: Invalid username or password for username=$username", 'auth_debug.log');
            $_SESSION['error'] = 'Invalid username or password';
            header('Location: admin_login.php');
            exit;
        }
    } catch (PDOException $e) {
        debugLog("Database error in admin_login: " . $e->getMessage(), 'auth_debug.log');
        $_SESSION['error'] = 'Database error, please try again later';
        header('Location: admin_login.php');
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
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="/POS/css/login.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form action="admin_login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3"><a href="client_login.php">Client Login</a> | <a href="login.php">POS Login</a></p>
    </div>
</body>
</html>