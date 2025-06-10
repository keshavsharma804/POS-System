<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email using PHPMailer
function sendEmail($to, $subject, $body) {
    global $emailConfig;
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $emailConfig['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $emailConfig['smtp_username'];
        $mail->Password = $emailConfig['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $emailConfig['smtp_port'];

        // Recipients
        $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        debugLog("Email sent to $to with subject: $subject", 'email.log');
        return true;
    } catch (Exception $e) {
        debugLog("Failed to send email to $to: " . $mail->ErrorInfo, 'email.log');
        return false;
    }
}

// Low Stock Notification for Admins
function sendLowStockNotification() {
    global $pdo;
    $threshold = getLowStockThreshold();
    $stmt = $pdo->prepare("SELECT p.name, p.quantity, c.name AS category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.quantity <= ?");
    $stmt->execute([$threshold]);
    $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lowStockProducts)) {
        debugLog("No low stock products found.", 'email.log');
        return;
    }

    // Fetch admin emails
    $stmt = $pdo->prepare("SELECT email FROM users WHERE role = 'admin' AND email IS NOT NULL");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        debugLog("No admin emails found for low stock notification.", 'email.log');
        return;
    }

    // Build email content
    $body = "<h2>Low Stock Alert</h2>";
    $body .= "<p>The following products are below the low stock threshold of $threshold:</p>";
    $body .= "<table border='1' cellpadding='5'><tr><th>Product Name</th><th>Category</th><th>Quantity</th></tr>";
    foreach ($lowStockProducts as $product) {
        $body .= "<tr><td>" . htmlspecialchars($product['name']) . "</td><td>" . htmlspecialchars($product['category_name'] ?? 'N/A') . "</td><td>" . $product['quantity'] . "</td></tr>";
    }
    $body .= "</table>";
    $body .= "<p>Please restock these items as soon as possible.</p>";

    // Send email to each admin
    foreach ($admins as $admin) {
        sendEmail($admin['email'], "Low Stock Alert - POS System", $body);
    }
}

// Daily Sales Summary for Clients
function sendDailySalesSummary() {
    global $pdo;
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $start = $yesterday . ' 00:00:00';
    $end = $yesterday . ' 23:59:59';

    // Fetch all clients with email
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role = 'client' AND email IS NOT NULL");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($clients as $client) {
        // Fetch sales for this client from yesterday
        $query = "SELECT sh.id AS sale_id, p.name AS product_name, si.price, si.quantity, 
                         (si.price * si.quantity) AS total, si.sale_date
                  FROM sales_header sh
                  JOIN sale_items si ON si.sale_id = sh.id
                  JOIN products p ON si.product_id = p.id
                  WHERE sh.user_id = ? AND sh.sale_date BETWEEN ? AND ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$client['id'], $start, $end]);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sales)) {
            debugLog("No sales found for client {$client['username']} on $yesterday.", 'email.log');
            continue;
        }

        // Calculate total spent
        $totalSpent = array_sum(array_column($sales, 'total'));

        // Build email content
        $body = "<h2>Daily Sales Summary for {$client['username']}</h2>";
        $body .= "<p>Date: $yesterday</p>";
        $body .= "<p>Here is a summary of your purchases from yesterday:</p>";
        $body .= "<table border='1' cellpadding='5'><tr><th>Sale ID</th><th>Product Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Sale Date</th></tr>";
        foreach ($sales as $sale) {
            $body .= "<tr><td>" . $sale['sale_id'] . "</td><td>" . htmlspecialchars($sale['product_name']) . "</td><td>$" . number_format($sale['price'], 2) . "</td><td>" . $sale['quantity'] . "</td><td>$" . number_format($sale['total'], 2) . "</td><td>" . $sale['sale_date'] . "</td></tr>";
        }
        $body .= "</table>";
        $body .= "<p><strong>Total Spent: $" . number_format($totalSpent, 2) . "</strong></p>";
        $body .= "<p>Thank you for shopping with us!</p>";

        // Send email
        sendEmail($client['email'], "Daily Sales Summary - $yesterday", $body);
    }
}

// Main execution
debugLog("Starting notification script at " . date('Y-m-d H:i:s'), 'email.log');
sendLowStockNotification();
sendDailySalesSummary();
debugLog("Finished notification script at " . date('Y-m-d H:i:s'), 'email.log');
?>