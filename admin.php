<?php
require_once 'config.php';
if (!isAdmin() || !isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('Location: login.php');
    exit;
}
$csrf_token = getCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
        body { font-family: 'Open Sans', Arial, sans-serif; background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .error { color: red; }
        .success { color: green; }
        .spinner-border { color: #007bff; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .navbar-brand { font-weight: bold; }
        .nav-link { cursor: pointer; }
        .section { display: none; }
        .section.active { display: block; }
        .autocomplete-suggestions {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: calc(100% - 30px);
            max-height: 200px;
            overflow-y: auto;
        }
        .autocomplete-item {
            padding: 8px;
            cursor: pointer;
        }
        .autocomplete-item:hover {
            background-color: #f0f4f8;
        }

        .list-group.position-absolute {
            max-height: 200px;
            overflow-y: auto;
        }
        .list-group-item:hover {
            background-color: #f0f4f8;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">POS System - Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" data-section="dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="sales">Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="sales-report">Sales Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="sales-forecastin">Sales Forecasting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="most-sold-products">Most Sold Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="inventory">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="stock_adjustments">Stock Adjustments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="discounts">Discounts</a>
                    </li>
                    <li class="nav-item">
                           <a class="nav-link" data-section="customers">Customers</a>
                       </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Admin Panel</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <!-- Dashboard Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Sales (Last 30 Days)</h5>
                            <p class="card-text" id="totalSales">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue (Last 30 Days)</h5>
                            <p class="card-text" id="totalRevenue">$0.00</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Products Sold (Last 30 Days)</h5>
                            <p class="card-text" id="totalProductsSold">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Trend Chart -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Daily Sales Trend (Last 30 Days)</h2>
                    <canvas id="salesTrendChart" height="100"></canvas>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="card-title">Low Stock Alerts</h2>
                        <button id="refreshLowStockBtn" class="btn btn-primary btn-sm">Refresh</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Barcode</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Section -->
        <div id="sales" class="section">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Create New Sale</h2>
                    <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="clientSearchBar" class="form-label">Search Client (by Username)</label>
                                <div class="input-group position-relative">
                                    <input type="text" id="clientSearchBar" class="form-control" placeholder="Type client name..." autocomplete="off">
                                    <div id="clientSuggestions" class="autocomplete-suggestions"></div>
                                </div>
                            </div>
                            <div id="selectedClient" class="mb-3"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="productSearchBar" class="form-label">Search Product (by Name or Barcode)</label>
                                <div class="input-group position-relative">
                                    <input type="text" id="productSearchBar" class="form-control" placeholder="Type product name/barcode..." autocomplete="off">
                                    <div id="productSuggestions" class="autocomplete-suggestions"></div>
                                </div>
                            </div>
                            <div id="productResult" class="mb-3">
                                <div id="selectedProduct"></div>
                                <div class="d-flex align-items-center">
                                    <label for="quantity" class="me-2">Quantity:</label>
                                    <input type="number" id="quantity" class="form-control d-inline-block" style="width: 100px;" min="1" value="1">
                                    <button id="addToCartBtn" class="btn btn-success ms-2">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cartTable"></tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <h4>Subtotal: $<span id="subtotal">0.00</span></h4>
                        <h4>Discount: $<span id="discount">0.00</span></h4>
                        <h4>Tax (5%): $<span id="tax">0.00</span></h4>
                        <h4>Total: $<span id="cartTotal">0.00</span></h4>
                        <button id="checkoutBtn" class="btn btn-primary mt-3">Checkout</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Report Section -->
        <div id="sales-report" class="section">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Sales Report</h2>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="salesStartDate" class="form-label">Start Date:</label>
                            <input type="date" id="salesStartDate" class="form-control" value="<?php echo date('Y-m-d', strtotime('-90 days')); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="salesEndDate" class="form-label">End Date:</label>
                            <input type="date" id="salesEndDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="salesSearchQuery" class="form-label">Search Client/Product:</label>
                            <input type="text" id="salesSearchQuery" class="form-control" placeholder="Client username, product name, or barcode">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="d-flex">
                                <button id="fetchSalesReportBtn" class="btn btn-primary me-2">Fetch Report</button>
                                <button id="exportSalesCsvBtn" class="btn btn-secondary me-2">Export CSV</button>
                                <button id="exportSalesPdfBtn" class="btn btn-info">Export PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Detailed Sales Report</h2>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Client Name</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Category</th>
                                    <th>Barcode</th>
                                    <th>Discount Applied</th>
                                    <th>Discount Amount</th>
                                    <th>Sale Date</th>
                                </tr>
                            </thead>
                            <tbody id="salesReportTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



                    <div id="sales-forecasting" class="section">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Sales Forecasting</h2>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="forecastStartDate" class="form-label">Start Date:</label>
                                <input type="date" id="forecastStartDate" class="form-control" value="<?php echo date('Y-m-d', strtotime('-90 days')); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="forecastEndDate" class="form-label">End Date:</label>
                                <input type="date" id="forecastEndDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="forecastDays" class="form-label">Days to Forecast:</label>
                                <input type="number" id="forecastDays" class="form-control" value="30" min="1">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button id="fetchForecastBtn" class="btn btn-primary">Generate Forecast</button>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height:400px; width:100%">
                            <canvas id="forecastChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>




        <!-- Most Sold Products Section -->
        <div id="most-sold-products" class="section">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Most Sold Products</h2>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="mostSoldStartDate" class="form-label">Start Date:</label>
                            <input type="date" id="mostSoldStartDate" class="form-control" value="<?php echo date('Y-m-d', strtotime('-90 days')); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="mostSoldEndDate" class="form-label">End Date:</label>
                            <input type="date" id="mostSoldEndDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="d-flex">
                                <button id="fetchMostSoldBtn" class="btn btn-primary me-2">Fetch Report</button>
                                <button id="exportMostSoldCsvBtn" class="btn btn-secondary">Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Most Sold Products Report</h2>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th>Total Quantity Sold</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="mostSoldTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Section -->
        <div id="inventory" class="section">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Inventory Management</h2>
                    <button id="addProductBtn" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#productModal">Add New Product</button>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Barcode</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        


<!-- Discounts Section -->
<div id="discounts" class="section" style="display: none;">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Discount & Promotion Management</h2>
            <button id="addDiscountBtn" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#discountModal">Add New Discount</button>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Purchase</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="discountTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">Add Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <form id="discountForm">
        <input type="hidden" id="discountId">
        <div class="mb-3">
            <label for="discountName" class="form-label">Discount Name</label>
            <input type="text" class="form-control" id="discountName" required>
        </div>
        <div class="mb-3">
            <label for="discountType" class="form-label">Discount Type</label>
            <select class="form-control" id="discountType" required>
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount ($)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="discountValue" class="form-label">Discount Value</label>
            <input type="number" step="0.01" class="form-control" id="discountValue" required>
        </div>
        <div class="mb-3">
            <label for="discountMinPurchase" class="form-label">Minimum Purchase Amount (Optional)</label>
            <input type="number" step="0.01" class="form-control" id="discountMinPurchase">
        </div>
        <div class="mb-3">
            <label for="discountProduct" class="form-label">Specific Product (Optional)</label>
            <select class="form-control" id="discountProduct">
                <option value="">None</option>
                <?php
                $stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$product['id']}\">" . htmlspecialchars($product['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="discountCategory" class="form-label">Specific Category (Optional)</label>
            <select class="form-control" id="discountCategory">
                <option value="">None</option>
                <?php
                $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="discountStartDate" class="form-label">Start Date</label>
            <input type="datetime-local" class="form-control" id="discountStartDate" required>
        </div>
        <div class="mb-3">
            <label for="discountEndDate" class="form-label">End Date</label>
            <input type="datetime-local" class="form-control" id="discountEndDate" required>
        </div>
        <div class="mb-3">
            <label for="discountIsActive" class="form-label">Active</label>
            <input type="checkbox" class="form-check-input" id="discountIsActive" checked>
        </div>
        <button type="submit" class="btn btn-primary">Save Discount</button>
    </form>
</div>
        </div>
    </div>
</div>

        <!-- Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="productForm">
                            <input type="hidden" id="productId">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Name</label>
                                <input type="text" class="form-control" id="productName" required>
                            </div>
                            <div class="mb-3">
                                <label for="productCategory" class="form-label">Category</label>
                                <select class="form-control" id="productCategory" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT id, name FROM categories");
                                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="productPrice" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="productPrice" required>
                            </div>
                            <div class="mb-3">
                                <label for="productQuantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="productQuantity" required>
                            </div>
                            <div class="mb-3">
                                <label for="productBarcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="productBarcode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Section -->
<div id="customers" class="section" style="display: none;">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Customer Management</h2>
            <div class="mb-3">
                <input type="text" class="form-control" id="customerSearch" placeholder="Search by username or email...">
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer History Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Customer Purchase History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="historyTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<<!-- Stock Adjustments Section -->
<!-- Stock Adjustments Section -->
<div id="stock_adjustments" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Stock Adjustments</h2>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3 position-relative">
                    <label for="adjustmentSearch" class="form-label">Search Product:</label>
                    <input type="text" class="form-control" id="adjustmentSearch" placeholder="Search by product...">
                    <ul id="suggestionList" class="list-group position-absolute w-100" style="display: none; z-index: 1000;"></ul>
                </div>
                <div class="col-md-3">
                    <label for="adjustmentStartDate" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" id="adjustmentStartDate">
                </div>
                <div class="col-md-3">
                    <label for="adjustmentEndDate" class="form-label">End Date:</label>
                    <input type="date" class="form-control" id="adjustmentEndDate">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="exportAdjustmentsCsvBtn" class="btn btn-secondary w-100">Export CSV</button>
                </div>
            </div>

            <!-- Stock Adjustments Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Quantity Before</th>
                            <th>Adjustment</th>
                            <th>Quantity After</th>
                            <th>Reason</th>
                            <th>Notes</th>
                            <th>Adjusted By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adjustmentTable"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Stock Adjustment Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title" id="adjustmentFormTitle">Add New Stock Adjustment</h2>
            <form id="adjustmentForm">
                <div class="row mb-3">
                    <div class="col-md-3 position-relative">
                        <label for="adjustmentProduct" class="form-label">Product:</label>
                        <input type="text" class="form-control" id="adjustmentProduct" placeholder="Search product..." required>
                        <input type="hidden" id="adjustmentProductId">
                        <ul id="adjustmentProductSuggestions" class="list-group position-absolute w-100" style="display: none; z-index: 1000;"></ul>
                    </div>
                    <div class="col-md-2">
                        <label for="adjustmentValue" class="form-label">Adjustment Value:</label>
                        <input type="number" class="form-control" id="adjustmentValue" placeholder="e.g., +5 or -3" required>
                    </div>
                    <div class="col-md-2">
                        <label for="adjustmentReason" class="form-label">Reason:</label>
                        <select class="form-control" id="adjustmentReason" required>
                            <option value="">Select Reason</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, name FROM adjustment_reasons ORDER BY name ASC");
                            while ($reason = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value=\"{$reason['id']}\">" . htmlspecialchars($reason['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="adjustmentNotes" class="form-label">Notes (Optional):</label>
                        <input type="text" class="form-control" id="adjustmentNotes" placeholder="Additional notes...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" id="submitAdjustmentBtn" class="btn btn-primary me-2">Add Adjustment</button>
                        <button type="button" id="cancelEditBtn" class="btn btn-secondary" style="display: none;">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation Toggle
        const navLinks = document.querySelectorAll('.nav-link[data-section]');
        const sections = document.querySelectorAll('.section');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                sections.forEach(s => s.classList.remove('active'));
                const sectionId = link.dataset.section;
                const section = document.getElementById(sectionId);
                if (section) {
                    section.classList.add('active');
                    // Initialize section-specific data when shown
                    if (sectionId === 'customers') {
                        console.log('Calling fetchCustomers');
                        fetchCustomers();
                    }
                    if (sectionId === 'stock_adjustments') {
                        console.log('Calling fetchStockAdjustments');
                        fetchStockAdjustments();
                    }
                    if (sectionId === 'dashboard') {
                        fetchDashboardSummary();
                        fetchDailySalesTrend();
                        fetchLowStockProducts();
                    } else if (sectionId === 'inventory') {
                        fetchInventory();
                    } else if (sectionId === 'discounts') {
                        fetchDiscounts();
                    }
                }
            });
        });

        function logError(message) {
            console.error(message);
            fetch('api.php?action=log_error', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
        }

        // Dashboard Functions
        function fetchDashboardSummary() {
            fetch('api.php?action=get_dashboard_summary')
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Dashboard Summary:', data);
                    document.getElementById('totalSales').textContent = data.total_sales || 0;
                    document.getElementById('totalRevenue').textContent = `$${parseFloat(data.total_revenue || 0).toFixed(2)}`;
                    document.getElementById('totalProductsSold').textContent = data.total_products_sold || 0;
                })
                .catch(error => {
                    logError('Error fetching dashboard summary: ' + error.message);
                    console.error('Error fetching dashboard summary:', error);
                });
        }

        let salesTrendChart = null;
        function fetchDailySalesTrend() {
            fetch('api.php?action=get_daily_sales_trend')
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Daily Sales Trend:', data);
                    const labels = data.map(item => item.sale_day);
                    const revenues = data.map(item => parseFloat(item.daily_revenue || 0));
                    const ctx = document.getElementById('salesTrendChart').getContext('2d');

                    if (salesTrendChart) {
                        salesTrendChart.destroy();
                    }

                    if (labels.length === 0 || revenues.length === 0) {
                        console.warn('No data to display in sales trend chart.');
                        return;
                    }

                    salesTrendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Daily Revenue ($)',
                                data: revenues,
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Revenue ($)' } },
                                x: { title: { display: true, text: 'Date' } }
                            }
                        }
                    });
                })
                .catch(error => {
                    logError('Error fetching daily sales trend: ' + error.message);
                    console.error('Error fetching daily sales trend:', error);
                });
        }

        function fetchLowStockProducts() {
            fetch('api.php?action=get_low_stock_products')
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Low Stock Products:', data);
                    const lowStockTable = document.getElementById('lowStockTable');
                    lowStockTable.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.name}</td>
                                <td>${product.category_name || 'N/A'}</td>
                                <td>${product.quantity}</td>
                                <td>$${parseFloat(product.price).toFixed(2)}</td>
                                <td>${product.barcode}</td>
                            `;
                            lowStockTable.appendChild(row);
                        });
                    } else {
                        lowStockTable.innerHTML = '<tr><td colspan="5" class="text-center">No low stock products found.</td></tr>';
                    }
                })
                .catch(error => {
                    logError('Error fetching low stock products: ' + error.message);
                    console.error('Error fetching low stock products:', error);
                });
        }

        document.getElementById('refreshLowStockBtn').addEventListener('click', fetchLowStockProducts);

        // Sales Functions
        let cart = [];
        let clientId = null;
        let selectedProduct = null;
        const cartTable = document.getElementById('cartTable');
        const subtotalElement = document.getElementById('subtotal');
        const taxElement = document.getElementById('tax');
        const cartTotalElement = document.getElementById('cartTotal');
        const TAX_RATE = 0.05;
        const clientSearchBar = document.getElementById('clientSearchBar');
        const clientSuggestions = document.getElementById('clientSuggestions');
        const selectedClientDiv = document.getElementById('selectedClient');
        const productSearchBar = document.getElementById('productSearchBar');
        const productSuggestions = document.getElementById('productSuggestions');
        const selectedProductDiv = document.getElementById('selectedProduct');
        const quantityInput = document.getElementById('quantity');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const checkoutBtn = document.getElementById('checkoutBtn');
        let csrfToken = document.getElementById('csrf_token').value;

        function updateCartTable() {
    fetch('api.php?action=get_active_discounts')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(discounts => {
            cartTable.innerHTML = '';
            let subtotal = 0;
            cart.forEach((item, index) => {
                let discount = discounts.find(d => 
                    d.product_id == item.id || 
                    (d.category_id && !d.product_id && item.category_id == d.category_id) || 
                    (!d.product_id && !d.category_id)
                );
                let discountAmount = 0;
                let discountedPrice = item.price;
                if (discount) {
                    if (discount.type === 'percentage') {
                        discountAmount = (item.price * discount.value) / 100;
                    } else {
                        discountAmount = parseFloat(discount.value);
                    }
                    discountAmount = Math.min(discountAmount, item.price);
                    discountedPrice = item.price - discountAmount;
                }
                const itemTotal = item.quantity * discountedPrice;
                subtotal += itemTotal;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name} ${discount ? `<br><small class="text-success">${discount.name} (${discount.type === 'percentage' ? discount.value + '%' : '$' + discount.value})</small>` : ''}</td>
                    <td>$${discountedPrice.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="adjustQuantity(${index}, -1)">-</button>
                        ${item.quantity}
                        <button class="btn btn-sm btn-secondary" onclick="adjustQuantity(${index}, 1)">+</button>
                    </td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">Remove</button></td>
                `;
                cartTable.appendChild(row);
            });
            const tax = subtotal * TAX_RATE;
            const total = subtotal + tax;
            subtotalElement.textContent = subtotal.toFixed(2);
            taxElement.textContent = tax.toFixed(2);
            cartTotalElement.textContent = total.toFixed(2);
        })
        .catch(error => {
            logError('Error fetching discounts for cart: ' + error.message);
            console.error('Error fetching discounts for cart:', error);
            // Fallback to original pricing
            cartTable.innerHTML = '';
            let subtotal = 0;
            cart.forEach((item, index) => {
                const itemTotal = item.quantity * item.price;
                subtotal += itemTotal;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="adjustQuantity(${index}, -1)">-</button>
                        ${item.quantity}
                        <button class="btn btn-sm btn-secondary" onclick="adjustQuantity(${index}, 1)">+</button>
                    </td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">Remove</button></td>
                `;
                cartTable.appendChild(row);
            });
            const tax = subtotal * TAX_RATE;
            const total = subtotal + tax;
            subtotalElement.textContent = subtotal.toFixed(2);
            taxElement.textContent = tax.toFixed(2);
            cartTotalElement.textContent = total.toFixed(2);
        });
}

        function adjustQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            const maxQuantity = item.maxQuantity;
            if (newQuantity < 1) {
                removeFromCart(index);
                return;
            }
            if (newQuantity > maxQuantity) {
                alert(`Only ${maxQuantity} units of ${item.name} are available.`);
                return;
            }
            item.quantity = newQuantity;
            updateCartTable();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartTable();
        }

        let clientTimeout;
        clientSearchBar.addEventListener('input', () => {
            clearTimeout(clientTimeout);
            const query = clientSearchBar.value.trim();
            if (query.length < 2) {
                clientSuggestions.innerHTML = '';
                clientSuggestions.style.display = 'none';
                return;
            }
            clientTimeout = setTimeout(() => {
                fetch(`api.php?action=search_clients&username=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Client Search:', data);
                        clientSuggestions.innerHTML = '';
                        const clients = Array.isArray(data) ? data : (data.clients || []);
                        if (clients.length > 0) {
                            clients.forEach(client => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = client.username;
                                div.addEventListener('click', () => {
                                    clientId = client.id;
                                    clientSearchBar.value = client.username;
                                    selectedClientDiv.textContent = `Selected Client: ${client.username}`;
                                    clientSuggestions.innerHTML = '';
                                    clientSuggestions.style.display = 'none';
                                });
                                clientSuggestions.appendChild(div);
                            });
                            clientSuggestions.style.display = 'block';
                        } else {
                            clientSuggestions.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        logError('Error searching clients: ' + error.message);
                        console.error('Error searching clients:', error);
                        clientSuggestions.style.display = 'none';
                    });
            }, 300);
        });

        let productTimeout;
        productSearchBar.addEventListener('input', () => {
            clearTimeout(productTimeout);
            const query = productSearchBar.value.trim();
            if (query.length < 2) {
                productSuggestions.innerHTML = '';
                productSuggestions.style.display = 'none';
                selectedProductDiv.innerHTML = '';
                selectedProduct = null;
                return;
            }
            productTimeout = setTimeout(() => {
                fetch(`api.php?action=search_products&query=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Product Search:', data);
                        productSuggestions.innerHTML = '';
                        const products = Array.isArray(data) ? data : (data.products || []);
                        if (products.length > 0) {
                            products.forEach(product => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = `${product.name} (Barcode: ${product.barcode}) - $${product.price} (Qty: ${product.quantity})`;
                                div.addEventListener('click', () => {
                                    selectedProduct = {
                                        id: product.id,
                                        name: product.name,
                                        price: parseFloat(product.price),
                                        maxQuantity: parseInt(product.quantity)
                                    };
                                    productSearchBar.value = product.name;
                                    selectedProductDiv.textContent = `Selected Product: ${product.name} - $${product.price} (Available: ${product.quantity})`;
                                    productSuggestions.innerHTML = '';
                                    productSuggestions.style.display = 'none';
                                    quantityInput.focus();
                                });
                                productSuggestions.appendChild(div);
                            });
                            productSuggestions.style.display = 'block';
                        } else {
                            productSuggestions.style.display = 'none';
                            selectedProductDiv.textContent = 'No products found.';
                            selectedProduct = null;
                        }
                    })
                    .catch(error => {
                        logError('Error searching products: ' + error.message);
                        console.error('Error searching products:', error);
                        productSuggestions.style.display = 'none';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!clientSearchBar.contains(e.target) && !clientSuggestions.contains(e.target)) {
                clientSuggestions.style.display = 'none';
            }
            if (!productSearchBar.contains(e.target) && !productSuggestions.contains(e.target)) {
                productSuggestions.style.display = 'none';
            }
        });

        addToCartBtn.addEventListener('click', () => {
            if (!selectedProduct) {
                alert('Please select a product.');
                return;
            }
            const quantity = parseInt(quantityInput.value);
            if (quantity <= 0) {
                alert('Please enter a valid quantity.');
                return;
            }
            const maxQuantity = selectedProduct.maxQuantity;
            if (quantity > maxQuantity) {
                alert(`Only ${maxQuantity} units of ${selectedProduct.name} are available.`);
                return;
            }
            const existingItem = cart.find(item => item.id === selectedProduct.id);
            if (existingItem) {
                const newQuantity = existingItem.quantity + quantity;
                if (newQuantity > maxQuantity) {
                    alert(`Cannot add ${newQuantity} units. Only ${maxQuantity} units are available.`);
                    return;
                }
                existingItem.quantity = newQuantity;
            } else {
                cart.push({
                    id: selectedProduct.id,
                    name: selectedProduct.name,
                    price: selectedProduct.price,
                    quantity: quantity,
                    maxQuantity: maxQuantity
                });
            }
            updateCartTable();
            quantityInput.value = 1;
            productSearchBar.value = '';
            selectedProductDiv.textContent = '';
            selectedProduct = null;
        });

        checkoutBtn.addEventListener('click', () => {
            if (!clientId) {
                alert('Please select a client.');
                return;
            }
            if (cart.length === 0) {
                alert('Cart is empty. Please add products to proceed.');
                return;
            }
            const payload = {
                action: 'checkout',
                client_id: clientId,
                items: cart.map(item => ({ id: item.id, quantity: item.quantity })),
                csrf_token: csrfToken
            };
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Sale processed successfully! Sale ID: ' + data.sale_id);
                        const receiptPayload = {
                            action: 'generate_receipt',
                            sale_id: data.sale_id,
                            csrf_token: csrfToken
                        };
                        fetch('api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(receiptPayload)
                        })
                            .then(response => {
                                const contentType = response.headers.get('Content-Type');
                                if (!response.ok) {
                                    if (contentType.includes('application/json')) {
                                        return response.json().then(err => {
                                            console.error('Receipt Generation Error (JSON Response):', err);
                                            throw new Error(err.message || 'Failed to generate receipt');
                                        });
                                    } else {
                                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                    }
                                }
                                if (!contentType.includes('application/pdf')) {
                                    return response.text().then(text => {
                                        console.error('Unexpected response:', text);
                                        throw new Error('Expected PDF but received: ' + contentType);
                                    });
                                }
                                return response.blob();
                            })
                            .then(blob => {
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = `Receipt_${data.sale_id}.pdf`;
                                a.click();
                                window.URL.revokeObjectURL(url);
                            })
                            .catch(error => {
                                logError('Error generating receipt: ' + error.message);
                                console.error('Error generating receipt:', error);
                                alert('Sale processed, but error generating receipt: ' + error.message);
                            })
                            .finally(() => {
                                fetchDashboardSummary();
                                fetchDailySalesTrend();
                                fetchLowStockProducts();
                                cart = [];
                                updateCartTable();
                                clientSearchBar.value = '';
                                selectedClientDiv.textContent = '';
                                clientId = null;
                            });
                    } else {
                        alert('Error processing sale: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    logError('Error during checkout: ' + error.message);
                    console.error('Error during checkout:', error);
                    alert('Error during checkout: ' + error.message);
                });
        });

        // Sales Report Functions
        const salesStartDateInput = document.getElementById('salesStartDate');
        const salesEndDateInput = document.getElementById('salesEndDate');
        const salesSearchQueryInput = document.getElementById('salesSearchQuery');
        const fetchSalesReportBtn = document.getElementById('fetchSalesReportBtn');
        const exportSalesCsvBtn = document.getElementById('exportSalesCsvBtn');
        const exportSalesPdfBtn = document.getElementById('exportSalesPdfBtn');
        const salesReportTable = document.getElementById('salesReportTable');

        function fetchSalesReport() {
    const startDate = salesStartDateInput.value;
    const endDate = salesEndDateInput.value;
    const searchQuery = salesSearchQueryInput.value.trim();
    if (!startDate || !endDate) {
        alert('Please select both start and end dates.');
        return;
    }
    let url = `api.php?action=get_detailed_sales_report&start_date=${startDate}&end_date=${endDate}`;
    if (searchQuery) url += `&search_query=${encodeURIComponent(searchQuery)}`;
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('Sales Report:', data);
            salesReportTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(sale => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${sale.sale_id}</td>
                        <td>${sale.client_name || 'Unknown'}</td>
                        <td>${sale.product_name}</td>
                        <td>$${parseFloat(sale.price).toFixed(2)}</td>
                        <td>${sale.quantity}</td>
                        <td>$${parseFloat(sale.total).toFixed(2)}</td>
                        <td>${sale.category_name || 'N/A'}</td>
                        <td>${sale.barcode}</td>
                        <td>${sale.discount_name || 'None'}</td>
                        <td>$${parseFloat(sale.discount_amount || 0).toFixed(2)}</td>
                        <td>${sale.sale_date}</td>
                    `;
                    salesReportTable.appendChild(row);
                });
            } else {
                salesReportTable.innerHTML = '<tr><td colspan="11" class="text-center">No sales found for the selected criteria.</td></tr>';
            }
        })
        .catch(error => {
            logError('Error fetching sales report: ' + error.message);
            console.error('Error fetching sales report:', error);
            alert('Error fetching sales report: ' + error.message);
        });
}

        function exportSalesCsv() {
            const startDate = salesStartDateInput.value;
            const endDate = salesEndDateInput.value;
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            let url = `api.php?action=export_sales_report_csv&start_date=${startDate}&end_date=${endDate}`;
            const searchQuery = salesSearchQueryInput.value.trim();
            if (searchQuery) url += `&search_query=${encodeURIComponent(searchQuery)}`;
            window.location.href = url;
        }

        function exportSalesPdf() {
            const startDate = salesStartDateInput.value;
            const endDate = salesEndDateInput.value;
            const searchQuery = salesSearchQueryInput.value.trim();
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            const payload = {
                action: 'generate_sales_report_pdf',
                start_date: startDate,
                end_date: endDate,
                search_query: searchQuery || '',
                csrf_token: csrfToken
            };
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `SalesReport_${startDate}_to_${endDate}.pdf`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    logError('Error generating PDF: ' + error.message);
                    console.error('Error generating PDF:', error);
                    alert('Error generating PDF: ' + error.message);
                });
        }

        fetchSalesReportBtn.addEventListener('click', fetchSalesReport);
        exportSalesCsvBtn.addEventListener('click', exportSalesCsv);
        exportSalesPdfBtn.addEventListener('click', exportSalesPdf);

        // Most Sold Products Functions
        const mostSoldStartDateInput = document.getElementById('mostSoldStartDate');
        const mostSoldEndDateInput = document.getElementById('mostSoldEndDate');
        const fetchMostSoldBtn = document.getElementById('fetchMostSoldBtn');
        const exportMostSoldCsvBtn = document.getElementById('exportMostSoldCsvBtn');
        const mostSoldTable = document.getElementById('mostSoldTable');

        function fetchMostSoldProducts() {
            const startDate = mostSoldStartDateInput.value;
            const endDate = mostSoldEndDateInput.value;
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            fetch(`api.php?action=get_most_sold_products&start_date=${startDate}&end_date=${endDate}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Most Sold Products:', data);
                    mostSoldTable.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.id}</td>
                                <td>${product.name}</td>
                                <td>$${parseFloat(product.price).toFixed(2)}</td>
                                <td>${product.barcode}</td>
                                <td>${product.category_name || 'N/A'}</td>
                                <td>${product.total_quantity_sold}</td>
                                <td>$${parseFloat(product.total_revenue).toFixed(2)}</td>
                            `;
                            mostSoldTable.appendChild(row);
                        });
                    } else {
                        mostSoldTable.innerHTML = '<tr><td colspan="7" class="text-center">No data found for the selected date range.</td></tr>';
                    }
                })
                .catch(error => {
                    logError('Error fetching most sold products: ' + error.message);
                    console.error('Error fetching most sold products:', error);
                    alert('Error fetching most sold products: ' + error.message);
                });
        }

        function exportMostSoldCsv() {
            const startDate = mostSoldStartDateInput.value;
            const endDate = mostSoldEndDateInput.value;
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            window.location.href = `api.php?action=export_most_sold_products_csv&start_date=${startDate}&end_date=${endDate}`;
        }

        fetchMostSoldBtn.addEventListener('click', fetchMostSoldProducts);
        exportMostSoldCsvBtn.addEventListener('click', exportMostSoldCsv);

        // Inventory Functions
        const inventoryTable = document.getElementById('inventoryTable');
        const productForm = document.getElementById('productForm');
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        const productModalLabel = document.getElementById('productModalLabel');
        const productIdInput = document.getElementById('productId');
        const productNameInput = document.getElementById('productName');
        const productCategoryInput = document.getElementById('productCategory');
        const productPriceInput = document.getElementById('productPrice');
        const productQuantityInput = document.getElementById('productQuantity');
        const productBarcodeInput = document.getElementById('productBarcode');
        const suggestionList = document.getElementById('suggestionList');

