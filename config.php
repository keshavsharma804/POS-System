<?php
session_start();


$DB_HOST = 'localhost';
$DB_NAME = 'pos_demo';
$DB_USER = 'root';
$DB_PASS = 'pos_demo99';

// SMTP Config
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_PORT = 587;
$SMTP_USER = '@gmail.com';
$SMTP_PASS = 'rzdskzlryfyhlxhi';
$SMTP_FROM = 'sharmakeshav364@gmail.com';
$SMTP_SECURE = 'tls';

// At the VERY TOP of config.php
require __DIR__ . '/vendor/autoload.php';


// Debug .env loading
if (!file_exists(__DIR__ . '/.env')) {
    die('.env file not found at: ' . __DIR__);
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->safeLoad();
} catch (Exception $e) {
    die('Dotenv error: ' . $e->getMessage());
}

// Debug output (temporary)
error_log('SMTP_USER: ' . ($_ENV['SMTP_USER'] ?? 'Not loaded'));

require_once 'vendor/autoload.php';

define('DEMO_MODE', true);

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pos_demo;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET time_zone = '+05:30'"); // IST timezone
    debugLog("Database connected successfully");
} catch (PDOException $e) {
    debugLog("Connection failed: " . $e->getMessage());
    http_response_code(500);
    die('Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Initialize tables
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        name VARCHAR(50) PRIMARY KEY,
        value VARCHAR(255) NOT NULL
    )");
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (name, value) VALUES ('low_stock_threshold', ?)");
    $stmt->execute([10]);
    debugLog("Settings table initialized");
} catch (PDOException $e) {
    debugLog("Error creating settings table: " . $e->getMessage());
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
        user_id INT PRIMARY KEY,
        token VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        INDEX idx_expires_at (expires_at)
    )");
    debugLog("Remember tokens table initialized");
} catch (PDOException $e) {
    debugLog("Error creating remember_tokens table: " . $e->getMessage());
}

// Clean up expired tokens
try {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    $stmt->execute();
    debugLog("Cleaned up expired remember tokens");
} catch (PDOException $e) {
    debugLog("Error cleaning remember tokens: " . $e->getMessage());
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS discounts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        type ENUM('percentage', 'fixed') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        product_id INT NULL,
        category_id INT NULL,
        min_purchase_amount DECIMAL(10,2) NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    debugLog("Discounts table initialized");
} catch (PDOException $e) {
    debugLog("Error creating discounts table: " . $e->getMessage());
}



function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}



function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


// function isRateLimited($email, $action='pwd_reset', $limit=3) {
//     $key = "limit_{$action}_{$email}";
//     $count = $_SESSION[$key] ?? 0;
    
//     if ($count >= $limit) {
//         error_log("Rate limit exceeded for $email");
//         return true;
//     }
    
//     $_SESSION[$key] = $count + 1;
//     return false;
// }


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOtpEmail($to, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings (from .env or defaults)
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? ''; // Fail early if not set
        $mail->Password   = $_ENV['SMTP_PASS'] ?? ''; // Fail early if not set
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_STARTTLS; // TLS by default
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587; // 587 for TLS, 465 for SSL

        // Validate critical .env settings
        if (empty($mail->Username)) {
            throw new Exception('SMTP_USER is not set in .env');
        }
        if (empty($mail->Password)) {
            throw new Exception('SMTP_PASS is not set in .env');
        }

        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM'] ?? $mail->Username, 'POS System'); // Fallback to SMTP_USER
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body    = "
            <h2>Password Reset Request</h2>
            <p>Your OTP code is: <strong>$otp</strong></p>
            <p>Valid for 15 minutes</p>
        ";
        $mail->AltBody = "Your OTP code is: $otp\nValid for 15 minutes";

        $mail->send();
        error_log("[SUCCESS] OTP sent to $to");
        return true;
    } catch (Exception $e) {
        error_log("[FAILED] OTP to $to | Error: " . $e->getMessage());
        return false;
    }
}


function isRateLimited($email, $action = 'pwd_reset', $limit = 3, $window = 3600) {
    global $pdo;
    try {
        $pdo->prepare("DELETE FROM rate_limits WHERE last_attempt < NOW() - INTERVAL ? SECOND")
            ->execute([$window]);
        $stmt = $pdo->prepare("SELECT attempt_count FROM rate_limits WHERE email = ? AND action = ?");
        $stmt->execute([$email, $action]);
        $record = $stmt->fetch();
        if ($record && $record['attempt_count'] >= $limit) {
            debugLog("Rate limit exceeded for $email ($action)", 'api_debug.log');
            return true;
        }
        if ($record) {
            $stmt = $pdo->prepare("UPDATE rate_limits SET attempt_count = attempt_count + 1, last_attempt = NOW() WHERE email = ? AND action = ?");
            $stmt->execute([$email, $action]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO rate_limits (email, action, attempt_count, last_attempt) VALUES (?, ?, 1, NOW())");
            $stmt->execute([$email, $action]);
        }
        return false;
    } catch (PDOException $e) {
        debugLog("Rate limit error: " . $e->getMessage(), 'api_debug.log');
        return true;
    }
}


function getLowStockThreshold() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'low_stock_threshold'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? (int)$result['value'] : 10;
    } catch (PDOException $e) {
        debugLog("Error fetching low stock threshold: " . $e->getMessage());
        return 10;
    }
}

