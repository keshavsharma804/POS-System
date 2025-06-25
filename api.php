<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'config.php';
require 'vendor/autoload.php'; // TCPDF
$DEMO_MODE = true; 
ob_clean();





$sort = $_GET['sort'] ?? '';
$orderBy = 'name ASC'; // Default sort
if ($sort === 'price_asc') $orderBy = 'price ASC';
elseif ($sort === 'price_desc') $orderBy = 'price DESC';
elseif ($sort === 'quantity_asc') $orderBy = 'quantity ASC';
elseif ($sort === 'quantity_desc') $orderBy = 'quantity DESC';
$query = "SELECT * FROM products WHERE ... ORDER BY $orderBy LIMIT :offset, :page_size";


function sendJsonResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}







// Process image upload
function processImageUpload($file, $product_id) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'File upload error: ' . $file['error']];
    }
    if (!in_array($file['type'], $allowedTypes)) {
        return ['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, GIF allowed'];
    }
    if ($file['size'] > $maxSize) {
        return ['status' => 'error', 'message' => 'File size exceeds 2MB'];
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . $product_id . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['status' => 'error', 'message' => 'Failed to save file'];
    }
    $relativePath = 'uploads/' . $filename;
    return ['status' => 'success', 'filename' => $relativePath];
}



// Determine the action based on request method and content type
$action = '';
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $action = $jsonData['action'] ?? '';
            $data = $jsonData;
        }
    } elseif (stripos($contentType, 'multipart/form-data') !== false) {
        $action = $_POST['action'] ?? '';
        $data = $_POST;
    }
}

if (empty($action)) {
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
    $data = $_POST;
}

debugLog("Action requested: $action", 'api_debug.log');