adjustmentSearch.addEventListener('input', async () => {
    const term = adjustmentSearch.value;
    suggestionList.style.display = term.length >= 2 ? 'block' : 'none';
    if (term.length < 2) {
        suggestionList.innerHTML = '';
        return;
    }
    try {
        const response = await fetch(`api.php?action=get_product_suggestions&term=${encodeURIComponent(term)}`);
        if (!response.ok) throw new Error('Network error');
        const products = await response.json();
        suggestionList.innerHTML = products.map(p => `<li class="list-group-item suggestion-item">${p}</li>`).join('');
        document.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                adjustmentSearch.value = item.textContent;
                suggestionList.style.display = 'none';
                fetchStockAdjustments();
            });
        });
    } catch (error) {
        console.error('Autocomplete error:', error);
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!adjustmentSearch.contains(e.target) && !suggestionList.contains(e.target)) {
        suggestionList.style.display = 'none';
    }
});


async function fetchForecast() {
    try {
        const startDate = document.getElementById('forecastStartDate').value;
        const endDate = document.getElementById('forecastEndDate').value;
        const days = document.getElementById('forecastDays').value;
        
        if (!startDate || !endDate || !days) {
            throw new Error('Please fill all required fields');
        }

        // Show loading state
        const forecastBtn = document.getElementById('fetchForecastBtn');
        forecastBtn.disabled = true;
        forecastBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';

        const response = await fetch(`api.php?action=get_sales_forecast&start_date=${startDate}&end_date=${endDate}&days=${days}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.status !== 'success' || !data.forecasts || data.forecasts.length === 0) {
            throw new Error(data.message || 'No forecast data available');
        }

        // Prepare chart data
        const labels = data.forecasts.map(f => f.product_name);
        const historicalData = data.forecasts.map(f => f.historical_sales);
        const predictedData = data.forecasts.map(f => f.predicted_sales);

        // Get chart canvas
        const canvas = document.getElementById('forecastChart');
        if (!canvas) {
            throw new Error('Chart canvas not found');
        }
        
        const ctx = canvas.getContext('2d');

        // Destroy existing chart if it exists
        if (window.forecastChartInstance) {
            window.forecastChartInstance.destroy();
        }

        // Create new chart
        window.forecastChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Historical Sales (Last Period)',
                        data: historicalData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Predicted Sales (Next Period)',
                        data: predictedData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity Sold'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Products'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: `Sales Forecast - Historical vs Predicted (${startDate} to ${endDate})`
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Forecast error:', error);
        alert('Error generating forecast: ' + error.message);
    } finally {
        // Reset button state
        const forecastBtn = document.getElementById('fetchForecastBtn');
        if (forecastBtn) {
            forecastBtn.disabled = false;
            forecastBtn.textContent = 'Generate Forecast';
        }
    }
}



        function fetchInventory() {
            fetch('api.php?action=get_inventory')
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Inventory Data:', data);
                    inventoryTable.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.id}</td>
                                <td>${product.name}</td>
                                <td>${product.category_name || 'N/A'}</td>
                                <td>$${parseFloat(product.price).toFixed(2)}</td>
                                <td>${product.quantity}</td>
                                <td>${product.barcode}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-2" onclick="editProduct(${product.id}, '${product.name}', ${product.category_id}, ${product.price}, ${product.quantity}, '${product.barcode}')">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                                </td>
                            `;
                            inventoryTable.appendChild(row);
                        });
                    } else {
                        inventoryTable.innerHTML = '<tr><td colspan="7" class="text-center">No products found.</td></tr>';
                    }
                })
                .catch(error => {
                    logError('Error fetching inventory: ' + error.message);
                    console.error('Error fetching inventory:', error);
                    alert('Error fetching inventory: ' + error.message);
                });
        }

        function editProduct(id, name, categoryId, price, quantity, barcode) {
            productModalLabel.textContent = 'Edit Product';
            productIdInput.value = id;
            productNameInput.value = name;
            productCategoryInput.value = categoryId;
            productPriceInput.value = price;
            productQuantityInput.value = quantity;
            productBarcodeInput.value = barcode;
            productModal.show();
        }

        function deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product?')) return;
            const payload = {
                action: 'delete_product',
                id: id,
                csrf_token: csrfToken
            };
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Product deleted successfully.');
                        fetchInventory();
                    } else {
                        alert('Error deleting product: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    logError('Error deleting product: ' + error.message);
                    console.error('Error deleting product:', error);
                    alert('Error deleting product: ' + error.message);
                });
        }

        productForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = productIdInput.value;
            const action = id ? 'update_product' : 'add_product';
            const payload = {
                action: action,
                id: id || undefined,
                name: productNameInput.value,
                category_id: productCategoryInput.value,
                price: parseFloat(productPriceInput.value),
                quantity: parseInt(productQuantityInput.value),
                barcode: productBarcodeInput.value,
                csrf_token: csrfToken
            };
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(id ? 'Product updated successfully.' : 'Product added successfully.');
                        productModal.hide();
                        productForm.reset();
                        productIdInput.value = '';
                        productModalLabel.textContent = 'Add Product';
                        fetchInventory();
                        fetchLowStockProducts(); // Refresh low stock alerts
                    } else {
                        alert('Error saving product: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    logError('Error saving product: ' + error.message);
                    console.error('Error saving product:', error);
                    alert('Error saving product: ' + error.message);
                });
        });

        document.getElementById('addProductBtn').addEventListener('click', () => {
            productModalLabel.textContent = 'Add Product';
            productForm.reset();
            productIdInput.value = '';
        });

        // Discount Functions