function setLowStockThreshold($threshold) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES ('low_stock_threshold', ?) 
                               ON DUPLICATE KEY UPDATE value = ?");
        $result = $stmt->execute([$threshold, $threshold]);
        debugLog("Set low stock threshold: $threshold");
        return $result;
    } catch (PDOException $e) {
        debugLog("Error setting low stock threshold: " . $e->getMessage());
        return false;
    }
}

function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

function storeRememberToken($user_id, $token) {
    global $pdo;
    try {
        $expires_at = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
        $result = $stmt->execute([$user_id, $token, $expires_at, $token, $expires_at]);
        debugLog("Stored remember token for user_id: $user_id");
        return $result;
    } catch (PDOException $e) {
        debugLog("Error storing remember token for user_id $user_id: " . $e->getMessage());
        return false;
    }
}

function checkRememberCookie($role) {
    global $pdo;
    $cookie_name = $role === 'admin' ? 'remember_admin' : 'remember_client';
    if (!isset($_COOKIE[$cookie_name])) {
        return false;
    }
    $token = $_COOKIE[$cookie_name];
    try {
        $stmt = $pdo->prepare("SELECT rt.user_id, u.username, u.role 
                               FROM remember_tokens rt 
                               JOIN users u ON rt.user_id = u.id 
                               WHERE rt.token = ? AND rt.expires_at > NOW() AND u.role = ?");
        $stmt->execute([$token, $role]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            debugLog("Auto-login successful via cookie for user_id: {$user['user_id']}");
            return true;
        }
        debugLog("Invalid or expired remember token: $token");
        setcookie($cookie_name, '', time() - 3600, '/', '', false, true);
        return false;
    } catch (PDOException $e) {
        debugLog("Error checking remember token: " . $e->getMessage());
        return false;
    }
}

function debugLog($message, $file = 'api_debug.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/' . $file;
    $logMessage = "[$timestamp] $message\n";
    if (is_writable(dirname($logFile)) || !file_exists($logFile)) {
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

function getCache($key) {
    $cacheDir = __DIR__ . '/Cache/';
    $cacheFile = $cacheDir . md5($key) . '.cache';
    try {
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
            $data = file_get_contents($cacheFile);
            $unserialized = unserialize($data);
            if ($unserialized !== false) {
                debugLog("Cache hit for key: $key");
                return $unserialized;
            }
            debugLog("Cache deserialization failed for key: $key");
        }
    } catch (Exception $e) {
        debugLog("Cache read error for key $key: " . $e->getMessage());
    }
    return false;
}

function setCache($key, $data) {
    $cacheDir = __DIR__ . '/Cache/';
    $cacheFile = $cacheDir . md5($key) . '.cache';
    try {
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0755, true)) {
                debugLog("Failed to create cache directory: $cacheDir");
                return false;
            }
        }
        if (!is_writable($cacheDir)) {
            debugLog("Cache directory not writable: $cacheDir");
            return false;
        }
        if (!function_exists('serialize')) {
            debugLog("Error: serialize function not available for key: $key");
            return false;
        }
        $serialized = serialize($data);
        if (file_put_contents($cacheFile, $serialized) === false) {
            debugLog("Failed to write cache for key: $key");
            return false;
        }
        debugLog("Cache set for key: $key");
        return true;
    } catch (Exception $e) {
        debugLog("Cache write error for key $key: " . $e->getMessage());
        return false;
    }
}

function clearCache($key) {
    $cacheDir = __DIR__ . '/Cache/';
    $cacheFile = $cacheDir . md5($key) . '.cache';
    try {
        if (file_exists($cacheFile)) {
            if (unlink($cacheFile)) {
                debugLog("Cache cleared for key: $key");
            } else {
                debugLog("Failed to clear cache for key: $key");
            }
        }
    } catch (Exception $e) {
        debugLog("Cache clear error for key $key: " . $e->getMessage());
    }
}