if (in_array($action, ['add_product', 'delete_product', 'edit_product', 'update_product', 'add_discount', 'delete_discount', 'update_discount', 'get_sales_summary', 'export_products_csv', 'import_products_csv', 'export_sales_csv', 'get_low_stock_products', 'set_low_stock_threshold', 'get_sales_report', 'export_sales_report_csv', 'generate_invoice', 'checkout', 'get_sales', 'search_clients', 'get_detailed_sales_report', 'generate_sales_report_pdf', 'generate_receipt', 'get_sales_forecast', 'get_customer_detailed_history', 'generate_customer_history_pdf', 'update_customer', 'delete_customer', 'export_stock_adjustments_csv', 'get_product_catalog', 'add_to_cart', 'get_cart_contents', 'update_cart_item', 'remove_from_cart', 'upload_product_image'])) {
    if (!isAdmin() && !isClient()) {
        debugLog("Unauthorized access for action: $action", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add_product', 'edit_product', 'add_discount', 'delete_discount', 'delete_product', 'update_discount', 'update_product', 'import_products_csv', 'set_low_stock_threshold', 'generate_invoice', 'checkout', 'search_products', 'generate_sales_report_pdf', 'generate_receipt', 'get_most_sold_products', 'export_most_sold_products_csv', 'get_dashboard_summary', 'get_daily_sales_trend', 'get_customer_detailed_history', 'generate_customer_history_pdf', 'update_customer', 'delete_customer', 'add_to_cart', 'update_cart_item', 'remove_from_cart', 'upload_product_image'])) {
    $csrf_token = $data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!validateCsrfToken($csrf_token)) {
        debugLog("Invalid CSRF token for action: $action, provided: $csrf_token", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
        exit;
    }
    debugLog("CSRF token validated for action: $action, token: $csrf_token", 'api_debug.log');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['login', 'register'])) {
    $csrf_token = $data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!validateCsrfToken($csrf_token)) {
        debugLog("Invalid CSRF token for action: $action, provided: $csrf_token", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid CSRF token']);
    }
    debugLog("CSRF token validated for action: $action, token: $csrf_token", 'api_debug.log');
}


switch ($action) {
    case 'get_new_csrf_token':
        $new_token = regenerateCsrfToken();
        debugLog("New CSRF token generated: $new_token", 'api_debug.log');
        echo json_encode(['csrf_token' => $new_token]);
        break;

    case 'search_customers':
    if (!isAdmin()) {
        debugLog("Unauthorized access to search_customers by non-admin", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    $search_term = trim($_GET['username'] ?? '');
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, loyalty_points FROM customers WHERE username LIKE ? OR email LIKE ?");
        $stmt->execute(["%$search_term%", "%$search_term%"]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("search_customers: search_term='$search_term', found=" . count($customers), 'api_debug.log');
        echo json_encode(['success' => true, 'customers' => $customers]);
    } catch (PDOException $e) {
        debugLog("search_customers error: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    break;

   case 'search_products':
    if (!isAdmin()) {
        sendJsonResponse(403, ['success' => false, 'message' => 'Unauthorized access']);
    }
    try {
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        // If neither term nor id is provided, return error
        if (empty($term) && !$id) {
            sendJsonResponse(400, ['success' => false, 'message' => 'Search term or product ID is required']);
        }

        $query = "SELECT id, name, barcode, price, quantity, image FROM products WHERE 1=1";
        $params = [];

        if ($id) {
            $query .= " AND id = ?";
            $params[] = $id;
        } elseif ($term) {
            $query .= " AND (name LIKE ? OR barcode LIKE ?)";
            $params[] = "%$term%";
            $params[] = "%$term%";
        }

        $query .= " LIMIT 10";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(200, $products);
    } catch (Exception $e) {
        debugLog("Error in search_products: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Error searching products']);
    }
    break;

    

case 'checkout':
    $raw_input = file_get_contents('php://input');
    debugLog("Raw checkout input: " . $raw_input, 'api_debug.log');

    $json_data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debugLog("JSON decode error: " . json_last_error_msg(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $customer_id = isset($json_data['customer_id']) ? (int)$json_data['customer_id'] : 0;
    $items = isset($json_data['items']) && is_array($json_data['items']) ? $json_data['items'] : [];
    if (empty($items) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $items[] = [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'] // Include price from session cart
            ];
        }
    }

    $csrf_token = $json_data['csrf_token'] ?? '';
    $points_earned = isset($json_data['points_earned']) ? (int)$json_data['points_earned'] : 0;
    $points_redeemed = isset($json_data['points_redeemed']) ? (int)$json_data['points_redeemed'] : 0;

    debugLog("Checkout: customer_id=$customer_id, items=" . json_encode($items) . ", points_earned=$points_earned, points_redeemed=$points_redeemed", 'api_debug.log');
    debugLog("Items type: " . gettype($items) . ", count: " . count($items), 'api_debug.log');
    debugLog("Items structure: " . print_r($items, true), 'api_debug.log');
    debugLog("Full \$_SESSION: " . print_r($_SESSION, true), 'api_debug.log');

    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
        debugLog("Invalid user_id in session", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Invalid user session']);
        exit;
    }

    if (!validateCsrfToken($csrf_token)) {
        debugLog("Invalid CSRF token: $csrf_token", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    if ($customer_id <= 0) {
        debugLog("Invalid customer_id: $customer_id", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }

    if (empty($items) || !is_array($items)) {
        debugLog("No items in checkout payload", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'No items in cart']);
        exit;
    }

    foreach ($items as $index => &$item) {
        if (!isset($item['id'], $item['quantity'], $item['price']) ||
            !is_numeric($item['id']) || (int)$item['id'] <= 0 ||
            !is_numeric($item['quantity']) || (int)$item['quantity'] <= 0 ||
            !is_numeric($item['price']) || (float)$item['price'] < 0) {
            debugLog("Invalid item at index $index: " . json_encode($item), 'api_debug.log');
            echo json_encode(['success' => false, 'message' => 'Invalid items in cart']);
            exit;
        }
        $item['id'] = (int)$item['id'];
        $item['quantity'] = (int)$item['quantity'];
        $item['price'] = (float)$item['price'];
    }
    unset($item);

    try {
        $pdo->beginTransaction();

        // Verify customer
        $stmt = $pdo->prepare("SELECT id, loyalty_points, username FROM customers WHERE id = ?");
        if (!$stmt->execute([$customer_id])) {
            debugLog("Customer query failed: " . json_encode($stmt->errorInfo()), 'api_debug.log');
            throw new Exception("Failed to verify customer");
        }
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            debugLog("Customer ID $customer_id not found", 'api_debug.log');
            throw new Exception("Customer not found");
        }

        // Validate loyalty points
        if ($points_redeemed > 0 && $points_redeemed > $customer['loyalty_points']) {
            debugLog("Insufficient loyalty points: requested=$points_redeemed, available={$customer['loyalty_points']}", 'api_debug.log');
            throw new Exception("Insufficient loyalty points");
        }

        // Validate product inventory
        $total_amount = 0;
        $item_details = [];
        foreach ($items as $item) {
            $stmt = $pdo->prepare("SELECT id, name, price, quantity FROM products WHERE id = ? FOR UPDATE");
            if (!$stmt->execute([$item['id']])) {
                debugLog("Product query failed for ID {$item['id']}: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to fetch product ID {$item['id']}");
            }
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                debugLog("Product ID {$item['id']} not found", 'api_debug.log');
                throw new Exception("Product ID {$item['id']} not found");
            }
            if ($product['quantity'] < $item['quantity']) {
                debugLog("Insufficient stock for product ID {$item['id']}: available={$product['quantity']}, requested={$item['quantity']}", 'api_debug.log');
                throw new Exception("Insufficient stock for {$product['name']}");
            }
            if (abs($product['price'] - $item['price']) > 0.01) {
                debugLog("Price mismatch for product ID {$item['id']}: session={$item['price']}, db={$product['price']}", 'api_debug.log');
                throw new Exception("Price mismatch for {$product['name']}");
            }
            $item_total = $item['quantity'] * $product['price'];
            $total_amount += $item_total;
            $item_details[] = [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $product['price'],
                'name' => $product['name']
            ];
        }

        // Calculate tax and discounts
        $tax_rate = 0.05;
        $tax_amount = $total_amount * $tax_rate;
        $discount_amount = 0;
        $applied_discounts = [];
        if (function_exists('calculateDiscount')) {
            $discount_result = calculateDiscount($item_details, $total_amount);
            $discount_amount = $discount_result['total_discount'] ?? 0;
            $applied_discounts = $discount_result['applied_discounts'] ?? [];
        }
        $loyalty_discount_amount = $points_redeemed / 100;
        $final_amount = max(0, ($total_amount + $tax_amount) - ($discount_amount + $loyalty_discount_amount));

        $user_id = (int)$_SESSION['user_id'];

        // Insert sale header
        $stmt = $pdo->prepare("
            INSERT INTO sales_header (
                sale_date, total_amount, payment_methods, user_id, customer_id,
                order_status, subtotal, discount, tax, discount_amount,
                final_amount, points_earned, loyalty_discount_amount, tax_amount
            ) VALUES (
                NOW(), ?, NULL, ?, ?, 'completed', ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $params = [
            $total_amount,
            $user_id,
            $customer_id,
            $total_amount, // subtotal
            $discount_amount, // discount
            $tax_amount, // tax
            $discount_amount, // discount_amount
            $final_amount,
            $points_earned,
            $loyalty_discount_amount,
            $tax_amount
        ];

        // Debug parameters
        debugLog("Sales header params: " . json_encode($params), 'api_debug.log');
        debugLog("SQL Query: " . $stmt->queryString, 'api_debug.log');

        if (!$stmt->execute($params)) {
            debugLog("Failed to execute sales_header query: " . json_encode($stmt->errorInfo()), 'api_debug.log');
            throw new Exception("Failed to insert sale header");
        }

        $sale_id = $pdo->lastInsertId();
        debugLog("Inserted sale header: sale_id=$sale_id", 'api_debug.log');

        // Insert sale items
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (
                sale_id, product_id, quantity, price, sale_date, subtotal,
                discount, tax, discount_amount
            ) VALUES (?, ?, ?, ?, NOW(), ?, 0, 0, 0)
        ");
        foreach ($item_details as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            $params = [
                $sale_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $subtotal
            ];
            debugLog("Sale item params: " . json_encode($params), 'api_debug.log');
            if (!$stmt->execute($params)) {
                debugLog("Failed to insert sale item for product ID {$item['id']}: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to insert sale item");
            }
            debugLog("Inserted sale item: sale_id=$sale_id, product_id={$item['id']}", 'api_debug.log');

            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            if (!$stmt->execute([$item['quantity'], $item['id']])) {
                debugLog("Failed to update stock for product ID {$item['id']}: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to update product stock");
            }
            debugLog("Updated stock for product ID {$item['id']}", 'api_debug.log');
        }

        // Insert discounts
        if (!empty($applied_discounts)) {
            $stmt = $pdo->prepare("
                INSERT INTO sale_discounts (sale_id, discount_id, discount_amount, product_id, discount, tax)
                VALUES (?, ?, ?, ?, 0, 0)
            ");
            foreach ($applied_discounts as $discount) {
                if (!isset($discount['id'], $discount['amount'], $discount['product_id'])) {
                    debugLog("Skipping invalid discount: " . json_encode($discount), 'api_debug.log');
                    continue;
                }
                if (!$stmt->execute([$sale_id, $discount['id'], $discount['amount'], $discount['product_id']])) {
                    debugLog("Failed to insert sale discount: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                    throw new Exception("Failed to insert sale discount");
                }
            }
        }

        // Update loyalty points
        if ($points_redeemed > 0) {
            $stmt = $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ?");
            if (!$stmt->execute([$points_redeemed, $customer_id])) {
                debugLog("Failed to update loyalty points: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to update loyalty points");
            }
            $stmt = $pdo->prepare("
                INSERT INTO loyalty_transactions (customer_id, points, action, sale_id, created_at)
                VALUES (?, ?, 'redeem', ?, NOW())
            ");
            if (!$stmt->execute([$customer_id, $points_redeemed, $sale_id])) {
                debugLog("Failed to record loyalty transaction: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to record loyalty transaction");
            }
        }
        if ($points_earned > 0) {
            $stmt = $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?");
            if (!$stmt->execute([$points_earned, $customer_id])) {
                debugLog("Failed to add loyalty points: " . json_encode($stmt->errorInfo()), 'api_debug.log');
                throw new Exception("Failed to add loyalty points");
            }
        }

        // Generate invoice
        $stmt = $pdo->prepare("
            SELECT sh.id, sh.total_amount, sh.sale_date, sh.user_id, sh.customer_id,
                   COALESCE(c.username, 'Unknown') AS client_name
            FROM sales_header sh
            LEFT JOIN customers c ON sh.customer_id = c.id
            WHERE sh.id = ?
        ");
        if (!$stmt->execute([$sale_id])) {
            debugLog("Failed to fetch sale for invoice: " . json_encode($stmt->errorInfo()), 'api_debug.log');
            throw new Exception("Failed to fetch sale for invoice");
        }
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sale) {
            debugLog("Sale ID $sale_id not found", 'api_debug.log');
            throw new Exception("Sale not found");
        }

        $stmt = $pdo->prepare("
            SELECT si.quantity, si.price, p.name AS product_name
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = ?
        ");
        if (!$stmt->execute([$sale_id])) {
            debugLog("Failed to fetch sale items for invoice: " . json_encode($stmt->errorInfo()), 'api_debug.log');
            throw new Exception("Failed to fetch sale items for invoice");
        }
        $invoice_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($sale_id, 6, '0', STR_PAD_LEFT);
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('POS System');
        $pdf->SetTitle('Invoice ' . $invoice_number);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Invoice', 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(0, 10, 'Invoice Number: ' . $invoice_number, 0, 1);
        $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d H:i:s', strtotime($sale['sale_date'])), 0, 1);
        $pdf->Cell(0, 10, 'Client: ' . $sale['client_name'], 0, 1);
        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(80, 10, 'Product', 1);
        $pdf->Cell(30, 10, 'Quantity', 1);
        $pdf->Cell(30, 10, 'Price', 1);
        $pdf->Cell(40, 10, 'Total', 1);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 12);
        foreach ($invoice_items as $item) {
            $pdf->Cell(80, 10, $item['product_name'], 1);
            $pdf->Cell(30, 10, $item['quantity'], 1);
            $pdf->Cell(30, 10, '$' . number_format($item['price'], 2), 1);
            $pdf->Cell(40, 10, '$' . number_format($item['quantity'] * $item['price'], 2), 1);
            $pdf->Ln();
        }
        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(140, 10, 'Total Amount:', 0);
        $pdf->Cell(40, 10, '$' . number_format($sale['total_amount'], 2), 0);
        $pdf->Ln();

        $invoice_dir = __DIR__ . '/Invoices/';
        if (!is_dir($invoice_dir)) {
            mkdir($invoice_dir, 0755, true);
        }
        $file_path = $invoice_dir . $invoice_number . '.pdf';
        $pdf->Output($file_path, 'F');

        $stmt = $pdo->prepare("
            INSERT INTO invoices (sale_id, invoice_number, file_path, generated_at, generated_by)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        if (!$stmt->execute([$sale_id, $invoice_number, $file_path, $user_id])) {
            debugLog("Failed to insert invoice: " . json_encode($stmt->errorInfo()), 'api_debug.log');
            throw new Exception("Failed to insert invoice");
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        debugLog("Checkout successful: sale_id=$sale_id, total_amount=$total_amount", 'api_debug.log');

        clearCache('get_products_*');
        clearCache('get_available_products_*');
        clearCache('get_low_stock_products_*');
        clearCache('get_sales_*');

        echo json_encode([
            'success' => true,
            'sale_id' => $sale_id,
            'invoice_url' => 'Invoices/' . basename($file_path)
        ]);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        debugLog("Checkout error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
    break;

    case 'get_dashboard_summary':
    try {
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        // Total Sales
        $query = "SELECT COUNT(*) as total_sales, SUM(total_amount) as total_revenue
                  FROM sales_header
                  WHERE sale_date BETWEEN ? AND ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Total Products Sold
        $query = "SELECT SUM(si.quantity) as total_products_sold
                  FROM sale_items si
                  JOIN sales_header sh ON si.sale_id = sh.id
                  WHERE sh.sale_date BETWEEN ? AND ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        $products_sold = $stmt->fetch(PDO::FETCH_ASSOC);

        $summary = [
            'total_sales' => $sales_data['total_sales'] ?? 0,
            'total_revenue' => $sales_data['total_revenue'] ?? 0,
            'total_products_sold' => $products_sold['total_products_sold'] ?? 0
        ];
        debugLog("Fetched dashboard summary: " . json_encode($summary), 'api_debug.log');
        echo json_encode($summary);
    } catch (Exception $e) {
        debugLog("Error in get_dashboard_summary: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    break; 

    case 'get_most_sold_products':
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // Validate dates
        if (!strtotime($start_date) || !strtotime($end_date)) {
            throw new Exception('Invalid date format.');
        }

        $query = "SELECT p.id, p.name, p.price, p.barcode, c.name as category_name,
                         SUM(si.quantity) as total_quantity_sold,
                         SUM(si.quantity * si.price) as total_revenue
                  FROM sale_items si
                  JOIN products p ON si.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  JOIN sales_header sh ON si.sale_id = sh.id
                  WHERE DATE(sh.sale_date) BETWEEN ? AND ?
                  GROUP BY p.id, p.name, p.price, p.barcode, c.name
                  ORDER BY total_quantity_sold DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($result);
        debugLog("Fetched most sold products for date range: $start_date to $end_date", 'api_log.log');
    } catch (Exception $e) {
        debugLog("Error fetching most sold products: " . $e->getMessage(), 'api_log.log');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'export_most_sold_products_csv':
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-90 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        // Validate dates
        if (!$start_date || !$end_date) {
            throw new Exception('Start date and end date are required.');
        }

        // Fetch most sold products (similar to get_most_sold_products)
        $query = "SELECT p.id, p.name, p.price, p.barcode, c.name as category_name,
                         SUM(si.quantity) as total_quantity_sold,
                         SUM(si.quantity * si.price) as total_revenue
                  FROM sale_items si
                  JOIN sales_header sh ON si.sale_id = sh.id
                  JOIN products p ON si.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE DATE(sh.sale_date) BETWEEN ? AND ?
                  GROUP BY p.id, p.name, p.price, p.barcode, c.name
                  ORDER BY total_quantity_sold DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        $most_sold_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate CSV
        $filename = "most_sold_products_{$start_date}_to_{$end_date}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Product ID', 'Product Name', 'Price', 'Barcode', 'Category', 'Total Quantity Sold', 'Total Revenue']);

        if ($most_sold_products) {
            foreach ($most_sold_products as $product) {
                fputcsv($output, [
                    $product['id'],
                    $product['name'],
                    number_format($product['price'], 2),
                    $product['barcode'],
                    $product['category_name'] ?? 'N/A',
                    $product['total_quantity_sold'],
                    number_format($product['total_revenue'], 2)
                ]);
            }
        }

        fclose($output);
        debugLog("Exported most sold products CSV: start_date=$start_date, end_date=$end_date, rows=" . count($most_sold_products), 'api_debug.log');
        exit;
    } catch (Exception $e) {
        debugLog("Error in export_most_sold_products_csv: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        http_response_code(500);
        exit;
    }
    break;

    case 'get_inventory':
    try {
        $search = $_GET['search'] ?? '';
        $category_id = $_GET['category_id'] ?? '';
        $query = "SELECT p.id, p.name, p.company, p.model, p.price, p.quantity, p.barcode, p.image, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (p.name LIKE :search OR p.company LIKE :search OR p.model LIKE :search OR c.name LIKE :search)";
            $params['search'] = "%$search%";
        }
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $params['category_id'] = $category_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched " . count($products) . " products for inventory", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'products' => $products]);
    } catch (PDOException $e) {
        debugLog("Database error in get_inventory: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

      

 case 'add_product':
    try {
        if (!isAdmin()) {
            throw new Exception('Unauthorized access');
        }
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $name = trim($_POST['name'] ?? '');
        $company = trim($_POST['company'] ?? '') ?: null;
        $model = trim($_POST['model'] ?? '') ?: null;
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $barcode = trim($_POST['barcode'] ?? '') ?: null;
        $description = trim($_POST['description'] ?? '') ?: null;
        $image_path = null;

        if (empty($name) || $category_id <= 0 || $price <= 0 || $quantity < 0) {
            throw new Exception('Required fields missing or invalid');
        }

        // Verify category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Invalid category ID');
        }

        // Process image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = processImageUpload($_FILES['image'], time());
            if ($result['status'] === 'error') {
                throw new Exception($result['message']);
            }
            $image_path = $result['filename'];
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, company, model, category_id, price, quantity, barcode, image, description)
                               VALUES (:name, :company, :model, :category_id, :price, :quantity, :barcode, :image, :description)");
        $stmt->execute([
            'name' => $name,
            'company' => $company,
            'model' => $model,
            'category_id' => $category_id,
            'price' => $price,
            'quantity' => $quantity,
            'barcode' => $barcode,
            'image' => $image_path,
            'description' => $description
        ]);
        $product_id = $pdo->lastInsertId();
        debugLog("Added product: $name (ID: $product_id)", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'Product added successfully', 'image_path' => $image_path]);
    } catch (PDOException $e) {
        debugLog("Database error in add_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in add_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => $e->getMessage()]);
    }
    break;

 case 'update_product':
    try {
        if (!isAdmin()) {
            throw new Exception('Unauthorized access');
        }
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $company = trim($_POST['company'] ?? '') ?: null;
        $model = trim($_POST['model'] ?? '') ?: null;
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $barcode = trim($_POST['barcode'] ?? '') ?: null;
        $description = trim($_POST['description'] ?? '') ?: null;

        if ($id <= 0 || empty($name) || $category_id <= 0 || $price <= 0 || $quantity < 0) {
            throw new Exception('Required fields missing or invalid');
        }

        // Verify category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Invalid category ID');
        }

        // Fetch existing product to get current image
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            throw new Exception('Product not found');
        }
        $image_path = $product['image'];

        // Process image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = processImageUpload($_FILES['image'], $id);
            if ($result['status'] === 'error') {
                throw new Exception($result['message']);
            }
            $image_path = $result['filename'];

            // Delete old image if it exists
            if ($product['image'] && file_exists(__DIR__ . '/' . $product['image'])) {
                unlink(__DIR__ . '/' . $product['image']);
            }
        }

        $stmt = $pdo->prepare("UPDATE products SET name = :name, company = :company, model = :model, category_id = :category_id, price = :price, quantity = :quantity, barcode = :barcode, image = :image, description = :description WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'company' => $company,
            'model' => $model,
            'category_id' => $category_id,
            'price' => $price,
            'quantity' => $quantity,
            'barcode' => $barcode,
            'image' => $image_path,
            'description' => $description
        ]);
        debugLog("Updated product ID: $id", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'Product updated successfully', 'image_path' => $image_path]);
    } catch (PDOException $e) {
        debugLog("Database error in update_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in update_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => $e->getMessage()]);
    }
    break;

 case 'delete_product':
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $csrf_token = isset($data['csrf_token']) ? $data['csrf_token'] : '';

        if ($id <= 0 || !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid product ID or CSRF token.');
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        debugLog("Deleted product ID: $id", 'api_log.log');
    } catch (Exception $e) {
        debugLog("Error deleting product: " . $e->getMessage(), 'api_log.log');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    break;

    case 'update_product_quantity':
    try {
        if (!isAdmin()) {
            throw new Exception('Unauthorized access');
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id']) || !isset($data['quantity']) || !isset($data['csrf_token'])) {
            throw new Exception('Missing required fields');
        }
        $product_id = (int)$data['id'];
        $quantity = (int)$data['quantity'];
        $csrf_token = $data['csrf_token'];

        if (!validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid CSRF token');
        }
        if ($product_id <= 0) {
            throw new Exception('Invalid product ID');
        }
        if ($quantity < 0) {
            throw new Exception('Quantity cannot be negative');
        }

        $stmt = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
        $affected_rows = $stmt->rowCount();

        if ($affected_rows === 0) {
            throw new Exception('Product not found or no changes made');
        }

        debugLog("Updated quantity for product ID: $product_id to $quantity", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'Quantity updated successfully']);
    } catch (PDOException $e) {
        debugLog("Database error in update_product_quantity: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in update_product_quantity: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => $e->getMessage()]);
    }
    break;

    case 'log_stock_adjustment':
    try {
        if (!isAdmin()) {
            throw new Exception('Unauthorized access');
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['product_id']) || !isset($data['quantity']) || !isset($data['adjustment_type']) || !isset($data['reason']) || !isset($data['csrf_token'])) {
            throw new Exception('Missing required fields');
        }
        $product_id = (int)$data['product_id'];
        $quantity = (int)$data['quantity'];
        $adjustment_type = trim($data['adjustment_type']);
        $reason = trim($data['reason']);
        $csrf_token = $data['csrf_token'];
        $user_id = $_SESSION['user_id'];

        if (!validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid CSRF token');
        }
        if ($product_id <= 0) {
            throw new Exception('Invalid product ID');
        }
        if ($quantity <= 0) {
            throw new Exception('Invalid quantity');
        }
        if (!in_array($adjustment_type, ['ADD', 'SUBTRACT'])) {
            throw new Exception('Invalid adjustment type');
        }

        $stmt = $pdo->prepare("
            INSERT INTO stock_adjustments (product_id, quantity, adjustment_type, reason, user_id, adjusted_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$product_id, $quantity, $adjustment_type, $reason, $user_id]);
        debugLog("Logged stock adjustment for product ID: $product_id", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'Adjustment logged']);
    } catch (PDOException $e) {
        debugLog("Database error in log_stock_adjustment: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in log_stock_adjustment: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => $e->getMessage()]);
    }
    break;
    
    case 'get_daily_sales_trend':
    try {
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        $query = "SELECT DATE(sale_date) as sale_day, SUM(total_amount) as daily_revenue
                  FROM sales_header
                  WHERE sale_date BETWEEN ? AND ?
                  GROUP BY DATE(sale_date)
                  ORDER BY sale_day ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        $sales_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched daily sales trend: start_date=$start_date, end_date=$end_date, rows=" . count($sales_trend), 'api_debug.log');
        echo json_encode($sales_trend);
    } catch (Exception $e) {
        debugLog("Error in get_daily_sales_trend: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    break;

    case 'generate_receipt':
    try {
        if (ob_get_length()) {
            ob_end_clean();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $sale_id = isset($data['sale_id']) ? (int)$data['sale_id'] : 0;
        $csrf_token = isset($data['csrf_token']) ? $data['csrf_token'] : '';
        
        if ($sale_id <= 0 || !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid sale ID or CSRF token.');
        }

        // Fetch sale details
        $query = "SELECT sh.id, sh.total_amount, sh.discount_amount, sh.loyalty_discount_amount, sh.final_amount, sh.sale_date, 
                         c.username, c.email, c.first_name, c.last_name
                  FROM sales_header sh
                  JOIN customers c ON sh.customer_id = c.id
                  WHERE sh.id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sale_id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sale) {
            throw new Exception('Sale not found.');
        }

        // Fetch sale items
        $query = "SELECT p.name, si.quantity, si.price, (si.quantity * si.price) as total,
                         d.name as discount_name, sd.discount_amount
                  FROM sale_items si
                  JOIN products p ON si.product_id = p.id
                  LEFT JOIN sale_discounts sd ON sd.sale_id = si.sale_id
                  LEFT JOIN discounts d ON sd.discount_id = d.id
                  WHERE si.sale_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sale_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate totals
        $subtotal = array_sum(array_column($items, 'total'));
        $total_discount = ($sale['discount_amount'] ?? 0) + ($sale['loyalty_discount_amount'] ?? 0);
        $tax_rate = 0.05; // 5%
        $tax_amount = $subtotal * $tax_rate;
        $total = $sale['final_amount'] ?? ($subtotal + $tax_amount - $total_discount);

        require_once 'vendor/autoload.php';
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('POS System');
        $pdf->SetTitle('Receipt');
        $pdf->SetSubject('Sale Receipt');
        $pdf->SetKeywords('Receipt, Sale, POS');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'POS System Company', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Receipt', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, "Sale ID: {$sale['id']}", 0, 1);
        $pdf->Cell(0, 5, "Date: " . date('Y-m-d H:i:s', strtotime($sale['sale_date'])), 0, 1);
        $customer_name = trim(($sale['first_name'] ?? '') . ' ' . ($sale['last_name'] ?? ''));
        $pdf->Cell(0, 5, "Customer: {$sale['username']} ($customer_name)", 0, 1);
        $pdf->Cell(0, 5, "Email: {$sale['email']}", 0, 1);
        $pdf->Ln(5);

        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(60, 7, 'Item', 1, 0, 'L', 1);
        $pdf->Cell(30, 7, 'Quantity', 1, 0, 'R', 1);
        $pdf->Cell(30, 7, 'Price', 1, 0, 'R', 1);
        $pdf->Cell(30, 7, 'Total', 1, 1, 'R', 1);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($items as $item) {
            $pdf->Cell(60, 6, $item['name'] . ($item['discount_name'] ? " ({$item['discount_name']})" : ''), 1);
            $pdf->Cell(30, 6, $item['quantity'], 1, 0, 'R');
            $pdf->Cell(30, 6, '$' . number_format($item['price'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, '$' . number_format($item['total'], 2), 1, 1, 'R');
        }

        $pdf->Ln(5);
        $pdf->Cell(120, 6, '', 0);
        $pdf->Cell(30, 6, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(30, 6, '$' . number_format($subtotal, 2), 0, 1, 'R');
        if ($total_discount > 0) {
            $pdf->Cell(120, 6, '', 0);
            $pdf->Cell(30, 6, 'Discount:', 0, 0, 'R');
            $pdf->Cell(30, 6, '-$' . number_format($total_discount, 2), 0, 1, 'R');
        }
        $pdf->Cell(120, 6, '', 0);
        $pdf->Cell(30, 6, 'Tax (5%):', 0, 0, 'R');
        $pdf->Cell(30, 6, '$' . number_format($tax_amount, 2), 0, 1, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(120, 6, '', 0);
        $pdf->Cell(30, 6, 'Total:', 0, 0, 'R');
        $pdf->Cell(30, 6, '$' . number_format($total, 2), 0, 1, 'R');

        $pdfContent = $pdf->Output('', 'S');
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"Receipt_{$sale_id}.pdf\"");
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $pdfContent;
        debugLog("Generated receipt PDF for sale ID: $sale_id", 'api_debug.log');
        exit;
    } catch (Exception $e) {
        debugLog("Error generating receipt PDF: " . $e->getMessage(), 'api_debug.log');
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Error generating receipt: ' . $e->getMessage()]);
        exit;
    }
    break;

    case 'log_error':
        $message = $data['message'] ?? 'Unknown error';
        debugLog("Client error: $message", 'api_debug.log');
        echo json_encode(['success' => true]);
        break;

    case 'generate_invoice':
        $sale_id = intval($data['sale_id'] ?? 0);
        debugLog("Generate invoice: sale_id=$sale_id, username={$_SESSION['username']}", 'api_debug.log');
        if ($sale_id <= 0) {
            debugLog("Error: Invalid sale ID", 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Invalid sale ID']);
            exit;
        }
        try {
            $query = "SELECT sh.id, sh.total_amount, sh.user_id, sh.sale_date, u.username AS client_name
                      FROM sales_header sh
                      JOIN users u ON sh.user_id = u.id
                      WHERE sh.id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sale_id]);
            $sale = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sale) {
                debugLog("Error: Sale ID $sale_id not found", 'api_debug.log');
                echo json_encode(['status' => 'error', 'message' => 'Sale not found']);
                exit;
            }
            if (isClient() && $sale['user_id'] !== $_SESSION['user_id']) {
                debugLog("Error: User {$_SESSION['user_id']} not authorized for sale_id=$sale_id", 'api_debug.log');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
                exit;
            }
            $query = "SELECT si.quantity, si.price, p.name
                      FROM sale_items si
                      JOIN products p ON si.product_id = p.id
                      WHERE si.sale_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sale_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($sale_id, 6, '0', STR_PAD_LEFT);
            $pdf = new TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('POS System');
            $pdf->SetTitle('Invoice ' . $invoice_number);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Invoice', 0, 1, 'C');
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->Cell(0, 10, 'Invoice Number: ' . $invoice_number, 0, 1);
            $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d H:i:s', strtotime($sale['sale_date'])), 0, 1);
            $pdf->Cell(0, 10, 'Client: ' . $sale['client_name'], 0, 1);
            $pdf->Ln(10);
            $pdf->SetFont('Helvetica', 'B', 12);
            $pdf->Cell(80, 10, 'Product', 1);
            $pdf->Cell(30, 10, 'Quantity', 1);
            $pdf->Cell(30, 10, 'Price', 1);
            $pdf->Cell(40, 10, 'Total', 1);
            $pdf->Ln();
            $pdf->SetFont('Helvetica', '', 12);
            foreach ($items as $item) {
                $pdf->Cell(80, 10, $item['name'], 1);
                $pdf->Cell(30, 10, $item['quantity'], 1);
                $pdf->Cell(30, 10, '$' . number_format($item['price'], 2), 1);
                $pdf->Cell(40, 10, '$' . number_format($item['quantity'] * $item['price'], 2), 1);
                $pdf->Ln();
            }
            $pdf->Ln(10);
            $pdf->SetFont('Helvetica', 'B', 12);
            $pdf->Cell(140, 10, 'Total Amount:', 0);
            $pdf->Cell(40, 10, '$' . number_format($sale['total_amount'], 2), 0);
            $pdf->Ln();
            $invoice_dir = __DIR__ . '/Invoices/';
            if (!is_dir($invoice_dir)) {
                mkdir($invoice_dir, 0755, true);
            }
            $file_path = 'Invoices/' . $invoice_number . '.pdf';
            $pdf->Output($invoice_dir . $invoice_number . '.pdf', 'F');
            $stmt = $pdo->prepare("INSERT INTO invoices (sale_id, invoice_number, file_path, generated_at, generated_by) 
                                   VALUES (?, ?, ?, NOW(), ?)");
            $stmt->execute([$sale_id, $invoice_number, $file_path, $_SESSION['user_id']]);
            debugLog("Invoice generated: sale_id=$sale_id, invoice_number=$invoice_number", 'api_debug.log');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $invoice_number . '.pdf"');
            readfile($invoice_dir . $invoice_number . '.pdf');
        } catch (Exception $e) {
            debugLog("Error in generate_invoice: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error generating invoice: ' . $e->getMessage()]);
        }
        break;

    case 'get_sales_report':
        try {
            $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
                debugLog("Invalid date format: start_date=$start_date, end_date=$end_date", 'api_debug.log');
                echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
                exit;
            }
            $query = "SELECT sh.id, sh.total_amount, sh.sale_date, u.username AS client_name, u.id AS client_id
                      FROM sales_header sh
                      LEFT JOIN users u ON sh.user_id = u.id
                      WHERE sh.sale_date BETWEEN ? AND ?";
            $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
            if ($client_id !== null) {
                $query .= " AND sh.user_id = ?";
                $params[] = $client_id;
            }
            $query .= " ORDER BY sh.sale_date DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debugLog("Fetched sales report: start_date=$start_date, end_date=$end_date, client_id=" . ($client_id ?? 'all') . ", rows=" . count($report), 'api_debug.log');
            echo json_encode($report);
        } catch (Exception $e) {
            debugLog("Error in get_sales_report: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'get_detailed_sales_report':
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-90 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            throw new Exception('Invalid date format.');
        }

        $query = "SELECT sh.id as sale_id, u.username as client_name, p.name as product_name,
                         si.price, si.quantity, (si.quantity * si.price) as total,
                         c.name as category_name, p.barcode, sh.sale_date,
                         d.name as discount_name, sd.discount_amount
                  FROM sales_header sh
                  JOIN users u ON sh.user_id = u.id
                  JOIN sale_items si ON si.sale_id = sh.id
                  JOIN products p ON si.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN sale_discounts sd ON sd.sale_id = sh.id AND sd.product_id = p.id
                  LEFT JOIN discounts d ON sd.discount_id = d.id
                  WHERE sh.sale_date >= ? AND sh.sale_date <= ?";
        $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];

        if ($search_query) {
            $query .= " AND (u.username LIKE ? OR p.name LIKE ? OR p.barcode LIKE ?)";
            $search_term = "%{$search_query}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $query .= " ORDER BY sh.sale_date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        debugLog("Fetched detailed sales report: start_date=$start_date, end_date=$end_date, search_query=$search_query, rows=" . count($sales), 'api_debug.log');
        echo json_encode($sales);
    } catch (Exception $e) {
        debugLog("Error in get_detailed_sales_report: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    break;

    case 'generate_sales_report_pdf':
    try {
        $start_date = $data['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
        $end_date = $data['end_date'] ?? date('Y-m-d');
        $client_id = isset($data['client_id']) ? intval($data['client_id']) : null;
        $product_query = trim($data['product_query'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            debugLog("Invalid date format for PDF: start_date=$start_date, end_date=$end_date", 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
            exit;
        }
        $query = "SELECT sh.id AS sale_id, u.username AS client_name, p.name AS product_name, si.price, si.quantity, 
                         (si.price * si.quantity) AS total, c.name AS category_name, p.barcode, si.sale_date,
                         d.name AS discount_name, sd.discount_amount
                  FROM sales_header sh
                  LEFT JOIN users u ON sh.user_id = u.id
                  JOIN sale_items si ON si.sale_id = sh.id
                  JOIN products p ON si.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN sale_discounts sd ON sd.sale_id = sh.id AND sd.product_id = p.id
                  LEFT JOIN discounts d ON sd.discount_id = d.id
                  WHERE sh.sale_date BETWEEN ? AND ?";
        $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
        if ($client_id !== null) {
            $query .= " AND sh.user_id = ?";
            $params[] = $client_id;
        }
        if ($product_query) {
            $query .= " AND (p.name LIKE ? OR p.barcode = ?)";
            $params[] = '%' . $product_query . '%';
            $params[] = $product_query;
        }
        $query .= " ORDER BY sh.sale_date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Generating sales report PDF: start_date=$start_date, end_date=$end_date, client_id=" . ($client_id ?? 'all') . ", product_query='$product_query', rows=" . count($sales), 'api_debug.log');
        
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('POS System');
        $pdf->SetTitle('Sales Report ' . $start_date . ' to ' . $end_date);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Sales Report', 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1);
        if ($client_id) {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$client_id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $pdf->Cell(0, 10, 'Client: ' . ($client['username'] ?? 'Unknown'), 0, 1);
        }
        if ($product_query) {
            $pdf->Cell(0, 10, 'Product Filter: ' . $product_query, 0, 1);
        }
        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(15, 10, 'Sale ID', 1);
        $pdf->Cell(25, 10, 'Client', 1);
        $pdf->Cell(30, 10, 'Product', 1);
        $pdf->Cell(20, 10, 'Price', 1);
        $pdf->Cell(15, 10, 'Qty', 1);
        $pdf->Cell(20, 10, 'Total', 1);
        $pdf->Cell(25, 10, 'Category', 1);
        $pdf->Cell(25, 10, 'Barcode', 1);
        $pdf->Cell(25, 10, 'Discount', 1);
        $pdf->Cell(20, 10, 'Disc Amt', 1);
        $pdf->Cell(30, 10, 'Sale Date', 1);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 8);
        $grand_total = 0;
        $total_discount = 0;
        foreach ($sales as $sale) {
            $pdf->Cell(15, 8, $sale['sale_id'], 1);
            $pdf->Cell(25, 8, $sale['client_name'] ?? 'Unknown', 1);
            $pdf->Cell(30, 8, $sale['product_name'], 1);
            $pdf->Cell(20, 8, '$' . number_format($sale['price'], 2), 1);
            $pdf->Cell(15, 8, $sale['quantity'], 1);
            $pdf->Cell(20, 8, '$' . number_format($sale['total'], 2), 1);
            $pdf->Cell(25, 8, $sale['category_name'] ?? 'N/A', 1);
            $pdf->Cell(25, 8, $sale['barcode'], 1);
            $pdf->Cell(25, 8, $sale['discount_name'] ?? 'None', 1);
            $pdf->Cell(20, 8, '$' . number_format($sale['discount_amount'] ?? 0, 2), 1);
            $pdf->Cell(30, 8, $sale['sale_date'], 1);
            $pdf->Ln();
            $grand_total += $sale['total'];
            $total_discount += ($sale['discount_amount'] ?? 0);
        }
        $pdf->Ln(5);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(135, 10, 'Grand Total:', 0);
        $pdf->Cell(40, 10, '$' . number_format($grand_total, 2), 0);
        $pdf->Ln();
        if ($total_discount > 0) {
            $pdf->Cell(135, 10, 'Total Discount:', 0);
            $pdf->Cell(40, 10, '-$' . number_format($total_discount, 2), 0);
            $pdf->Ln();
        }
        $report_filename = 'SalesReport_' . $start_date . '_to_' . $end_date . '.pdf';
        $report_dir = __DIR__ . '/Reports/';
        if (!is_dir($report_dir)) {
            mkdir($report_dir, 0755, true);
        }
        $file_path = 'Reports/' . $report_filename;
        $pdf->Output($report_dir . $report_filename, 'F');
        debugLog("Sales report PDF generated: filename=$report_filename", 'api_debug.log');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $report_filename . '"');
        readfile($report_dir . $report_filename);
    } catch (Exception $e) {
        debugLog("Error in generate_sales_report_pdf: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error generating PDF: ' . $e->getMessage()]);
    }
    break;

    // Add these cases in the switch ($action) block
 case 'add_discount':
    if (!isAdmin()) {
        debugLog("Unauthorized access to add_discount", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? '';
        $value = floatval($data['value'] ?? 0);
        $min_purchase_amount = !empty($data['min_purchase_amount']) ? floatval($data['min_purchase_amount']) : null;
        $product_id = !empty($data['product_id']) ? intval($data['product_id']) : null;
        $category_id = !empty($data['category_id']) ? intval($data['category_id']) : null;
        $start_date = $data['start_date'] ?? '';
        $end_date = $data['end_date'] ?? '';
        $csrf_token = $data['csrf_token'] ?? '';

        if (empty($name) || !in_array($type, ['percentage', 'fixed']) || $value <= 0 ||
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $start_date) ||
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $end_date) ||
            !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid input data or CSRF token.');
        }

        $stmt = $pdo->prepare("INSERT INTO discounts (name, type, value, min_purchase_amount, product_id, category_id, start_date, end_date, is_active) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)");
        $stmt->execute([$name, $type, $value, $min_purchase_amount, $product_id, $category_id, $start_date . ':00', $end_date . ':00']);
        debugLog("Added discount: $name", 'api_debug.log');
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        debugLog("Error adding discount: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    break;

 case 'update_discount':
    if (!isAdmin()) {
        debugLog("Unauthorized access to update_discount", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? '';
        $value = floatval($data['value'] ?? 0);
        $min_purchase_amount = !empty($data['min_purchase_amount']) ? floatval($data['min_purchase_amount']) : null;
        $product_id = !empty($data['product_id']) ? intval($data['product_id']) : null;
        $category_id = !empty($data['category_id']) ? intval($data['category_id']) : null;
        $start_date = $data['start_date'] ?? '';
        $end_date = $data['end_date'] ?? '';
        $is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $csrf_token = $data['csrf_token'] ?? '';

        if ($id <= 0 || empty($name) || !in_array($type, ['percentage', 'fixed']) || $value <= 0 ||
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $start_date) ||
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $end_date) ||
            !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid input data or CSRF token.');
        }

        $stmt = $pdo->prepare("UPDATE discounts SET name = ?, type = ?, value = ?, min_purchase_amount = ?, 
                               product_id = ?, category_id = ?, start_date = ?, end_date = ?, is_active = ? 
                               WHERE id = ?");
        $stmt->execute([$name, $type, $value, $min_purchase_amount, $product_id, $category_id, 
                        $start_date . ':00', $end_date . ':00', $is_active, $id]);
        debugLog("Updated discount ID: $id", 'api_debug.log');
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        debugLog("Error updating discount: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    break;

 case 'get_discounts':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_discounts", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $query = "SELECT d.id, d.name, d.type, d.value, d.min_purchase_amount, d.product_id, d.category_id, 
                         d.start_date, d.end_date, d.is_active, p.name AS product_name, c.name AS category_name
                  FROM discounts d 
                  LEFT JOIN products p ON d.product_id = p.id 
                  LEFT JOIN categories c ON d.category_id = c.id
                  ORDER BY d.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched " . count($discounts) . " discounts", 'api_debug.log');
        echo json_encode($discounts);
    } catch (Exception $e) {
        debugLog("Error fetching discounts: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

 case 'delete_discount':
    if (!isAdmin()) {
        debugLog("Unauthorized access to delete_discount", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $csrf_token = $data['csrf_token'] ?? '';
        if ($id <= 0 || !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid input or CSRF token.');
        }
        $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
        $stmt->execute([$id]);
        debugLog("Deleted discount ID: $id", 'api_debug.log');
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        debugLog("Error deleting discount: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    break;



    case 'get_all_discounts':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_all_discounts", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $query = "SELECT d.id, d.name, d.type, d.value, d.min_purchase_amount, d.product_id, d.category_id, d.start_date, d.end_date, p.name AS product_name, c.name AS category_name
                  FROM discounts d
                  LEFT JOIN products p ON d.product_id = p.id
                  LEFT JOIN categories c ON d.category_id = c.id
                  ORDER BY d.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched " . count($discounts) . " discounts", 'api_debug.log');
        echo json_encode($discounts);
    } catch (PDOException $e) {
        debugLog("Error fetching all discounts: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    break;

    case 'export_sales_report_csv':
        try {
            $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
                debugLog("Invalid date format for CSV export: start_date=$start_date, end_date=$end_date", 'api_debug.log');
                echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
                exit;
            }
            $query = "SELECT sh.id, sh.total_amount, sh.sale_date, u.username AS client_name
                      FROM sales_header sh
                      LEFT JOIN users u ON sh.user_id = u.id
                      WHERE sh.sale_date BETWEEN ? AND ?";
            $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
            if ($client_id !== null) {
                $query .= " AND sh.user_id = ?";
                $params[] = $client_id;
            }
            $query .= " ORDER BY sh.sale_date DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debugLog("Exporting sales report CSV: start_date=$start_date, end_date=$end_date, client_id=" . ($client_id ?? 'all') . ", rows=" . count($report), 'api_debug.log');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="sales_report_' . $start_date . '_to_' . $end_date . '.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Sale ID', 'Client Name', 'Total Amount', 'Sale Date']);
            foreach ($report as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['client_name'] ?? 'Unknown',
                    $row['total_amount'] ?? 0,
                    $row['sale_date']
                ]);
            }
            fclose($output);
            exit;
        } catch (Exception $e) {
            debugLog("Error in export_sales_report_csv: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'get_low_stock_products':
        try {
            $threshold = getLowStockThreshold();
            $query = "SELECT p.id, p.name, p.price, p.quantity, p.image, p.category_id, c.name AS category_name, p.description, p.barcode 
                      FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.quantity <= ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$threshold]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debugLog("Fetched " . count($products) . " low stock products with threshold=$threshold", 'api_debug.log');
            echo json_encode($products);
        } catch (Exception $e) {
            debugLog("Error in get_low_stock_products: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'set_low_stock_threshold':
        $threshold = intval($data['threshold'] ?? 0);
        debugLog("Set low stock threshold: threshold=$threshold", 'api_debug.log');
        if ($threshold <= 0) {
            debugLog("Error: Invalid threshold", 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Threshold must be greater than zero']);
            exit;
        }
        try {
            if (setLowStockThreshold($threshold)) {
                clearCache('get_low_stock_products_*');
                debugLog("Low stock threshold set to $threshold", 'api_debug.log');
                echo json_encode(['status' => 'success']);
            } else {
                debugLog("Failed to set low stock threshold", 'api_debug.log');
                echo json_encode(['status' => 'error', 'message' => 'Failed to set threshold']);
            }
        } catch (Exception $e) {
            debugLog("Error in set_low_stock_threshold: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'add_product':
            if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                exit;
            }

            $name = $data['name'] ?? '';
            $company = $data['company'] ?? '';
            $model = $data['model'] ?? '';
            $category_id = $data['category_id'] ?? '';
            $price = $data['price'] ?? 0;
            $quantity = $data['quantity'] ?? 0;
            $barcode = $data['barcode'] ?? '';
            $image = $data['image'] ?? '';
            $description = $data['description'] ?? '';

            if (empty($name) || empty($category_id) || $price <= 0 || $quantity < 0) {
                echo json_encode(['status' => 'error', 'message' => 'Required fields missing or invalid']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, company, model, category_id, price, quantity, barcode, image, description) 
                                   VALUES (:name, :company, :model, :category_id, :price, :quantity, :barcode, :image, :description)");
            $stmt->execute([
                'name' => $name,
                'company' => $company,
                'model' => $model,
                'category_id' => $category_id,
                'price' => $price,
                'quantity' => $quantity,
                'barcode' => $barcode,
                'image' => $image,
                'description' => $description
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
            break;

    case 'edit_product':
    try {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (!validateCsrfToken($data['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('Invalid product ID');
        }

        // Fetch product details
        $stmt = $pdo->prepare("SELECT p.id, p.name, p.company, p.model, p.category_id, p.price, p.quantity, p.barcode, p.image, p.description, p.stock_quantity, p.is_featured, p.created_at, p.updated_at, c.name AS category_name
                               FROM products p
                               LEFT JOIN categories c ON p.category_id = c.id
                               WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Product not found');
        }

        debugLog("Fetched product for editing: ID=$id, Name={$product['name']}", 'api_debug.log');
        sendJsonResponse(200, ['status' => 'success', 'product' => $product]);
    } catch (PDOException $e) {
        debugLog("Database error in edit_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in edit_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;


    case 'delete_product':
        $product_id = intval($data['product_id'] ?? 0);
        debugLog("Delete product: product_id=$product_id", 'api_debug.log');
        if ($product_id <= 0) {
            debugLog("Error: Invalid product ID", 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM sale_items WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $pdo->commit();
            debugLog("Product deleted: product_id=$product_id", 'api_debug.log');
            clearCache('get_products_*');
            clearCache('get_available_products_*');
            clearCache('get_low_stock_products_*');
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            debugLog("Error in delete_product: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'get_products':
    try {
        // Validate parameters
        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = isset($_GET['page_size']) && is_numeric($_GET['page_size']) ? max(1, min(100, (int)$_GET['page_size'])) : 10;
        $offset = ($page - 1) * $pageSize;

        // Build query
        $query = "SELECT p.id, p.name, p.price, p.quantity, p.image, p.description, p.barcode, c.name AS category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        $params = [];
        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.barcode LIKE :search)";
            $params[':search'] = "%$search%";
        }
 if ($categoryId !== null) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        $query .= " ORDER BY p.name ASC LIMIT :page_size OFFSET :offset";
        $params[':page_size'] = $pageSize;
        $params[':offset'] = $offset;

        // Prepare and execute query
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $type = ($key === ':page_size' || $key === ':offset' || $key === ':category_id') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total products
        $countQuery = "SELECT COUNT(*) FROM products p WHERE 1=1";
        $countParams = [];
        if ($search) {
            $countQuery .= " AND (p.name LIKE :search OR p.barcode LIKE :search)";
            $countParams[':search'] = "%$search%";
        }
        if ($categoryId !== null) {
            $countQuery .= " AND p.category_id = :category_id";
            $countParams[':category_id'] = $categoryId;
        }
        $countStmt = $pdo->prepare($countQuery);
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();
        $totalPages = ceil($total / $pageSize);

        // Log success
        debugLog("Fetched " . count($products) . " products: search='$search', category_id=" . ($categoryId ?? 'null') . ", page=$page, page_size=$pageSize", 'api_debug.log');

        // Send response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'products' => $products,
            'total_pages' => $totalPages,
            'total_products' => $total
        ]);
    } catch (PDOException $e) {
        debugLog("Database error in get_products: " . $e->getMessage() . ", Query: $query, Params: " . json_encode($params), 'api_debug.log');
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in get_products: " . $e->getMessage(), 'api_debug.log');
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
    }
    break;

    case 'get_available_products':
    try {
        $search = trim($_GET['search'] ?? '');
        $query = "SELECT p.id, p.name, p.price, p.quantity, p.image, p.category_id, c.name AS category_name, p.description, p.barcode 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.quantity > 0";
        $params = [];
        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.barcode LIKE :search)";
            $params[':search'] = "%$search%";
        }
        $query .= " ORDER BY p.name ASC";
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        debugLog("Fetched " . count($products) . " available products: search='$search'", 'api_debug.log');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'products' => $products
        ]);
    } catch (PDOException $e) {
        debugLog("Database error in get_available_products: " . $e->getMessage() . ", Query: $query, Params: " . json_encode($params), 'api_debug.log');
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in get_available_products: " . $e->getMessage(), 'api_debug.log');
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    break;

    // case 'process_sale':
    //     $product_id = intval($data['product_id'] ?? 0);
    //     $quantity = intval($data['quantity'] ?? 0);
    //     $user_id = intval($data['client_id'] ?? 0);
    //     debugLog("Process sale: product_id=$product_id, quantity=$quantity, user_id=$user_id", 'api_debug.log');
    //     if ($product_id <= 0 || $quantity <= 0 || $user_id <= 0) {
    //         debugLog("Error: Invalid sale data", 'api_debug.log');
    //         echo json_encode(['status' => 'error', 'message' => 'Invalid sale data']);
    //         exit;
    //     }
    //     try {
    //         $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
    //         $stmt->execute([$user_id]);
    //         $user = $stmt->fetch(PDO::FETCH_ASSOC);
    //         if (!$user || ($user['role'] !== 'client' && isClient() && $user_id !== $_SESSION['user_id'])) {
    //             debugLog("Error: Invalid or unauthorized user ID $user_id", 'api_debug.log');
    //             echo json_encode(['status' => 'error', 'message' => 'Invalid or unauthorized user']);
    //             exit;
    //         }
    //         $pdo->beginTransaction();
    //         $productStmt = $pdo->prepare("SELECT price, quantity FROM products WHERE id = ? FOR UPDATE");
    //         $productStmt->execute([$product_id]);
    //         $product = $productStmt->fetch(PDO::FETCH_ASSOC);
    //         if (!$product) {
    //             throw new Exception("Product ID $product_id not found");
    //         }
    //         if ($product['quantity'] < $quantity) {
    //             throw new Exception("Insufficient stock for product ID $product_id");
    //         }
    //         $total_amount = $product['price'] * $quantity;
    //         $stmt = $pdo->prepare("INSERT INTO sales_header (total_amount, user_id, sale_date) VALUES (?, ?, NOW())");
    //         $stmt->execute([$total_amount, $user_id]);
    //         $sale_id = $pdo->lastInsertId();
    //         $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, sale_date) VALUES (?, ?, ?, ?, NOW())");
    //         $stmt->execute([$sale_id, $product_id, $quantity, $product['price']]);
    //         $updateStmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    //         $updateStmt->execute([$quantity, $product_id]);
    //         $pdo->commit();
    //         debugLog("Sale processed: sale_id=$sale_id", 'api_debug.log');
    //         clearCache('get_products_*');
    //         clearCache('get_available_products_*');
    //         clearCache('get_low_stock_products_*');
    //         clearCache('get_sales_report_*');
    //         echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);
    //     } catch (Exception $e) {
    //         $pdo->rollBack();
    //         debugLog("Error in process_sale: " . $e->getMessage(), 'api_debug.log');
    //         echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    //     }
    //     break;

    case 'get_product_suggestions':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_product_suggestions", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $term = trim($_GET['term'] ?? '');
        if (strlen($term) < 2) {
            echo json_encode([]);
            exit;
        }
        $query = "SELECT name FROM products WHERE name LIKE ? LIMIT 10";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['%' . $term . '%']);
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
        debugLog("Fetched " . count($products) . " product suggestions for term: $term", 'api_debug.log');
        header('Content-Type: application/json');
        echo json_encode($products);
    } catch (Exception $e) {
        debugLog("Error fetching product suggestions: " . $e->getMessage(), 'api_debug.log');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'export_products_csv':
        try {
            $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.quantity, c.name AS category_name, p.description, p.barcode 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debugLog("Exporting " . count($products) . " products to CSV", 'api_debug.log');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="products_' . date('Y-m-d') . '.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'Price', 'Quantity', 'Category', 'Description', 'Barcode']);
            foreach ($products as $product) {
                fputcsv($output, [
                    $product['id'],
                    $product['name'],
                    $product['price'],
                    $product['quantity'],
                    $product['category_name'] ?? 'N/A',
                    $product['description'] ?? '',
                    $product['barcode']
                ]);
            }
            fclose($output);
            exit;
        } catch (Exception $e) {
            debugLog("Error in export_products_csv: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'get_sales':
        try {
            $user_id = isClient() ? $_SESSION['user_id'] : null;
            $query = "SELECT sh.id, sh.total_amount, sh.sale_date, u.username AS client_name
                      FROM sales_header sh
                      LEFT JOIN users u ON sh.user_id = u.id";
            $params = [];
            if ($user_id) {
                $query .= " WHERE sh.user_id = ?";
                $params[] = $user_id;
            }
            $query .= " ORDER BY sh.sale_date DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debugLog("Fetched " . count($sales) . " sales for user_id=" . ($user_id ?? 'all'), 'api_debug.log');
            echo json_encode($sales);
        } catch (Exception $e) {
            debugLog("Error in get_sales: " . $e->getMessage(), 'api_debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    default:
        debugLog("Invalid action: $action", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;

    case 'get_all_customers':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_all_customers", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $search = $_GET['search'] ?? '';
        $query = "SELECT id, username, email, first_name, last_name, phone, address, created_at 
                  FROM customers";
        $params = [];
        if ($search) {
            $query .= " WHERE username LIKE ? OR email LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $query .= " ORDER BY username ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched " . count($customers) . " customers", 'api_debug.log');
        echo json_encode($customers);
    } catch (Exception $e) {
        debugLog("Error in get_all_customers: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

 case 'get_customer_history':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_customer_history", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = intval($data['customer_id'] ?? 0);
        if ($customer_id <= 0) {
            throw new Exception('Invalid customer ID');
        }
        $query = "SELECT sh.id AS order_id, sh.sale_date, sh.total_amount, sh.order_status
                  FROM sales_header sh
                  WHERE sh.customer_id = ?
                  ORDER BY sh.sale_date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$customer_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched history for customer_id: $customer_id", 'api_debug.log');
        echo json_encode($history);
    } catch (Exception $e) {
        debugLog("Error fetching customer history: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'get_stock_adjustments':
    if (!isAdmin()) {
        sendJsonResponse(403, ['success' => false, 'message' => 'Unauthorized access']);
    }
    try {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        $query = "
            SELECT sa.id, p.name AS product_name, p.quantity AS current_quantity, sa.quantity, sa.adjustment_type, 
                   sa.reason, u.username, sa.adjusted_at
            FROM stock_adjustments sa
            JOIN products p ON sa.product_id = p.id
            JOIN users u ON sa.user_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($search) {
            $query .= " AND (p.name LIKE ? OR sa.reason LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($start_date) {
            $query .= " AND sa.adjusted_at >= ?";
            $params[] = $start_date;
        }
        if ($end_date) {
            $query .= " AND sa.adjusted_at <= ?";
            $params[] = $end_date . ' 23:59:59';
        }
        $query .= " ORDER BY sa.adjusted_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $adjustments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(200, $adjustments);
    } catch (Exception $e) {
        debugLog("Error in get_stock_adjustments: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Error fetching adjustments']);
    }
    break;

    case 'get_sales_forecast':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_sales_forecast", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $days = intval($_GET['days'] ?? 30);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date) || $days <= 0) {
            throw new Exception('Invalid input parameters');
        }
        // Fetch historical sales
        $stmt = $pdo->prepare("
            SELECT p.name AS product_name, SUM(si.quantity) AS historical_sales
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN sales_header sh ON si.sale_id = sh.id
            WHERE sh.sale_date BETWEEN ? AND ?
            GROUP BY p.id, p.name
        ");
        $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        $historical = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Simple forecast: assume same sales volume for next period
        $forecasts = array_map(function($row) use ($days) {
            $daily_avg = $row['historical_sales'] / 90; // Assuming 90-day historical period
            $predicted = $daily_avg * $days;
            return [
                'product_name' => $row['product_name'],
                'historical_sales' => $row['historical_sales'],
                'predicted_sales' => round($predicted, 2)
            ];
        }, $historical);
        debugLog("Generated sales forecast: start_date=$start_date, end_date=$end_date, days=$days, products=" . count($forecasts), 'api_debug.log');
        echo json_encode(['status' => 'success', 'forecasts' => $forecasts]);
    } catch (Exception $e) {
        debugLog("Error in get_sales_forecast: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'get_customer_detailed_history':
    if (!isAdmin()) {
        debugLog("Unauthorized access to get_customer_detailed_history", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['customer_id']) || !isset($data['csrf_token'])) {
            throw new Exception('Missing required parameters: customer_id or csrf_token');
        }
        $customer_id = intval($data['customer_id']);
        validateCsrfToken($data['csrf_token']);
        if ($customer_id <= 0) throw new Exception('Invalid customer ID');

        // Check if customer exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Customer not found');
        }

        // Fetch detailed history
        $stmt = $pdo->prepare("
            SELECT 
                sh.id AS sale_id,
                sh.sale_date,
                p.name AS product_name,
                si.quantity,
                si.price AS unit_price,
                (si.quantity * si.price) AS subtotal,
                sh.discount_amount,
                sh.loyalty_discount_amount,
                sh.final_amount,
                (si.quantity * si.price * 0.05) AS tax_amount
            FROM sales_header sh
            JOIN sale_items si ON sh.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE sh.customer_id = ?
            ORDER BY sh.sale_date DESC
        ");
        $stmt->execute([$customer_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debugLog("Fetched detailed history for customer_id=$customer_id, rows=" . count($history), 'api_debug.log');
        echo json_encode($history);
    } catch (Exception $e) {
        debugLog("Error in get_customer_detailed_history: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString(), 'api_debug.log');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'generate_customer_history_pdf':
    try {
        if (ob_get_length()) {
            ob_end_clean();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $csrf_token = isset($data['csrf_token']) ? $data['csrf_token'] : '';
        
        if ($customer_id <= 0 || !validateCsrfToken($csrf_token)) {
            throw new Exception('Invalid customer ID or CSRF token.');
        }

        // Fetch customer details
        $stmt = $pdo->prepare("SELECT username, email, first_name, last_name FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            throw new Exception('Customer not found.');
        }

        // Fetch purchase history
        $stmt = $pdo->prepare("
            SELECT 
                sh.id AS sale_id,
                sh.sale_date,
                p.name AS product_name,
                si.quantity,
                si.price AS unit_price,
                (si.quantity * si.price) AS subtotal,
                sh.discount_amount,
                sh.loyalty_discount_amount,
                sh.final_amount,
                (si.quantity * si.price * 0.05) AS tax_amount
            FROM sales_header sh
            JOIN sale_items si ON sh.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE sh.customer_id = ?
            ORDER BY sh.sale_date DESC
        ");
        $stmt->execute([$customer_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'vendor/autoload.php';
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('POS System');
        $pdf->SetTitle('Customer Purchase History');
        $pdf->SetSubject('Customer Purchase History Report');
        $pdf->SetKeywords('Customer, Purchase History, POS');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'POS System Company', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Customer Purchase History Report', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);
        $customer_name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        $pdf->Cell(0, 5, "Customer: {$customer['username']} ($customer_name)", 0, 1);
        $pdf->Cell(0, 5, "Email: {$customer['email']}", 0, 1);
        $pdf->Cell(0, 5, "Generated: " . date('Y-m-d H:i:s'), 0, 1);
        $pdf->Ln(5);

        if (empty($history)) {
            $pdf->Cell(0, 10, 'No purchase history found.', 0, 1, 'C');
        } else {
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(30, 7, 'Order ID', 1, 0, 'L', 1);
            $pdf->Cell(40, 7, 'Date', 1, 0, 'L', 1);
            $pdf->Cell(50, 7, 'Product', 1, 0, 'L', 1);
            $pdf->Cell(20, 7, 'Qty', 1, 0, 'R', 1);
            $pdf->Cell(25, 7, 'Unit Price', 1, 0, 'R', 1);
            $pdf->Cell(25, 7, 'Subtotal', 1, 0, 'R', 1);
            $pdf->Cell(25, 7, 'Tax (5%)', 1, 0, 'R', 1);
            $pdf->Cell(25, 7, 'Total', 1, 1, 'R', 1);

            $pdf->SetFont('helvetica', '', 10);
            foreach ($history as $sale) {
                $pdf->Cell(30, 6, $sale['sale_id'], 1);
                $pdf->Cell(40, 6, date('Y-m-d H:i:s', strtotime($sale['sale_date'])), 1);
                $pdf->Cell(50, 6, $sale['product_name'], 1);
                $pdf->Cell(20, 6, $sale['quantity'], 1, 0, 'R');
                $pdf->Cell(25, 6, '$' . number_format($sale['unit_price'], 2), 1, 0, 'R');
                $pdf->Cell(25, 6, '$' . number_format($sale['subtotal'], 2), 1, 0, 'R');
                $pdf->Cell(25, 6, '$' . number_format($sale['tax_amount'], 2), 1, 0, 'R');
                $pdf->Cell(25, 6, '$' . number_format($sale['final_amount'], 2), 1, 1, 'R');
            }
        }

        $pdfContent = $pdf->Output('', 'S');
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"CustomerHistory_{$customer_id}.pdf\"");
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $pdfContent;
        debugLog("Generated customer history PDF for customer ID: $customer_id", 'api_debug.log');
        exit;
    } catch (Exception $e) {
        debugLog("Error generating customer history PDF: " . $e->getMessage(), 'api_debug.log');
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Error generating PDF: ' . $e->getMessage()]);
        exit;
    }
    break;

    case 'update_customer':
    if (!isAdmin()) {
        debugLog("Unauthorized access to update_customer", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        validateCsrfToken($data['csrf_token'] ?? '');
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) throw new Exception('Invalid customer ID');
        if (empty($data['username']) || empty($data['email'])) {
            throw new Exception('Username and email are required');
        }
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$data['username'], $data['email'], $id]);
        if ($stmt->fetch()) {
            throw new Exception('Username or email already exists for another customer');
        }
        $stmt = $pdo->prepare("UPDATE customers SET 
            username = ?, email = ?, first_name = ?, last_name = ?, 
            phone = ?, address = ?, updated_at = NOW() 
            WHERE id = ?");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $id
        ]);
        debugLog("Updated customer ID: $id", 'api_debug.log');
        echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully']);
    } catch (Exception $e) {
        debugLog("Error in update_customer: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'delete_customer':
    if (!isAdmin()) {
        debugLog("Unauthorized access to delete_customer", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        validateCsrfToken($data['csrf_token'] ?? '');
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) throw new Exception('Invalid customer ID');

        // Check if customer has orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_header WHERE customer_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Cannot delete customer with existing orders');
        }

        // Delete customer
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            throw new Exception('Customer not found');
        }

        debugLog("Deleted customer ID: $id", 'api_debug.log');
        echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully']);
    } catch (Exception $e) {
        debugLog("Error in delete_customer: " . $e->getMessage(), 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'export_stock_adjustments_csv':
    if (!isAdmin()) {
        debugLog("Unauthorized access to export_stock_adjustments_csv", 'api_debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
    try {
        $search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : null;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="stock_adjustments_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Product', 'Current Quantity', 'Adjustment', 'Reason', 'Adjusted By', 'Date']);
        $query = "SELECT sa.id, p.name AS product_name, p.quantity AS current_quantity, 
                         sa.quantity, sa.reason, u.username AS user_name, sa.adjusted_at
                  FROM stock_adjustments sa
                  JOIN products p ON sa.product_id = p.id
                  LEFT JOIN users u ON sa.user_id = u.id
                  WHERE 1=1";
        $params = [];
        if ($search) {
            $query .= " AND (p.name LIKE ? OR sa.reason LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }
        if ($start_date) {
            $query .= " AND DATE(sa.adjusted_at) >= ?";
            $params[] = $start_date;
        }
        if ($end_date) {
            $query .= " AND DATE(sa.adjusted_at) <= ?";
            $params[] = $end_date;
        }
        $query .= " ORDER BY sa.adjusted_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['id'],
                $row['product_name'],
                $row['current_quantity'],
                $row['quantity'],
                $row['reason'],
                $row['user_name'] ?? 'N/A',
                $row['adjusted_at']
            ]);
        }
        debugLog("Exported stock adjustments CSV: rows=" . $stmt->rowCount(), 'api_debug.log');
        fclose($output);
        exit;
    } catch (Exception $e) {
        debugLog("Error in export_stock_adjustments_csv: " . $e->getMessage(), 'api_debug.log');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    


    case 'add_customer':
           if (!isAdmin()) {
               debugLog("Unauthorized access to add_customer", 'api_debug.log');
               echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
               exit;
           }
           try {
               $data = json_decode(file_get_contents('php://input'), true);
               validateCsrfToken($data['csrf_token'] ?? '');
               $username = trim($data['username'] ?? '');
               $email = trim($data['email'] ?? '');
               $first_name = trim($data['first_name'] ?? '') ?: null;
               $last_name = trim($data['last_name'] ?? '') ?: null;
               $phone = trim($data['phone'] ?? '') ?: null;
               $address = trim($data['address'] ?? '') ?: null;

               if (empty($username) || empty($email)) {
                   throw new Exception('Username and email are required');
               }

               // Validate email format
               if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                   throw new Exception('Invalid email format');
               }

               // Check for duplicate username or email
               $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE username = ? OR email = ?");
               $stmt->execute([$username, $email]);
               if ($stmt->fetchColumn() > 0) {
                   throw new Exception('Username or email already exists');
               }

               // Insert customer
               $stmt = $pdo->prepare("INSERT INTO customers (username, email, first_name, last_name, phone, address, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, NOW())");
               $stmt->execute([$username, $email, $first_name, $last_name, $phone, $address]);

               debugLog("Added customer: $username", 'api_debug.log');
               echo json_encode(['status' => 'success', 'message' => 'Customer added successfully']);
           } catch (Exception $e) {
               debugLog("Error in add_customer: " . $e->getMessage(), 'api_debug.log');
               echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
           }
           break;


           case 'update_loyalty_points':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        debugLog("Invalid request method for update_loyalty_points: {$_SERVER['REQUEST_METHOD']}", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    if (!isAdmin()) {
        debugLog("Unauthorized access to update_loyalty_points", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['customer_id']) || !isset($data['points']) || !isset($data['csrf_token'])) {
        debugLog("Missing parameters for update_loyalty_points", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for update_loyalty_points", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    $customer_id = (int)$data['customer_id'];
    $points = (int)$data['points'];
    if ($customer_id <= 0) {
        debugLog("Invalid customer_id: $customer_id", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        if (!$stmt->fetch()) {
            debugLog("Customer not found: $customer_id", 'api_debug.log');
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?");
        $stmt->execute([$points, $customer_id]);
        $stmt = $pdo->prepare("SELECT loyalty_points FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $new_points = $stmt->fetchColumn();
        debugLog("Updated loyalty points for customer_id: $customer_id, points: $points, new_points: $new_points", 'api_debug.log');
        echo json_encode(['success' => true, 'new_points' => $new_points]);
    } catch (PDOException $e) {
        debugLog("Database error in update_loyalty_points: " . $e->getMessage(), 'api_debug.log');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    break;

    case 'redeem_loyalty_points':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        debugLog("Invalid request method for redeem_loyalty_points: {$_SERVER['REQUEST_METHOD']}", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    if (!isAdmin()) {
        debugLog("Unauthorized access to redeem_loyalty_points", 'api_debug.log');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['customer_id']) || !isset($data['points_to_redeem']) || !isset($data['csrf_token'])) {
        debugLog("Missing parameters for redeem_loyalty_points", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for redeem_loyalty_points", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    $customer_id = (int)$data['customer_id'];
    $points_to_redeem = (int)$data['points_to_redeem'];
    if ($customer_id <= 0 || $points_to_redeem <= 0) {
        debugLog("Invalid customer_id: $customer_id or points_to_redeem: $points_to_redeem", 'api_debug.log');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID or points']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT loyalty_points FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $current_points = $stmt->fetchColumn();
        if ($current_points === false) {
            debugLog("Customer not found: $customer_id", 'api_debug.log');
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }
        if ($current_points < $points_to_redeem) {
            debugLog("Insufficient points for customer_id: $customer_id, current: $current_points, requested: $points_to_redeem", 'api_debug.log');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Insufficient loyalty points']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ?");
        $stmt->execute([$points_to_redeem, $customer_id]);
        $discount_amount = $points_to_redeem / 100; // Define explicitly or use POINTS_REDEMPTION_RATE
        debugLog("Redeemed $points_to_redeem points for customer_id: $customer_id, discount: $discount_amount", 'api_debug.log');
        echo json_encode(['success' => true, 'new_points' => $current_points - $points_to_redeem, 'discount_amount' => $discount_amount]);
    } catch (PDOException $e) {
        debugLog("Database error in redeem_loyalty_points: " . $e->getMessage(), 'api_debug.log');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    break;

           case 'login':
            session_start();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        debugLog("Invalid request method for login: {$_SERVER['REQUEST_METHOD']}", 'api_debug.log');
        sendJsonResponse(405, ['success' => false, 'message' => 'Method not allowed']);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['username']) || empty($data['password']) || empty($data['csrf_token'])) {
        debugLog("Missing login parameters", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Missing parameters']);
    }
    $username = trim($data['username']);
    $password = $data['password'];
    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for login: {$data['csrf_token']}", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid CSRF token']);
    }
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Add this line
            if (isset($data['remember']) && $data['remember']) {
                $remember_token = bin2hex(random_bytes(32));
                $token_expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expires_at = ? WHERE id = ?");
                $stmt->execute([$remember_token, $token_expires_at, $user['id']]);
                setcookie('user', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }
            $new_csrf_token = generateCsrfToken();
            debugLog("Login successful for user: $username, role: {$user['role']}", 'api_debug.log');
            sendJsonResponse(200, [
                'success' => true,
                'message' => 'Login successful',
                'csrf_token' => $new_csrf_token
            ]);
        } else {
            debugLog("Login failed for user: $username - Invalid credentials", 'api_debug.log');
            sendJsonResponse(401, ['success' => false, 'message' => 'Invalid username or password']);
        }
    } catch (PDOException $e) {
        debugLog("Login error for user $username: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error']);
    }
    break;

case 'register':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        debugLog("Invalid request method for register: {$_SERVER['REQUEST_METHOD']}", 'api_debug.log');
        sendJsonResponse(405, ['success' => false, 'message' => 'Method not allowed']);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['csrf_token'])) {
        debugLog("Missing register parameters", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Missing parameters']);
    }

    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];

    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for register: {$data['csrf_token']}", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid CSRF token']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        debugLog("Invalid email format for register: $email", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid email format']);
    }

    if (strlen($password) < 8) {
        debugLog("Password too short for register: $username", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Password must be at least 8 characters']);
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        debugLog("Invalid username length for register: $username", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            debugLog("Duplicate username or email for register: $username, $email", 'api_debug.log');
            sendJsonResponse(400, ['success' => false, 'message' => 'Username or email already exists']);
        }

        $role = DEMO_MODE ? 'admin' : 'user';

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at)
            VALUES (?, ?, ?, 'admin', NOW())
        ");
        $stmt->execute([$username, $email, $hashedPassword]);

        $user_id = $pdo->lastInsertId();
        debugLog("Registered new ADMIN user: $username (ID: $user_id)", 'api_debug.log');

        $new_csrf_token = generateCsrfToken();

        sendJsonResponse(200, [
            'success' => true,
            'message' => 'Registration successful - You are registered as an admin',
            'csrf_token' => $new_csrf_token
        ]);
    } catch (PDOException $e) {
        debugLog("Database error in register for user $username: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error']);
    }
    break;



   case 'get_product_catalog':
    try {
        // Validate parameters
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['page_size']) && is_numeric($_GET['page_size']) ? max(1, (int)$_GET['page_size']) : 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $categoryId = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? (int)$_GET['category_id'] : null;

        if ($perPage > 100) {
            throw new Exception('Page size cannot exceed 100');
        }

        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1=1';
        $params = [];

        if ($search) {
            $where .= ' AND (p.name LIKE :search OR p.company LIKE :search OR p.model LIKE :search OR p.barcode LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        if ($categoryId !== null) {
            $where .= ' AND p.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        // Count total products
        $countQuery = "SELECT COUNT(DISTINCT p.id)
                       FROM products p
                       LEFT JOIN categories c ON p.category_id = c.id
                       $where";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalProducts = (int)$countStmt->fetchColumn();
        $totalPages = ceil($totalProducts / $perPage);

        // Fetch products
        $query = "SELECT p.id, p.name, p.company, p.model, p.category_id, p.barcode,
                         p.price, p.quantity, p.image AS products_image, p.description,
                         c.name AS category_name
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  $where
                  ORDER BY p.name ASC
                  LIMIT :offset, :per_page";
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log for debugging
        $sampleProduct = !empty($products) ? json_encode($products[0]) : 'No products';
        debugLog("get_product_catalog: Found " . count($products) . " products for page=$page, per_page=$perPage, search='$search', category_id=" . ($categoryId ?? 'null') . ", sample=$sampleProduct", 'api_debug.log');

        sendJsonResponse(200, [
            'status' => 'success',
            'products' => $products,
            'total_pages' => $totalPages,
            'total_products' => $totalProducts
        ]);
    } catch (PDOException $e) {
        debugLog("Database error in get_product_catalog: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in get_product_catalog: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

case 'add_to_cart':
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            debugLog("JSON decode error: " . json_last_error_msg(), 'api_debug.log');
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON data']);
            exit;
        }

        $csrf_token = $data['csrf_token'] ?? '';
        if (!validateCsrfToken($csrf_token)) {
            debugLog("Invalid CSRF token for add_to_cart", 'api_debug.log');
            sendJsonResponse(403, ['status' => 'error', 'message' => 'Invalid CSRF token']);
            exit;
        }

        $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
        $quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;

        if ($product_id <= 0 || $quantity <= 0) {
            debugLog("Invalid product_id or quantity: product_id=$product_id, quantity=$quantity", 'api_debug.log');
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid product ID or quantity']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT id, name, price, quantity, image 
            FROM products 
            WHERE id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            debugLog("Product not found: product_id=$product_id", 'api_debug.log');
            sendJsonResponse(404, ['status' => 'error', 'message' => 'Product not found']);
            exit;
        }

        if ($product['quantity'] < $quantity) {
            debugLog("Insufficient stock for product_id=$product_id: available={$product['quantity']}, requested=$quantity", 'api_debug.log');
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Insufficient stock']);
            exit;
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
            if ($new_quantity > $product['quantity']) {
                debugLog("Total quantity exceeds stock for product_id=$product_id: new_quantity=$new_quantity, available={$product['quantity']}", 'api_debug.log');
                sendJsonResponse(400, ['status' => 'error', 'message' => 'Total quantity exceeds stock']);
                exit;
            }
            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => (float)$product['price'],
                'quantity' => $quantity,
                'maxQuantity' => (int)$product['quantity'],
                'image' => $product['image']
            ];
        }

        debugLog("Added to cart: product_id=$product_id, quantity=$quantity", 'api_debug.log');

        sendJsonResponse(200, [
            'status' => 'success',
            'message' => 'Product added to cart',
            'cart' => array_values($_SESSION['cart'])
        ]);
    } catch (Exception $e) {
        debugLog("Error in add_to_cart: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine(), 'api_debug.log');
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to add to cart: ' . $e->getMessage()]);
    }
    break;

case 'get_product':
    try {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('Invalid product ID');
        }
        $stmt = $pdo->prepare("SELECT p.id, p.name, p.company, p.model, p.category_id, p.price, p.quantity, p.barcode, p.image, p.description 
                               FROM products p WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            throw new Exception('Product not found');
        }
        sendJsonResponse(200, ['success' => true, 'product' => $product]);
    } catch (PDOException $e) {
        debugLog("Database error in get_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        debugLog("Error in get_product: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => $e->getMessage()]);
    }
    break;

 case 'get_categories':
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(200, ['categories' => $categories]);
    } catch (Exception $e) {
        debugLog('Error in get_categories: ' . $e->getMessage());
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to fetch categories']);
    }
    break;


 case 'get_cart':
    header('Content-Type: application/json');
    session_start();
    $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
    debugLog("get_cart: " . json_encode($cart), 'api_debug.log');
    echo json_encode(['success' => true, 'cart' => $cart]);
    exit;

case 'update_cart_item':
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
        $quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;

        if ($product_id <= 0) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        session_start();
        if (!isset($_SESSION['cart'][$product_id])) {
            sendJsonResponse(404, ['status' => 'error', 'message' => 'Product not in cart']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product && $product['quantity'] < $quantity) {
            debugLog("Low stock warning: Updated quantity $quantity for product ID $product_id (Stock: {$product['quantity']})");
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Insufficient stock']);
            exit;
        }

        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        $_SESSION['cart'][$product_id]['maxQuantity'] = $product['quantity'];
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Cart item updated', 'cart' => array_values($_SESSION['cart'])]);
    } catch (Exception $e) {
        debugLog('Error in update_cart_item: ' . $e->getMessage());
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to update cart item']);
    }
    break;

case 'remove_cart_item':
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;

        if ($product_id <= 0) {
            sendJsonResponse(400, ['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        session_start();
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            sendJsonResponse(200, ['status' => 'success', 'message' => 'Cart item removed', 'cart' => array_values($_SESSION['cart'])]);
        } else {
            sendJsonResponse(404, ['status' => 'error', 'message' => 'Product not in cart']);
        }
    } catch (Exception $e) {
        debugLog('Error in remove_cart_item: ' . $e->getMessage());
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to remove cart item']);
    }
    break;

case 'clear_cart':
    try {
        session_start();
        $_SESSION['cart'] = [];
        sendJsonResponse(200, ['status' => 'success', 'message' => 'Cart cleared', 'cart' => []]);
    } catch (Exception $e) {
        debugLog('Error in clear_cart: ' . $e->getMessage());
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to clear cart']);
    }
    break;

case 'get_active_discounts':
    try {
        $stmt = $pdo->prepare("SELECT * FROM discounts WHERE start_date <= NOW() AND end_date >= NOW()");
        $stmt->execute();
        $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(200, ['discounts' => $discounts]);
    } catch (Exception $e) {
        debugLog('Error in get_active_discounts: ' . $e->getMessage());
        sendJsonResponse(500, ['status' => 'error', 'message' => 'Failed to fetch discounts']);
    }
    break;

    case 'forgot_password':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, ['success' => false, 'message' => 'Method not allowed']);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email']) || !isset($data['csrf_token'])) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Email or CSRF token missing']);
    }
    
    $email = trim($data['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid email format']);
    }
    
    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for forgot_password: {$data['csrf_token']}", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid CSRF token']);
    }
    
    try {
        // Check rate limiting
        if (isRateLimited($email, 'pwd_reset', 3, 3600)) {
            debugLog("Rate limit exceeded for $email on forgot_password", 'api_debug.log');
            sendJsonResponse(429, ['success' => false, 'message' => 'Too many attempts. Please try again later.']);
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendJsonResponse(400, ['success' => false, 'message' => 'Email not found']);
        }
        
        // Generate OTP (6 digits)
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Store in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([password_hash($otp, PASSWORD_DEFAULT), $expires, $email]);
        
        // Send email
        if (!sendOtpEmail($email, $otp)) {
            debugLog("Failed to send OTP to $email", 'api_debug.log');
            sendJsonResponse(500, ['success' => false, 'message' => 'Failed to send OTP email']);
        }
        
        debugLog("OTP sent to $email for forgot_password", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'OTP sent to your email']);
    } catch (PDOException $e) {
        debugLog("Database error in forgot_password for $email: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error']);
    }
    break;

case 'reset_password':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, ['success' => false, 'message' => 'Method not allowed']);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email']) || !isset($data['otp']) || !isset($data['new_password']) || !isset($data['csrf_token'])) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Missing required parameters']);
    }
    
    $email = trim($data['email']);
    $otp = trim($data['otp']);
    $newPassword = $data['new_password'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid email format']);
    }
    
    if (!validateCsrfToken($data['csrf_token'])) {
        debugLog("Invalid CSRF token for reset_password: {$data['csrf_token']}", 'api_debug.log');
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid CSRF token']);
    }
    
    if (strlen($newPassword) < 8) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Password must be at least 8 characters']);
    }
    
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        sendJsonResponse(400, ['success' => false, 'message' => 'Invalid OTP format']);
    }
    
    try {
        // Verify OTP
        $stmt = $pdo->prepare("SELECT reset_token, reset_expires FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['reset_token'] || strtotime($user['reset_expires']) < time()) {
            sendJsonResponse(400, ['success' => false, 'message' => 'Invalid or expired OTP']);
        }
        
        if (!password_verify($otp, $user['reset_token'])) {
            sendJsonResponse(400, ['success' => false, 'message' => 'Invalid OTP']);
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        
        debugLog("Password reset successfully for $email", 'api_debug.log');
        sendJsonResponse(200, ['success' => true, 'message' => 'Password reset successfully']);
    } catch (PDOException $e) {
        debugLog("Database error in reset_password for $email: " . $e->getMessage(), 'api_debug.log');
        sendJsonResponse(500, ['success' => false, 'message' => 'Database error']);
    }
    break;




}
    


ob_end_flush();
?>