const discountTable = document.getElementById('discountTable');
const discountForm = document.getElementById('discountForm');
const discountModal = new bootstrap.Modal(document.getElementById('discountModal'));
const discountModalLabel = document.getElementById('discountModalLabel');
const discountIdInput = document.getElementById('discountId');
const discountNameInput = document.getElementById('discountName');
const discountTypeInput = document.getElementById('discountType');
const discountValueInput = document.getElementById('discountValue');
const discountProductInput = document.getElementById('discountProduct');
const discountCategoryInput = document.getElementById('discountCategory');
const discountStartDateInput = document.getElementById('discountStartDate');
const discountEndDateInput = document.getElementById('discountEndDate');
const discountMinPurchaseInput = document.getElementById('discountMinPurchase');
const discountIsActiveInput = document.getElementById('discountIsActive');
const customerTable = document.getElementById('customerTable');
const customerSearch = document.getElementById('customerSearch');
const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
const customerModalLabel = document.getElementById('customerModalLabel');
const historyTable = document.getElementById('historyTable');




$(document).ready(function() {
    $("#adjustmentSearch").autocomplete({
        source: function(request, response) {
            fetch('api.php?action=get_product_suggestions&term=' + encodeURIComponent(request.term))
                .then(res => res.json())
                .then(data => response(data))
                .catch(err => console.error('Autocomplete error:', err));
        },
        minLength: 2,
        select: function(event, ui) {
            adjustmentSearch.value = ui.item.value;
            fetchStockAdjustments();
        }
    });
});