function calculateDiscount($items, $total_amount) {
    global $pdo;
    $discount_amount = 0;
    $applied_discounts = [];
    
    try {
        $query = "SELECT id, name, type, value, product_id, category_id, min_purchase_amount 
                  FROM discounts 
                  WHERE is_active = TRUE 
                  AND start_date <= NOW() 
                  AND end_date >= NOW()";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($discounts as $discount) {
            $applicable = false;
            $discount_value = 0;

            // Check minimum purchase amount
            if ($discount['min_purchase_amount'] && $total_amount < $discount['min_purchase_amount']) {
                continue;
            }

            // Check if discount applies to specific product or category
            foreach ($items as $item) {
                $productStmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
                $productStmt->execute([$item['id']]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                if (($discount['product_id'] && $discount['product_id'] == $item['id']) ||
                    ($discount['category_id'] && $discount['category_id'] == $product['category_id']) ||
                    (!$discount['product_id'] && !$discount['category_id'])) {
                    $applicable = true;
                    $item_total = $item['quantity'] * $item['price'];
                    if ($discount['type'] === 'percentage') {
                        $discount_value += ($item_total * $discount['value']) / 100;
                    } else {
                        $discount_value += min($discount['value'], $item_total);
                    }
                }
            }

            if ($applicable) {
                $discount_amount += $discount_value;
                $applied_discounts[] = [
                    'id' => $discount['id'],
                    'name' => $discount['name'],
                    'amount' => $discount_value
                ];
            }
        }
    } catch (PDOException $e) {
        debugLog("Error calculating discounts: " . $e->getMessage());
    }

    return ['total_discount' => $discount_amount, 'applied_discounts' => $applied_discounts];
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sale_discounts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sale_id INT NOT NULL,
        discount_id INT NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales_header(id) ON DELETE CASCADE,
        FOREIGN KEY (discount_id) REFERENCES discounts(id) ON DELETE CASCADE
    )");
    debugLog("Sale discounts table initialized");
} catch (PDOException $e) {
    debugLog("Error creating sale_discounts table: " . $e->getMessage());
}

// Add OTP columns to users table if they don't exist
try {
    $pdo->exec("ALTER TABLE users 
                ADD COLUMN reset_token VARCHAR(255) NULL,
                ADD COLUMN reset_expires DATETIME NULL");
    debugLog("Added OTP columns to users table");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
        debugLog("Error adding OTP columns: " . $e->getMessage());
    }
}

// Ensure min_purchase_amount column exists
try {
    $pdo->exec("ALTER TABLE discounts ADD min_purchase_amount DECIMAL(10,2) NULL DEFAULT NULL");
    debugLog("Added min_purchase_amount column to discounts table");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
        debugLog("Error adding min_purchase_amount column: " . $e->getMessage());
    }
}

try {
    $pdo->exec("ALTER TABLE discounts ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE");
    debugLog("Added is_active column to discounts table");
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
        debugLog("Error adding is_active column: " . $e->getMessage());
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales_header (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL,
        user_id INT NOT NULL,
        customer_id INT NULL,
        order_status VARCHAR(50) NOT NULL DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    debugLog("sales_header table initialized");
} catch (PDOException $e) {
    debugLog("Error creating sales_header table: " . $e->getMessage());
}

// Create users table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(255) NULL,
        token_expires_at DATETIME NULL,
        role ENUM('admin','client') NOT NULL DEFAULT 'client'
    ) ENGINE=InnoDB");
    debugLog("users table initialized");
} catch (PDOException $e) {
    debugLog("Error creating users table: " . $e->getMessage());
}

// Create products table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        image VARCHAR(255) NULL,
        description TEXT NULL,
        barcode VARCHAR(50) NULL UNIQUE,
        category_id INT NULL
    ) ENGINE=InnoDB");
    debugLog("products table initialized");
} catch (PDOException $e) {
    debugLog("Error creating products table: " . $e->getMessage());
}

// Create stock_adjustments table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_adjustments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        reason VARCHAR(255) NOT NULL,
        adjusted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_adjusted_at (adjusted_at)
    ) ENGINE=InnoDB");
    debugLog("stock_adjustments table initialized");
} catch (PDOException $e) {
    debugLog("Error creating stock_adjustments table: " . $e->getMessage());
}

function clearCachePattern($pattern) {
    $cacheDir = __DIR__ . '/Cache/';
    try {
        $files = glob($cacheDir . '*.cache');
        foreach ($files as $file) {
            $key = str_replace('.cache', '', basename($file));
            if (fnmatch(md5($pattern), $key)) {
                if (unlink($file)) {
                    debugLog("Cache cleared for pattern: $pattern, file: $key");
                } else {
                    debugLog("Failed to clear cache for pattern: $pattern, file: $key");
                }
            }
        }
    } catch (Exception $e) {
        debugLog("Cache clear pattern error for $pattern: " . $e->getMessage());
    }
}



function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function regenerateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function generateCsrfToken() {
    return getCsrfToken();
}

// Loyalty Program Configuration
define('POINTS_PER_DOLLAR', 1); // 1 point per $1 spent
define('POINTS_REDEMPTION_RATE', 100); // 100 points = $1 discount

// Supported payment methods
define('SUPPORTED_PAYMENT_METHODS', ['Cash', 'Credit Card', 'Loyalty Points']);

?>