function fetchCustomers(search = '') {
    const url = search ? `api.php?action=get_all_customers&search=${encodeURIComponent(search)}` : 'api.php?action=get_all_customers';
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('Customers Data:', data);
            if (data.status === 'error') {
                console.error('API Error:', data.message);
                alert(`Error fetching customers: ${data.message}`);
                customerTable.innerHTML = '<tr><td colspan="8" class="text-center">Failed to load customers: ' + data.message + '</td></tr>';
                return;
            }
            customerTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(customer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${customer.id}</td>
                        <td>${customer.username}</td>
                        <td>${customer.email}</td>
                        <td>${customer.first_name || ''} ${customer.last_name || ''}</td>
                        <td>${customer.phone || 'N/A'}</td>
                        <td>${customer.address || 'N/A'}</td>
                        <td>${customer.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewCustomerHistory(${customer.id}, '${customer.username}')">View History</button>
                        </td>
                    `;
                    customerTable.appendChild(row);
                });
            } else {
                customerTable.innerHTML = '<tr><td colspan="8" class="text-center">No customers found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching customers:', error);
            alert('Error fetching customers: ' + error.message);
            customerTable.innerHTML = '<tr><td colspan="8" class="text-center">Failed to load customers: ' + error.message + '</td></tr>';
        });
}

function viewCustomerHistory(id, username) {
    customerModalLabel.textContent = `Purchase History for ${username}`;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_customer_history', customer_id: id, csrf_token: document.querySelector('meta[name="csrf-token"]').content })
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('History Data:', data);
            if (data.status === 'error') {
                console.error('API Error:', data.message);
                alert(`Error fetching history: ${data.message}`);
                historyTable.innerHTML = '<tr><td colspan="4" class="text-center">Failed to load history: ' + data.message + '</td></tr>';
                return;
            }
            historyTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(sale => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${sale.sale_id}</td>
                        <td>${sale.sale_date}</td>
                        <td>$${parseFloat(sale.total_amount).toFixed(2)}</td>
                        <td>${sale.status}</td>
                    `;
                    historyTable.appendChild(row);
                });
            } else {
                historyTable.innerHTML = '<tr><td colspan="4" class="text-center">No purchase history found.</td></tr>';
            }
            customerModal.show();
        })
        .catch(error => {
            console.error('Error fetching history:', error);
            alert('Error fetching history: ' + error.message);
            historyTable.innerHTML = '<tr><td colspan="4" class="text-center">Failed to load history: ' + error.message + '</td></tr>';
        });
}

customerSearch.addEventListener('input', () => {
    fetchCustomers(customerSearch.value);
});

function fetchDiscounts(attempts = 5, delay = 100) {
    const discountTable = document.getElementById('discountTable');
    if (!discountTable && attempts > 0) {
        console.warn(`Discount table element not found, retrying... (${attempts} attempts left)`);
        setTimeout(() => fetchDiscounts(attempts - 1, delay), delay);
        return;
    }
    if (!discountTable) {
        console.error('Discount table element not found after retries');
        alert('Error: Discount table UI element not found. Please ensure the Discounts section is properly loaded.');
        return;
    }
    fetch('api.php?action=get_discounts')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('Discounts Data:', data);
            if (data.status === 'error') {
                console.error('API Error:', data.message);
                alert(`Error fetching discounts: ${data.message}`);
                discountTable.innerHTML = '<tr><td colspan="11" class="text-center">Failed to load discounts: ' + data.message + '</td></tr>';
                return;
            }
            discountTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(discount => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${discount.id}</td>
                        <td>${discount.name}</td>
                        <td>${discount.type === 'percentage' ? 'Percentage (%)' : 'Fixed ($)'}</td>
                        <td>${discount.type === 'percentage' ? discount.value + '%' : '$' + parseFloat(discount.value).toFixed(2)}</td>
                        <td>${discount.min_purchase_amount ? '$' + parseFloat(discount.min_purchase_amount).toFixed(2) : 'N/A'}</td>
                        <td>${discount.product_name || 'N/A'}</td>
                        <td>${discount.category_name || 'N/A'}</td>
                        <td>${discount.start_date}</td>
                        <td>${discount.end_date}</td>
                        <td>${discount.is_active ? 'Yes' : 'No'}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-2" onclick="editDiscount(${discount.id}, '${discount.name}', '${discount.type}', ${discount.value}, ${discount.min_purchase_amount || 'null'}, '${discount.product_id || ''}', '${discount.category_id || ''}', '${discount.start_date}', '${discount.end_date}', ${discount.is_active})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDiscount(${discount.id})">Delete</button>
                        </td>
                    `;
                    discountTable.appendChild(row);
                });
            } else {
                discountTable.innerHTML = '<tr><td colspan="11" class="text-center">No discounts found.</td></tr>';
            }
        })
        .catch(error => {
            logError('Error fetching discounts: ' + error.message);
            console.error('Error fetching discounts:', error);
            alert('Error fetching discounts: ' + error.message);
            discountTable.innerHTML = '<tr><td colspan="11" class="text-center">Failed to load discounts: ' + error.message + '</td></tr>';
        });
}

function editDiscount(id, name, type, value, min_purchase_amount, product_id, category_id, start_date, end_date) {
    discountModalLabel.textContent = 'Edit Discount';
    discountIdInput.value = id;
    discountNameInput.value = name;
    discountTypeInput.value = type;
    discountValueInput.value = value;
    discountMinPurchaseInput.value = min_purchase_amount || '';
    discountProductInput.value = product_id || '';
    discountCategoryInput.value = category_id || '';
    discountStartDateInput.value = start_date.replace(' ', 'T');
    discountEndDateInput.value = end_date.replace(' ', 'T');
    discountModal.show();
}

function deleteDiscount(id) {
    if (!confirm('Are you sure you want to delete this discount?')) return;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_discount', id: id, csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchDiscounts();
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error deleting discount:', error);
        alert('Error deleting discount: ' + error.message);
    });
}

discountForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const action = discountIdInput.value ? 'update_discount' : 'add_discount';
    const id = discountIdInput.value ? parseInt(discountIdInput.value) : undefined;
    const payload = {
        action: action,
        id: id,
        name: discountNameInput.value,
        type: discountTypeInput.value,
        value: parseFloat(discountValueInput.value),
        min_purchase_amount: discountMinPurchaseInput.value ? parseFloat(discountMinPurchaseInput.value) : null,
        product_id: discountProductInput.value || null,
        category_id: discountCategoryInput.value || null,
        start_date: discountStartDateInput.value.replace('T', ' '),
        end_date: discountEndDateInput.value.replace('T', ' '),
        is_active: discountIsActiveInput.checked,
        csrf_token: document.querySelector('meta[name="csrf-token"]').content || csrfToken // Ensure CSRF token is defined
    };
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            discountModal.hide();
            fetchDiscounts();
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error saving discount:', error);
        alert('Error saving discount: ' + error.message);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const sectionId = link.getAttribute('data-section');
            console.log(`Navigating to section: ${sectionId}`);
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = section.id === sectionId ? 'block' : 'none';
                console.log(`Section ${section.id} display: ${section.style.display}`);
            });
            navLinks.forEach(nav => nav.classList.remove('active'));
            link.classList.add('active');
            if (sectionId === 'dashboard') {
                fetchDashboardSummary();
                fetchDailySalesTrend();
                fetchLowStockProducts();
            } else if (sectionId === 'inventory') {
                fetchInventory();
            } else if (sectionId === 'discounts') {
                console.log('Calling fetchDiscounts');
                fetchDiscounts();
            }
        });
    });
    // Initialize Dashboard
    document.querySelector('.nav-link[data-section="dashboard"]').click();
    document.getElementById('fetchForecastBtn').addEventListener('click', fetchForecast);
});

document.getElementById('addDiscountBtn').addEventListener('click', () => {
    discountModalLabel.textContent = 'Add Discount';
    discountForm.reset();
    discountIdInput.value = ''; 
});


// Global variable to track editing state
window.editingAdjustmentId = null;

// Function to fetch stock adjustments
function fetchStockAdjustments() {
    const adjustmentTable = document.getElementById('adjustmentTable');
    const adjustmentSearch = document.getElementById('adjustmentSearch');
    const adjustmentStartDate = document.getElementById('adjustmentStartDate');
    const adjustmentEndDate = document.getElementById('adjustmentEndDate');

    if (!adjustmentTable || !adjustmentSearch || !adjustmentStartDate || !adjustmentEndDate) {
        console.error('Required Stock Adjustments elements are missing for fetchStockAdjustments');
        return;
    }

    const search = adjustmentSearch.value;
    const start_date = adjustmentStartDate.value;
    const end_date = adjustmentEndDate.value;
    let url = 'api.php?action=get_stock_adjustments';
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (start_date) url += `&start_date=${encodeURIComponent(start_date)}`;
    if (end_date) url += `&end_date=${encodeURIComponent(end_date)}`;

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('Stock Adjustments API Response:', data); // Add this for debugging
            adjustmentTable.innerHTML = '';
            if (data.status === 'error') {
                adjustmentTable.innerHTML = `<tr><td colspan="10" class="text-center">Error: ${data.message}</td></tr>`;
                return;
            }
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(adjustment => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-adjustment-id', adjustment.id);
                    row.innerHTML = `
                        <td>${adjustment.id}</td>
                        <td>${adjustment.product_name || 'N/A'}</td>
                        <td>${adjustment.quantity_before !== null ? adjustment.quantity_before : 'N/A'}</td>
                        <td>${adjustment.adjustment_value > 0 ? '+' : ''}${adjustment.adjustment_value}</td>
                        <td>${adjustment.quantity_after !== null ? adjustment.quantity_after : 'N/A'}</td>
                        <td>${adjustment.reason_name || 'N/A'}</td>
                        <td>${adjustment.notes || 'N/A'}</td>
                        <td>${adjustment.user_name || 'N/A'}</td>
                        <td>${adjustment.adjusted_at}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-2" onclick="editAdjustment(${adjustment.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteAdjustment(${adjustment.id})">Delete</button>
                        </td>
                    `;
                    adjustmentTable.appendChild(row);
                });
            } else {
                adjustmentTable.innerHTML = '<tr><td colspan="10" class="text-center">No adjustments found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching adjustments:', error);
            adjustmentTable.innerHTML = `<tr><td colspan="10" class="text-center">Error: ${error.message}</td></tr>`;
            if (typeof logError === 'function') {
                logError('Failed to fetch stock adjustments: ' + error.message);
            }
        });
}

// Function to edit an adjustment
function editAdjustment(id) {
    const row = document.querySelector(`tr[data-adjustment-id="${id}"]`);
    if (!row) return;

    const adjustmentProduct = document.getElementById('adjustmentProduct');
    const adjustmentProductId = document.getElementById('adjustmentProductId');
    const adjustmentValue = document.getElementById('adjustmentValue');
    const adjustmentReason = document.getElementById('adjustmentReason');
    const adjustmentNotes = document.getElementById('adjustmentNotes');
    const adjustmentFormTitle = document.getElementById('adjustmentFormTitle');
    const submitAdjustmentBtn = document.getElementById('submitAdjustmentBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');

    if (!adjustmentProduct || !adjustmentProductId || !adjustmentValue || !adjustmentReason || 
        !adjustmentNotes || !adjustmentFormTitle || !submitAdjustmentBtn || !cancelEditBtn) {
        console.error('Required form elements for editAdjustment are missing');
        return;
    }

    window.editingAdjustmentId = id;
    adjustmentProduct.value = row.cells[1].textContent;
    adjustmentProductId.value = '';
    adjustmentValue.value = parseInt(row.cells[3].textContent);
    adjustmentNotes.value = row.cells[6].textContent === 'N/A' ? '' : row.cells[6].textContent;

    // Fetch product ID
    fetch(`api.php?action=search_products&query=${encodeURIComponent(row.cells[1].textContent)}`)
        .then(res => res.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                adjustmentProductId.value = data.products[0].id;
            }
        })
        .catch(error => {
            console.error('Error fetching product ID:', error);
            if (typeof logError === 'function') {
                logError('Failed to fetch product ID for editing adjustment: ' + error.message);
            }
        });

    // Fetch reason ID
    fetch(`api.php?action=get_adjustment_reasons`)
        .then(res => res.json())
        .then(reasons => {
            const reasonName = row.cells[5].textContent;
            const reason = reasons.find(r => r.name === reasonName);
            if (reason) {
                adjustmentReason.value = reason.id;
            }
        })
        .catch(error => {
            console.error('Error fetching reasons:', error);
            if (typeof logError === 'function') {
                logError('Failed to fetch adjustment reasons: ' + error.message);
            }
        });

    adjustmentFormTitle.textContent = 'Edit Stock Adjustment';
    submitAdjustmentBtn.textContent = 'Update Adjustment';
    cancelEditBtn.style.display = 'inline-block';
}

// Function to delete an adjustment
function deleteAdjustment(id) {
    if (!confirm('Are you sure you want to delete this adjustment?')) return;

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete_stock_adjustment',
            id: id,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Adjustment deleted successfully!');
            fetchStockAdjustments();
        } else {
            throw new Error(data.message || 'Failed to delete adjustment');
        }
    })
    .catch(error => {
        console.error('Error deleting adjustment:', error);
        alert('Error deleting adjustment: ' + error.message);
        if (typeof logError === 'function') {
            logError('Failed to delete stock adjustment: ' + error.message);
        }
    });
}

// Function to reset the adjustment form
function resetAdjustmentForm() {
    const adjustmentForm = document.getElementById('adjustmentForm');
    const adjustmentProductId = document.getElementById('adjustmentProductId');
    const adjustmentFormTitle = document.getElementById('adjustmentFormTitle');
    const submitAdjustmentBtn = document.getElementById('submitAdjustmentBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');

    if (!adjustmentForm || !adjustmentProductId || !adjustmentFormTitle || !submitAdjustmentBtn || !cancelEditBtn) {
        console.error('Required form elements for resetAdjustmentForm are missing');
        return;
    }

    adjustmentForm.reset();
    adjustmentProductId.value = '';
    window.editingAdjustmentId = null;
    adjustmentFormTitle.textContent = 'Add New Stock Adjustment';
    submitAdjustmentBtn.textContent = 'Add Adjustment';
    cancelEditBtn.style.display = 'none';
}

// Initialize event listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentTable = document.getElementById('adjustmentTable');
    const adjustmentSearch = document.getElementById('adjustmentSearch');
    const adjustmentStartDate = document.getElementById('adjustmentStartDate');
    const adjustmentEndDate = document.getElementById('adjustmentEndDate');
    const suggestionList = document.getElementById('suggestionList');
    const adjustmentForm = document.getElementById('adjustmentForm');
    const adjustmentProduct = document.getElementById('adjustmentProduct');
    const adjustmentProductId = document.getElementById('adjustmentProductId');
    const adjustmentProductSuggestions = document.getElementById('adjustmentProductSuggestions');
    const adjustmentValue = document.getElementById('adjustmentValue');
    const adjustmentReason = document.getElementById('adjustmentReason');
    const adjustmentNotes = document.getElementById('adjustmentNotes');
    const submitAdjustmentBtn = document.getElementById('submitAdjustmentBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const exportAdjustmentsCsvBtn = document.getElementById('exportAdjustmentsCsvBtn');

    if (!adjustmentTable || !adjustmentSearch || !adjustmentStartDate || !adjustmentEndDate || !suggestionList ||
        !adjustmentForm || !adjustmentProduct || !adjustmentProductId || !adjustmentProductSuggestions ||
        !adjustmentValue || !adjustmentReason || !adjustmentNotes || !submitAdjustmentBtn || !cancelEditBtn ||
        !exportAdjustmentsCsvBtn) {
        console.error('Required Stock Adjustments elements are missing');
        return;
    }

    // Autocomplete for search
    adjustmentSearch.addEventListener('input', async () => {
        const term = adjustmentSearch.value;
        suggestionList.style.display = term.length >= 2 ? 'block' : 'none';
        if (term.length < 2) {
            suggestionList.innerHTML = '';
            return;
        }
        try {
            const response = await fetch(`api.php?action=get_product_suggestions&term=${encodeURIComponent(term)}`);
            if (!response.ok) throw new Error('Network error');
            const products = await response.json();
            suggestionList.innerHTML = products.map(p => `<li class="list-group-item">${p}</li>`).join('');
            document.querySelectorAll('#suggestionList .list-group-item').forEach(item => {
                item.addEventListener('click', () => {
                    adjustmentSearch.value = item.textContent;
                    suggestionList.style.display = 'none';
                    fetchStockAdjustments();
                });
            });
        } catch (error) {
            console.error('Autocomplete error:', error);
            if (typeof logError === 'function') {
                logError('Autocomplete error in stock adjustments search: ' + error.message);
            }
        }
    });

    // Autocomplete for product selection in form
    adjustmentProduct.addEventListener('input', async () => {
        const term = adjustmentProduct.value;
        adjustmentProductSuggestions.style.display = term.length >= 2 ? 'block' : 'none';
        if (term.length < 2) {
            adjustmentProductSuggestions.innerHTML = '';
            adjustmentProductId.value = '';
            return;
        }
        try {
            const response = await fetch(`api.php?action=get_product_suggestions&term=${encodeURIComponent(term)}`);
            if (!response.ok) throw new Error('Network error');
            const products = await response.json();
            adjustmentProductSuggestions.innerHTML = products.map(p => `<li class="list-group-item">${p}</li>`).join('');
            document.querySelectorAll('#adjustmentProductSuggestions .list-group-item').forEach(item => {
                item.addEventListener('click', () => {
                    adjustmentProduct.value = item.textContent;
                    adjustmentProductSuggestions.style.display = 'none';
                    fetch(`api.php?action=search_products&query=${encodeURIComponent(item.textContent)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.products && data.products.length > 0) {
                                adjustmentProductId.value = data.products[0].id;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching product ID:', error);
                            if (typeof logError === 'function') {
                                logError('Failed to fetch product ID for adjustment: ' + error.message);
                            }
                        });
                });
            });
        } catch (error) {
            console.error('Autocomplete error:', error);
            if (typeof logError === 'function') {
                logError('Autocomplete error in stock adjustments form: ' + error.message);
            }
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!adjustmentSearch.contains(e.target) && !suggestionList.contains(e.target)) {
            suggestionList.style.display = 'none';
        }
        if (!adjustmentProduct.contains(e.target) && !adjustmentProductSuggestions.contains(e.target)) {
            adjustmentProductSuggestions.style.display = 'none';
        }
    });

    // Form submission for add/edit
    // Form submission for add/edit
// Form submission for add/edit
// Form submission for add/edit
// Form submission for add/edit
adjustmentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    submitAdjustmentBtn.disabled = true; // Disable the button to prevent double submission

    const product_id = adjustmentProductId.value;
    const adjustment_value = parseInt(adjustmentValue.value);
    const reason_id = adjustmentReason.value;
    const notes = adjustmentNotes.value;
    const action = window.editingAdjustmentId ? 'update_stock_adjustment' : 'process_stock_adjustment';

    if (!product_id || isNaN(adjustment_value) || adjustment_value === 0 || !reason_id) {
        alert('Please fill in all required fields with valid values.');
        submitAdjustmentBtn.disabled = false;
        return;
    }

    const payload = {
        action: action,
        product_id: parseInt(product_id),
        adjustment_value: adjustment_value,
        reason_id: parseInt(reason_id),
        notes: notes,
        csrf_token: csrfToken
    };
    if (window.editingAdjustmentId) {
        payload.id = window.editingAdjustmentId;
    }

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        console.log('Process Stock Adjustment Response:', data);
        if (data.success) {
            alert(window.editingAdjustmentId ? 'Adjustment updated successfully!' : 'Adjustment added successfully!');
            resetAdjustmentForm();
            adjustmentStartDate.value = '';
            adjustmentEndDate.value = '';
            fetchStockAdjustments();
        } else {
            throw new Error(data.message || 'Failed to save adjustment');
        }
    } catch (error) {
        console.error('Error saving adjustment:', error);
        alert('Error saving adjustment: ' + error.message);
        if (typeof logError === 'function') {
            logError('Failed to save stock adjustment: ' + error.message);
        }
    } finally {
        submitAdjustmentBtn.disabled = false; // Re-enable the button
    }
});

    // Cancel edit
    cancelEditBtn.addEventListener('click', resetAdjustmentForm);

    // Export CSV
    exportAdjustmentsCsvBtn.addEventListener('click', () => {
        const start_date = adjustmentStartDate.value;
        const end_date = adjustmentEndDate.value;
        if (!start_date || !end_date) {
            alert('Please select both start and end dates.');
            return;
        }
        window.location.href = `api.php?action=export_adjustments_csv&start_date=${start_date}&end_date=${end_date}`;
    });

    // Bind filter inputs
    adjustmentSearch.addEventListener('input', fetchStockAdjustments);
    adjustmentStartDate.addEventListener('change', fetchStockAdjustments);
    adjustmentEndDate.addEventListener('change', fetchStockAdjustments);
});

        // Initialize Dashboard
        fetchDashboardSummary();
        fetchDailySalesTrend();
        fetchLowStockProducts();

    </script>
</body>
</html>