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


        /* Replace the existing .autocomplete-suggestions styles with these: */
.autocomplete-suggestions {
    position: absolute;
    top: 100%; /* Align directly below input */
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-top: none; /* Remove top border for seamless attachment */
    border-radius: 0 0 4px 4px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin: 0; /* Remove any margin */
    padding: 0;
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover {
    background-color: #f8f9fa;
}

/* Add this for the input group container */
.input-group.autocomplete-container {
    position: relative;
    z-index: 1001; /* Ensure container is above other elements */
}


/* Product Catalog Styles */
/* Product Catalog Styles */
#product-catalog .card {
    transition: transform 0.3s, box-shadow 0.3s;
}

#product-catalog .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

#product-catalog .product-img-container {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    overflow: hidden;
    border-bottom: 1px solid #e9ecef;
}

#product-catalog .product-img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    padding: 10px;
}

#product-catalog .card-body {
    padding: 1.5rem;
}

#product-catalog .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

#product-catalog .card-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1rem;
    height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
}

#product-catalog .product-stock {
    font-size: 0.9rem;
    font-weight: 500;
}

#product-catalog .in-stock {
    color: #28a745;
}

#product-catalog .out-of-stock {
    color: #dc3545;
}

#product-catalog .input-group {
    margin-top: 1rem;
}

#product-catalog .add-to-cart-btn {
    white-space: nowrap;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.add-to-cart-btn {
    white-space: nowrap;
}

/* Cart Styles */
#cartCountBadge {
    font-size: 0.8rem;
    vertical-align: middle;
}

#selectedCartCustomer {
    min-height: 60px;
}

.toast {
    max-width: 350px;
}


/* Product Catalog Styles */
#productGrid {
    min-height: 500px;
}

.product-card {
    transition: all 0.3s ease;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.product-img-container {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    padding: 15px;
}

.product-img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.product-stock {
    font-size: 0.9rem;
}

.in-stock {
    color: #28a745;
}

.out-of-stock {
    color: #dc3545;
}

/* Cart Styles */
#cartItems img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.cart-item-name {
    font-weight: 500;
}

.quantity-control {
    width: 120px;
}

.remove-item-btn {
    color: #dc3545;
    cursor: pointer;
}

#customerSearchResults {
    max-height: 200px;
    overflow-y: auto;
    position: absolute;
    width: 100%;
    z-index: 1000;
    display: none;
}

.payment-method {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
}

/* Toast Notification */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1100;
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
                        <a class="nav-link" data-section="product-catalog">Product Catalog</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-section="sales-forecasting">Sales Forecasting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="sales-report">Sales Report</a>
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

                                            <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" data-section="cart-section">
                                <i class="fas fa-shopping-cart"></i>
                                <span id="cartItemCountBadge" class="badge bg-danger rounded-pill">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">Logout</a>
                        </li>
                    </ul>
                 
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


       <!-- Product Catalog Section -->
<!-- Product Catalog Section -->
<div id="product-catalog" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Product Catalog</h2>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group autocomplete-container">
                        <input type="text" id="productSearch" class="form-control" placeholder="Search by name or barcode...">
                        <button class="btn btn-primary" id="searchProductsBtn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div id="productSuggestions" class="autocomplete-suggestions" style="display: none;"></div>
                </div>
                <div class="col-md-4">
                    <select id="productCategoryFilter" class="form-control">
                        <option value="">All Categories</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, name FROM categories");
                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" id="resetFiltersBtn">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
            <div class="row" id="productGrid">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading products...</p>
                </div>
            </div>
            <div id="pagination-controls" class="text-center mt-3"></div>
        </div>
    </div>
</div>

      <!-- Cart Section -->
<div id="cart-section" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="card-title mb-0">Shopping Cart</h2>
                <span class="badge bg-primary rounded-pill" id="cartItemCount">0 items</span>
            </div>
            
            <!-- Customer Selection -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group mb-3">
                                <input type="text" id="cartCustomerSearch" class="form-control" placeholder="Search customer by name, email or phone">
                                <button class="btn btn-outline-secondary" type="button" id="searchCustomerBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="customerSearchResults" class="list-group" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div id="selectedCustomerInfo" class="mt-3 p-3 bg-light rounded" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 id="selectedCustomerName"></h6>
                                <small class="text-muted" id="selectedCustomerDetails"></small>
                                <div class="mt-2">
                                    <span class="badge bg-info" id="selectedCustomerPoints">0 points</span>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-danger" id="removeCustomerBtn">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cart Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Cart Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50%">Product</th>
                                    <th width="15%">Price</th>
                                    <th width="20%">Quantity</th>
                                    <th width="15%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <tr>
                                    <td colspan="4" class="text-center py-5">Your cart is empty</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" class="form-control" id="loyaltyPointsToUse" placeholder="Points to use" min="0" value="0">
                                <button class="btn btn-primary" id="applyLoyaltyPoints">
                                    Apply Points
                                </button>
                            </div>
                            <small class="text-muted" id="loyaltyPointsNote">100 points = $1 discount</small>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="cartSubtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span id="cartDiscount">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (5%):</span>
                            <span id="cartTax">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span id="cartTotal">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment Methods</h5>
                    <button class="btn btn-sm btn-success" id="addPaymentMethodBtn">
                        <i class="fas fa-plus"></i> Add Payment
                    </button>
                </div>
                <div class="card-body">
                    <div id="paymentMethods">
                        <div class="payment-method mb-3">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select class="form-control payment-type">
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="loyalty_points">Loyalty Points</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" class="form-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger w-100 remove-payment" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total Paid:</span>
                            <span id="totalPaid">$0.00</span>
                        </div>
                        <div class="text-end text-muted small">
                            Remaining: <span id="remainingBalance">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Checkout Button -->
            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg" id="checkoutBtn" disabled>
                    <i class="fas fa-check-circle"></i> Complete Checkout
                </button>
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
    <div class="mb-3">
        <label for="productImage" class="form-label">Image URL</label>
        <input type="text" class="form-control" id="productImage" placeholder="e.g., images/product.jpg">
    </div>
    <div class="mb-3">
        <label for="productDescription" class="form-label">Description</label>
        <textarea class="form-control" id="productDescription" rows="4"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Product</button>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>



<div id="customers" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Customer Management</h2>
            <div class="mb-3 d-flex align-items-center">
                <div class="suggestion-container me-2" style="flex-grow: 1;">
                    <input type="text" id="customerSearch" class="form-control" placeholder="Search customers by username or email" autocomplete="off">
                    <div id="customerSuggestions" class="suggestion-dropdown" style="display: none;"></div>
                </div>
                <button id="customerSearchBtn" class="btn btn-primary me-2">Search</button>
                <button id="addCustomerBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#customerCreateModal">Add Customer</button>
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
                            <th>Loyalty Points</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer History Modal (updated to remove Status column) -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Customer Purchase History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6 id="customerInfo"></h6>
                    <button id="downloadCustomerHistoryPdf" class="btn btn-primary btn-sm">Download PDF Report</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Products</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
            <th>Tax (5%)</th>
            <th>Final Amount</th>
        </tr>
    </thead>
    <tbody id="historyTable"></tbody>
</table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Create Modal (unchanged) -->
<div class="modal fade" id="customerCreateModal" tabindex="-1" aria-labelledby="customerCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerCreateModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customerCreateForm">
                    <div class="mb-3">
                        <label for="createUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="createUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="createEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="createEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="createFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="createFirstName">
                    </div>
                    <div class="mb-3">
                        <label for="createLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="createLastName">
                    </div>
                    <div class="mb-3">
                        <label for="createPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="createPhone">
                    </div>
                    <div class="mb-3">
                        <label for="createAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="createAddress" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Customer Edit Modal (unchanged) -->
<div class="modal fade" id="customerEditModal" tabindex="-1" aria-labelledby="customerEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerEditModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customerEditForm">
                <input type="hidden" id="editCustomerId">
                <div class="mb-3">
                    <label for="editUsername" class="form-label">Username</label>
                    <input type="text" class="form-control" id="editUsername" required>
                </div>
                <div class="mb-3">
                    <label for="editEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="editEmail" required>
                </div>
                <div class="mb-3">
                    <label for="editFirstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="editFirstName">
                </div>
                <div class="mb-3">
                    <label for="editLastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="editLastName">
                </div>
                <div class="mb-3">
                    <label for="editPhone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="editPhone">
                </div>
                <div class="mb-3">
                    <label for="editAddress" class="form-label">Address</label>
                    <textarea class="form-control" id="editAddress" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="editLoyaltyPoints" class="form-label">Loyalty Points</label>
                    <input type="number" class="form-control" id="editLoyaltyPoints" readonly>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustments Section -->
<div id="stock_adjustments" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Stock Adjustment History</h2>
            <div class="row mb-3">
                <div class="col-md-4 position-relative">
                    <input type="text" class="form-control" id="adjustmentSearch" placeholder="Search by product or reason...">
                    <ul id="suggestionList" class="list-group position-absolute w-100" style="display: none; z-index: 1000;"></ul>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="adjustmentStartDate" placeholder="Start Date">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <input type="date" class="form-control me-2" id="adjustmentEndDate" placeholder="End Date">
                    <button id="exportStockAdjustmentsCsvBtn" class="btn btn-secondary">Export CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Current Quantity</th>
                            <th>Adjustment</th>
                            <th>Reason</th>
                            <th>Adjusted By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="adjustmentTable"></tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>


const salesStartDateInput = document.getElementById('salesStartDate');
    const salesEndDateInput = document.getElementById('salesStartDate');
    const salesSearchQueryInput = document.getElementById('salesSearchQuery');
    const fetchSalesReportBtn = document.getElementById('fetchSalesReportBtn');
    const exportSalesCsvBtn = document.getElementById('exportSalesCsvBtn');
    const exportSalesPdfBtn = document.getElementById('exportSalesPdfBtn');
    const salesReportTable = document.getElementById('salesReportTable');
    const mostSoldStartDateInput = document.getElementById('mostSoldStartDate');
    const mostSoldEndDateInput = document.getElementById('mostSoldEndDate');
    const fetchMostSoldBtn = document.getElementById('fetchMostSoldBtn');
    const exportMostSoldCsvBtn = document.getElementById('exportMostSoldCsvBtn');
    const mostSoldTable = document.getElementById('mostSoldTable');
    const inventoryTable = document.getElementById('inventoryTable');
    const customerTableBody = document.getElementById('customerTableBody');
    const adjustmentTable = document.getElementById('adjustmentTable');
    const adjustmentSearch = document.getElementById('adjustmentSearch');
    const adjustmentStartDate = document.getElementById('adjustmentStartDate');
    const adjustmentEndDate = document.getElementById('adjustmentEndDate');
    const suggestionList = document.getElementById('suggestionList');
    const customerSearch = document.getElementById('customerSearch');
    const customerSuggestions = document.getElementById('customerSuggestions');
    const customerSearchBtn = document.getElementById('customerSearchBtn');
    let salesTrendChart = null;
    let cart = [];
    let customerId = null;
    let selectedProduct = null;
    const POINTS_PER_DOLLAR = 1;
    const TAX_RATE = 0.05;
    const cartTable = document.getElementById('cartTable');
    const subtotalElement = document.getElementById('subtotal');
    const taxElement = document.getElementById('tax');
    const cartTotalElement = document.getElementById('cartTotal');
    const customerSearchBar = document.getElementById('cartCustomerSearch'); // Changed from customerSearchBar
    const selectedCustomerDiv = document.getElementById('selectedCustomerInfo'); // Changed from selectedCustomer
    const productSearchBar = document.getElementById('productSearch'); // Changed from productSearchBar
    const productSuggestions = document.getElementById('productSuggestions');
    const selectedProductDiv = document.getElementById('selectedProduct');
    // const quantityInput = document.getElementById('quantity');
    // const addToCartBtn = document.getElementById('addToCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    // const retryReceiptBtn = document.getElementById('retryReceiptBtn');
    const paymentMethodsContainer = document.getElementById('paymentMethods'); // Changed from paymentMethodsContainer
    const addPaymentMethodBtn = document.getElementById('addPaymentMethodBtn');
    const paymentTotalSpan = document.getElementById('totalPaid'); // Changed from paymentTotal
    const cartTotalDisplay = document.getElementById('cartTotal'); // Changed from cartTotalDisplay
    const productGrid = document.getElementById('productGrid');
    const productSearch = document.getElementById('productSearch');
    const productCategoryFilter = document.getElementById('productCategoryFilter');
    const searchProductsBtn = document.getElementById('searchProductsBtn');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const cartItemCountBadge = document.getElementById('cartItemCountBadge');
    const cartItemCount = document.getElementById('cartItemCount');
    const cartItemsTable = document.getElementById('cartItems');
    const customerSearchResults = document.getElementById('customerSearchResults');
    const selectedCustomerInfo = document.getElementById('selectedCustomerInfo');
    const selectedCustomerName = document.getElementById('selectedCustomerName');
    const selectedCustomerDetails = document.getElementById('selectedCustomerDetails');
    const selectedCustomerPoints = document.getElementById('selectedCustomerPoints');
    const removeCustomerBtn = document.getElementById('removeCustomerBtn');
    const loyaltyPointsToUse = document.getElementById('loyaltyPointsToUse');
  
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartDiscount = document.getElementById('cartDiscount');
    const cartTax = document.getElementById('cartTax');
    const cartTotal = document.getElementById('cartTotal');
    const paymentMethods = document.getElementById('paymentMethods');
    const totalPaid = document.getElementById('totalPaid');
    const remainingBalance = document.getElementById('remainingBalance');
    const productForm = document.getElementById('productForm');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productModalLabel = document.getElementById('productModalLabel');
    const productIdInput = document.getElementById('productId');
    const productNameInput = document.getElementById('productName');
    const productCategoryInput = document.getElementById('productCategory');
    const productPriceInput = document.getElementById('productPrice');
    const productQuantityInput = document.getElementById('productQuantity');
    const productBarcodeInput = document.getElementById('productBarcode');
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
    const customerModalLabel = document.getElementById('customerModalLabel');
    const historyTable = document.getElementById('historyTable');
    const customerCreateForm = document.getElementById('customerCreateForm');
    const customerEditForm = document.getElementById('customerEditForm');
    const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    const customerCreateModal = new bootstrap.Modal(document.getElementById('customerCreateModal'));
    const customerEditModal = new bootstrap.Modal(document.getElementById('customerEditModal'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';





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
            if (data.status === 'error') {
                throw new Error(data.message || 'API returned an error');
            }
            document.getElementById('totalSales').textContent = data.total_sales || 0;
            document.getElementById('totalRevenue').textContent = `$${parseFloat(data.total_revenue || 0).toFixed(2)}`;
            document.getElementById('totalProductsSold').textContent = data.total_products_sold || 0;
        })
        .catch(error => {
            logError('Error fetching dashboard summary: ' + error.message);
            console.error('Error fetching dashboard summary:', error);
            document.getElementById('totalSales').textContent = 'N/A';
            document.getElementById('totalRevenue').textContent = 'N/A';
            document.getElementById('totalProductsSold').textContent = 'N/A';
            alert('Failed to load dashboard summary: ' + error.message);
        });
}

        

       
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
    fetch('api.php?action=get_low_stock_products', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
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
            const lowStockTable = document.getElementById('lowStockTable');
            lowStockTable.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load low stock products: ' + error.message + '</td></tr>';
            alert('Failed to load low stock products: ' + error.message);
        });
}


function updateCartTable() {
    fetch('api.php?action=get_active_discounts', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            const discounts = Array.isArray(data) ? data : (data.discounts || []);
            cartItemsTable.innerHTML = '';
            let subtotal = 0;
            let discountTotal = 0;
            const pointsToUse = parseInt(loyaltyPointsToUse.value) || 0;
            const loyaltyDiscount = pointsToUse / 100;

            if (cart.length === 0) {
                cartItemsTable.innerHTML = '<tr><td colspan="4" class="text-center py-5">Your cart is empty</td></tr>';
                cartSubtotal.textContent = '$0.00';
                cartDiscount.textContent = '$0.00';
                cartTax.textContent = '$0.00';
                cartTotal.textContent = '$0.00';
                checkoutBtn.disabled = true;
                return;
            }

            cart.forEach((item, index) => {
                let discount = discounts.find(d => 
                    (d.product_id && d.product_id == item.id) || 
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

                if (loyaltyDiscount > 0) {
                    const totalItemValue = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
                    const itemProportion = (item.quantity * item.price) / totalItemValue;
                    const loyaltyDiscountPerItem = loyaltyDiscount * itemProportion;
                    discountedPrice = Math.max(0, discountedPrice - loyaltyDiscountPerItem / item.quantity);
                }

                const itemTotal = item.quantity * discountedPrice;
                subtotal += itemTotal;
                discountTotal += discountAmount * item.quantity;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${item.image_url || 'images/placeholder.jpg'}" alt="${item.name}">
                            <span class="cart-item-name ms-2">${item.name}</span>
                        </div>
                    </td>
                    <td>$${discountedPrice.toFixed(2)}</td>
                    <td>
                        <div class="input-group input-group-sm quantity-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateCartItemQuantity(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${item.quantity}" min="1" onchange="updateCartItemQuantity(${index}, this.value)">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateCartItemQuantity(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td>
                        $${itemTotal.toFixed(2)}
                        <i class="fas fa-trash remove-item-btn ms-2" onclick="removeCartItem(${index})"></i>
                    </td>
                `;
                cartItemsTable.appendChild(row);
            });

            const tax = subtotal * TAX_RATE;
            const total = subtotal + tax;

            cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
            cartDiscount.textContent = `$${discountTotal.toFixed(2)}`;
            cartTax.textContent = `$${tax.toFixed(2)}`;
            cartTotal.textContent = `$${total.toFixed(2)}`;
            checkoutBtn.disabled = cart.length === 0 || !customerId;
            updatePaymentTotal();
        })
        .catch(error => {
            logError('Error fetching discounts for cart: ' + error.message);
            alert('Error updating cart: ' + error.message);
        });
}



function updateCartQuantity(index, change) {
        const newQuantity = cart[index].quantity + parseInt(change);
        if (newQuantity < 1) {
            removeCartItem(index);
            return;
        }
        cart[index].quantity = newQuantity;
        updateCartTable();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        updateCartTable();
    }


// Preload placeholder image (data URI for a 1x1 transparent pixel)
const placeholderImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

// Fetch products for the Product Catalog
async function fetchProducts(page = 1, perPage = 10, search = '', categoryId = null) {
    if (window.isFetchingProducts) {
        console.log('FetchProducts already in progress, skipping...');
        return;
    }
    window.isFetchingProducts = true;
    try {
        const url = 'api.php';
        const params = new URLSearchParams({
            action: 'get_product_catalog',
            page: page,
            per_page: perPage
        });
        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);

        console.log(`Fetching products: ${url}?${params.toString()}`);
        const response = await fetch(`${url}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error(`HTTP ${response.status}: ${errorText}`);
            logError(`HTTP ${response.status}: ${errorText}`);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const data = await response.json();
        console.log('Raw response:', JSON.stringify(data));

        if (!data.products || !Array.isArray(data.products)) {
            console.error('Invalid response format:', data);
            logError('Invalid response format');
            throw new Error('Invalid response format');
        }

        // Update product catalog UI
        const productContainer = document.getElementById('productGrid');
        if (!productContainer) {
            console.error('Product container not found');
            logError('Product container not found');
            return;
        }

        productContainer.innerHTML = ''; // Clear existing products
        if (data.products.length === 0) {
            productContainer.innerHTML = '<div class="col-12 text-center py-5">No products found.</div>';
            return;
        }

        data.products.forEach(product => {
            console.log(`Rendering product: ${product.name}, Image: ${product.products_image || 'placeholder'}`);
            const productElement = document.createElement('div');
            productElement.className = 'col-md-3 mb-4';
            productElement.innerHTML = `
                <div class="card product-card">
                    <div class="product-img-container">
                        <img src="${product.products_image ? `/POS/images/${product.products_image}` : placeholderImage}" class="product-img" alt="${product.name}" onerror="this.onerror=null; console.warn('Image failed to load: ${product.products_image || 'placeholder'}'); this.src='${placeholderImage}'">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">Price: $${parseFloat(product.price || 0).toFixed(2)}</p>
                        <p class="product-stock ${product.quantity > 0 ? 'in-stock' : 'out-of-stock'}">
                            Quantity: ${product.quantity || 0}
                        </p>
                        <p class="card-text">Category: ${product.category_name || 'None'}</p>
                        <p class="card-text">Barcode: ${product.barcode || 'N/A'}</p>
                        <div class="input-group">
                            <input type="number" class="form-control" value="1" min="1" max="${product.quantity || 1}" id="qty-${product.id}">
                            <button class="btn btn-primary add-to-cart-btn" onclick="addToCart(${product.id}, parseInt(document.getElementById('qty-${product.id}').value))">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            `;
            productContainer.appendChild(productElement);
        });

        // Update pagination
        const paginationContainer = document.getElementById('pagination-controls');
        if (paginationContainer) {
            paginationContainer.innerHTML = '';
            for (let i = 1; i <= (data.total_pages || 1); i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = `btn btn-sm ${i === page ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
                pageButton.disabled = i === page;
                pageButton.onclick = () => fetchProducts(i, perPage, search, categoryId);
                paginationContainer.appendChild(pageButton);
            }
        }

        console.log(`Fetched ${data.products.length} products`);
    } catch (error) {
        console.error('Fetch Error:', error);
        logError(`Fetch Error: ${error.message}`);
        const productContainer = document.getElementById('productGrid');
        if (productContainer) {
            productContainer.innerHTML = '<div class="col-12 text-center py-5">Failed to load products: ' + error.message + '</div>';
        }
    } finally {
        window.isFetchingProducts = false;
        console.log('FetchProducts completed.');
    }
}

// Initial call to fetch products
document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
});


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


        function searchCustomers() {
    const query = cartCustomerSearch.value.trim();
    if (query.length < 2) {
        customerSearchResults.style.display = 'none';
        return;
    }
    fetch(`api.php?action=get_all_customers&search=${encodeURIComponent(query)}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            customerSearchResults.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(customer => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = `${customer.username} (${customer.email})`;
                    item.addEventListener('click', () => {
                        customerId = customer.id;
                        selectedCustomerName.textContent = customer.username;
                        selectedCustomerDetails.textContent = `${customer.email} | ${customer.phone || 'N/A'} | ${customer.address || 'N/A'}`;
                        selectedCustomerPoints.textContent = `${customer.loyalty_points || 0} points`;
                        selectedCustomerInfo.style.display = 'block';
                        customerSearchResults.style.display = 'none';
                        cartCustomerSearch.value = '';
                        checkoutBtn.disabled = cart.length === 0;
                    });
                    customerSearchResults.appendChild(item);
                });
                customerSearchResults.style.display = 'block';
            } else {
                customerSearchResults.style.display = 'none';
            }
        })
        .catch(error => {
            logError('Error searching customers: ' + error.message);
            alert('Error searching customers: ' + error.message);
        });
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
                            <button class="btn btn-sm btn-warning me-2" onclick="editProduct(${product.id}, '${product.name}', ${product.category_id}, ${product.price}, ${product.quantity}, '${product.barcode}', '${product.image_url || ''}', '${product.description || ''}')">Edit</button>
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



function addToCart(id, name, price, quantity, image_url, category_id) {
        const qtyInput = document.getElementById(`quantity-${id}`);
        const qty = parseInt(qtyInput.value) || 1;
        if (qty > quantity) {
            alert('Cannot add more than available stock.');
            return;
        }
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += qty;
        } else {
            cart.push({ id, name, price, quantity: qty, image_url, category_id });
        }
        updateCartTable();
    }


    function fetchStockAdjustments() {
    const search = document.getElementById('adjustmentSearch').value;
    const start_date = document.getElementById('adjustmentStartDate').value;
    const end_date = document.getElementById('adjustmentEndDate').value;
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
            const adjustmentTable = document.getElementById('adjustmentTable');
            adjustmentTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(adjustment => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${adjustment.id}</td>
                        <td>${adjustment.product_name || 'N/A'}</td>
                        <td>${adjustment.current_quantity !== null ? adjustment.current_quantity : 'N/A'}</td>
                        <td>${adjustment.quantity > 0 ? '+' : ''}${adjustment.quantity}</td>
                        <td>${adjustment.reason}</td>
                        <td>${adjustment.user_name || 'N/A'}</td>
                        <td>${adjustment.adjusted_at}</td>
                    `;
                    adjustmentTable.appendChild(row);
                });
            } else {
                adjustmentTable.innerHTML = '<tr><td colspan="7" class="text-center">No adjustments found.</td></tr>';
            }
        })
        .catch(error => {
            logError('Error fetching adjustments: ' + error.message);
            alert('Error fetching adjustments: ' + error.message);
        });
}




function fetchCustomers(search = '') {
    const url = search ? `api.php?action=get_all_customers&search=${encodeURIComponent(search)}` : 'api.php?action=get_all_customers';
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            customerTableBody.innerHTML = '';
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
                        <td>${customer.loyalty_points || 0}</td>
                        <td>${customer.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-info me-1" onclick="viewCustomerHistory(${customer.id}, '${customer.username}')">History</button>
                            <button class="btn btn-sm btn-warning me-1" onclick="editCustomer(${customer.id}, '${customer.username}', '${customer.email}', '${customer.first_name || ''}', '${customer.last_name || ''}', '${customer.phone || ''}', '${customer.address || ''}', ${customer.loyalty_points || 0})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id})">Delete</button>
                        </td>
                    `;
                    customerTableBody.appendChild(row);
                });
            } else {
                customerTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No customers found.</td></tr>';
            }
        })
        .catch(error => {
            logError('Error fetching customers: ' + error.message);
            alert('Error fetching customers: ' + error.message);
        });
}


function checkout() {
        if (cart.length === 0) {
            alert('Cart is empty.');
            return;
        }
        const paymentInputs = paymentMethods.querySelectorAll('.payment-amount');
        const paymentTypes = paymentMethods.querySelectorAll('.payment-type');
        let paymentTotal = 0;
        const paymentMethodsData = [];
        paymentInputs.forEach((input, index) => {
            const amount = parseFloat(input.value) || 0;
            if (amount > 0) {
                paymentMethodsData.push({
                    type: paymentTypes[index].value,
                    amount: amount
                });
                paymentTotal += amount;
            }
        });
        const cartTotalValue = parseFloat(cartTotal.textContent.replace('$', '')) || 0;
        if (paymentTotal < cartTotalValue) {
            alert(`Total payment ($${paymentTotal.toFixed(2)}) is less than cart total ($${cartTotalValue.toFixed(2)}).`);
            return;
        }
        if (paymentTotal > cartTotalValue) {
            alert(`Total payment ($${paymentTotal.toFixed(2)}) exceeds cart total ($${cartTotalValue.toFixed(2)}).`);
            return;
        }
        const pointsToRedeem = parseInt(loyaltyPointsToUse.value) || 0;
        const subtotal = parseFloat(cartSubtotal.textContent.replace('$', '')) || 0;
        const pointsEarned = Math.floor(subtotal * POINTS_PER_DOLLAR);
        const payload = {
            action: 'checkout',
            customer_id: customerId,
            items: cart.map(item => ({ id: item.id, quantity: item.quantity })),
            points_earned: pointsEarned,
            points_redeemed: pointsToRedeem,
            payment_methods: paymentMethodsData,
            csrf_token: csrfToken
        };
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(payload)
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Sale processed successfully! Sale ID: ' + data.sale_id);
                    cart = [];
                    updateCartTable();
                    customerSearchBar.value = '';
                    selectedCustomerInfo.style.display = 'none';
                    customerId = null;
                    loyaltyPointsToUse.value = 0;
                    paymentMethods.innerHTML = `
                        <div class="payment-method mb-3">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select class="form-control payment-type">
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="loyalty_points">Loyalty Points</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" class="form-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger w-100 remove-payment" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    totalPaid.textContent = '$0.00';
                    fetchDashboardSummary();
                    fetchDailySalesTrend();
                    fetchLowStockProducts();
                } else {
                    alert('Error processing sale: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                logError('Error during checkout: ' + error.message);
                alert('Error during checkout: ' + error.message);
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
                alert('Error generating PDF: ' + error.message);
            });
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



function viewCustomerHistory(id, username) {
    document.getElementById('customerModalLabel').textContent = `Purchase History for ${username}`;
    document.getElementById('customerInfo').textContent = `Customer: ${username} (ID: ${id})`;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_customer_detailed_history', customer_id: id, csrf_token: csrfToken })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            const historyTable = document.getElementById('historyTable');
            historyTable.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(sale => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${sale.sale_id}</td>
                        <td>${new Date(sale.sale_date).toLocaleString()}</td>
                        <td>${sale.product_name}</td>
                        <td>${sale.quantity}</td>
                        <td>$${parseFloat(sale.unit_price).toFixed(2)}</td>
                        <td>$${parseFloat(sale.subtotal).toFixed(2)}</td>
                        <td>$${parseFloat(sale.tax_amount).toFixed(2)}</td>
                        <td>$${parseFloat(sale.final_amount).toFixed(2)}</td>
                    `;
                    historyTable.appendChild(row);
                });
            } else {
                historyTable.innerHTML = '<tr><td colspan="8" class="text-center">No purchase history found.</td></tr>';
            }
            customerModal.show();
        })
        .catch(error => {
            logError('Error fetching customer history: ' + error.message);
            alert('Error fetching customer history: ' + error.message);
            historyTable.innerHTML = '<tr><td colspan="8" class="text-center">Error loading history.</td></tr>';
            customerModal.show();
        });

    document.getElementById('downloadCustomerHistoryPdf').onclick = () => {
        const payload = {
            action: 'generate_customer_history_pdf',
            customer_id: id,
            csrf_token: csrfToken
        };
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(response => {
                const contentType = response.headers.get('Content-Type');
                if (!response.ok) {
                    if (contentType.includes('application/json')) {
                        return response.json().then(err => {
                            throw new Error(err.message || `HTTP ${response.status}: ${response.statusText}`);
                        });
                    }
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                if (!contentType.includes('application/pdf')) {
                    return response.text().then(text => {
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'Expected PDF but received invalid response');
                        } catch {
                            throw new Error('Expected PDF but received: ' + contentType);
                        }
                    });
                }
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `CustomerHistory_${id}.pdf`;
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                logError('Error generating customer history PDF: ' + error.message);
                alert('Error generating PDF: ' + error.message);
            });
    };
}



async function fetchForecast() {
    try {
        const startDate = document.getElementById('forecastStartDate').value;
        const endDate = document.getElementById('forecastEndDate').value;
        const days = document.getElementById('forecastDays').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content || '';
         if (!csrfToken) console.warn('CSRF token not found');

        if (!startDate || !endDate || !days) {
            throw new Error('Please fill all required fields');
        }

        const forecastBtn = document.getElementById('fetchForecastBtn');
        forecastBtn.disabled = true;
        forecastBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';

        const response = await fetch(`api.php?action=get_sales_forecast&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&days=${encodeURIComponent(days)}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status !== 'success' || !data.forecasts || !Array.isArray(data.forecasts)) {
            throw new Error(data.message || 'No forecast data available');
        }

        const labels = data.forecasts.map(f => f.product_name);
        const historicalData = data.forecasts.map(f => parseFloat(f.historical_sales) || 0);
        const predictedData = data.forecasts.map(f => parseFloat(f.predicted_sales) || 0);

        const ctx = document.getElementById('forecastChart').getContext('2d');

        if (window.forecastChartInstance) {
            window.forecastChartInstance.destroy();
        }

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
                        title: { display: true, text: 'Quantity Sold' }
                    },
                    x: { title: { display: true, text: 'Products' } }
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
        logError('Forecast error: ' + error.message);
        alert('Error generating forecast: ' + error.message);
    } finally {
        const forecastBtn = document.getElementById('fetchForecastBtn');
        forecastBtn.disabled = false;
        forecastBtn.textContent = 'Generate Forecast';
    }
}



function editCustomer(id, username, email, first_name, last_name, phone, address, loyalty_points) {
    document.getElementById('editCustomerId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    document.getElementById('editFirstName').value = first_name;
    document.getElementById('editLastName').value = last_name;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editAddress').value = address;
    document.getElementById('editLoyaltyPoints').value = loyalty_points;
    customerEditModal.show();
}



function deleteCustomer(customerId) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete_customer',
            id: customerId,
            csrf_token: document.querySelector('meta[name="csrf-token"]').content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Customer deleted successfully');
            fetchCustomers(); // Refresh the customer table
        } else {
            logError('Error deleting customer: ' + data.message);
            if (data.message === 'Cannot delete customer with existing orders') {
                alert('This customer has existing orders and cannot be deleted. Please review their order history.');
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        logError('Error deleting customer: ' + error);
        alert('An error occurred while deleting the customer');
    });
}



        function exportStockAdjustmentsCsv() {
    const search = document.getElementById('adjustmentSearch').value;
    const start_date = document.getElementById('adjustmentStartDate').value;
    const end_date = document.getElementById('adjustmentEndDate').value;
    let url = 'api.php?action=export_stock_adjustments_csv';
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (start_date) url += `&start_date=${encodeURIComponent(start_date)}`;
    if (end_date) url += `&end_date=${encodeURIComponent(end_date)}`;
    window.location.href = url;
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



function editProduct(id, name, categoryId, price, quantity, barcode, image_url, description) {
        productModalLabel.textContent = 'Edit Product';
        productIdInput.value = id;
        productNameInput.value = name;
        productCategoryInput.value = categoryId;
        productPriceInput.value = price;
        productQuantityInput.value = quantity;
        productBarcodeInput.value = barcode;
        document.getElementById('productImage').value = image_url;
        document.getElementById('productDescription').value = description;
        productModal.show();
    }





       





function updateCartItemQuantity(index, change) {
    const item = cart[index];
    let newQuantity = typeof change === 'number' ? item.quantity + change : parseInt(change);
    if (newQuantity < 1) newQuantity = 1;
    
    fetch(`api.php?action=get_product&id=${item.id}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(product => {
            if (newQuantity > product.quantity) {
                alert(`Cannot set quantity to ${newQuantity}. Only ${product.quantity} available in stock.`);
                return;
            }
            item.quantity = newQuantity;
            updateProductQuantity(item.id, product.quantity - newQuantity + item.quantity);
            updateCartTable();
            updateCartBadge();
        })
        .catch(error => {
            logError('Error updating cart item quantity: ' + error.message);
            alert('Error updating cart item quantity: ' + error.message);
        });
}

function removeCartItem(index) {
    const item = cart[index];
    fetch(`api.php?action=get_product&id=${item.id}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(product => {
            updateProductQuantity(item.id, product.quantity + item.quantity);
            cart.splice(index, 1);
            updateCartTable();
            updateCartBadge();
        })
        .catch(error => {
            logError('Error removing cart item: ' + error.message);
            alert('Error removing cart item: ' + error.message);
        });
}


function updatePaymentTotal() {
        const paymentInputs = paymentMethods.querySelectorAll('.payment-amount');
        let total = 0;
        paymentInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        totalPaid.textContent = `$${total.toFixed(2)}`;
        const cartTotalValue = parseFloat(cartTotal.textContent.replace('$', '')) || 0;
        remainingBalance.textContent = `$${(cartTotalValue - total).toFixed(2)}`;
    }

function addPaymentMethodRow() {
    const row = document.createElement('div');
    row.className = 'payment-method mb-3';
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-5">
                <select class="form-control payment-type">
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="loyalty_points">Loyalty Points</option>
                </select>
            </div>
            <div class="col-md-5">
                <input type="number" class="form-control payment-amount" placeholder="Amount" min="0" step="0.01">
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger w-100 remove-payment">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    paymentMethods.appendChild(row);
    row.querySelector('.payment-amount').addEventListener('input', updatePaymentTotal);
    row.querySelector('.remove-payment').addEventListener('click', () => {
        row.remove();
        updatePaymentTotal();
    });
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const buttons = document.querySelectorAll('.remove-payment');
    buttons.forEach(btn => btn.disabled = buttons.length <= 1);
}

addPaymentMethodBtn.addEventListener('click', addPaymentMethodRow);

function generateReceipt(saleId, customerId) {
    return fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            action: 'generate_receipt',
            sale_id: saleId,
            customer_id: customerId,
            csrf_token: csrfToken
        })
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Receipt_${saleId}.pdf`;
            a.click();
            window.URL.revokeObjectURL(url);
        });
}



const applyLoyaltyPoints = document.getElementById('applyLoyaltyPoints');
applyLoyaltyPoints.addEventListener('click', () => {
    if (!customerId) {
        alert('Please select a customer before redeeming points.');
        return;
    }
    const pointsToUseValue = parseInt(loyaltyPointsToUse.value) || 0;
    const availablePoints = parseInt(selectedCustomerPoints.textContent) || 0;
    if (pointsToUseValue <= 0) {
        alert('Please enter a valid number of points to redeem.');
        return;
    }
    if (pointsToUseValue > availablePoints) {
        alert(`Cannot redeem ${pointsToUseValue} points. Only ${availablePoints} points available.`);
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            action: 'redeem_loyalty_points',
            customer_id: customerId,
            points_to_redeem: pointsToUseValue,
            csrf_token: csrfToken
        })
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(`Successfully redeemed ${pointsToUseValue} points for $${data.discount_amount.toFixed(2)} discount.`);
                selectedCustomerPoints.textContent = `${data.new_points} points`;
                updateCartTable();
            } else {
                alert('Error redeeming points: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            logError('Error redeeming loyalty points: ' + error.message);
            alert('Error redeeming points: ' + error.message);
        });
});





checkoutBtn.addEventListener('click', () => {
    if (!customerId) {
        alert('Please select a customer before checkout.');
        return;
    }
    if (cart.length === 0) {
        alert('Cart is empty. Please add products before checkout.');
        return;
    }
    const total = parseFloat(cartTotal.textContent.replace('$', '')) || 0;
    const paid = parseFloat(totalPaid.textContent.replace('$', '')) || 0;
    if (paid < total) {
        alert(`Total payment ($${paid.toFixed(2)}) is less than cart total ($${total.toFixed(2)}).`);
        return;
    }
    if (paid > total) {
        alert(`Total payment ($${paid.toFixed(2)}) exceeds cart total ($${total.toFixed(2)}). Please adjust payment amounts.`);
        return;
    }
    const paymentMethodsData = Array.from(document.querySelectorAll('.payment-method')).map(pm => ({
        type: pm.querySelector('.payment-type').value,
        amount: parseFloat(pm.querySelector('.payment-amount').value) || 0
    }));
    if (paymentMethodsData.some(pm => pm.amount < 0)) {
        alert('Payment amounts cannot be negative.');
        return;
    }
    const subtotal = parseFloat(cartSubtotal.textContent.replace('$', '')) || 0;
    const pointsEarned = Math.floor(subtotal * POINTS_PER_DOLLAR);
    const pointsToRedeem = parseInt(loyaltyPointsToUse.value) || 0;
    const payload = {
        action: 'checkout',
        customer_id: customerId,
        items: cart.map(item => ({ id: item.id, quantity: item.quantity })),
        points_earned: pointsEarned,
        points_redeemed: pointsToRedeem,
        payment_methods: paymentMethodsData,
        csrf_token: csrfToken
    };
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Sale processed successfully! Sale ID: ' + data.sale_id);
                generateReceipt(data.sale_id, customerId)
                    .catch(error => {
                        logError('Error generating receipt: ' + error.message);
                        alert('Sale processed, but error generating receipt: ' + error.message);
                    })
                    .finally(() => {
                        fetchDashboardSummary();
                        fetchDailySalesTrend();
                        fetchLowStockProducts();
                        cart = [];
                        customerId = null;
                        selectedCustomerInfo.style.display = 'none';
                        cartCustomerSearch.value = '';
                        loyaltyPointsToUse.value = '0';
                        paymentMethods.innerHTML = `
                            <div class="payment-method mb-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <select class="form-control payment-type">
                                            <option value="cash">Cash</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="debit_card">Debit Card</option>
                                            <option value="loyalty_points">Loyalty Points</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" class="form-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-danger w-100 remove-payment" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        updateCartTable();
                        updateCartBadge();
                    });
            } else {
                alert('Error processing sale: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            logError('Error during checkout: ' + error.message);
            alert('Error during checkout: ' + error.message);
        });
});

// Event Listeners for Cart Section
cartCustomerSearch.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchCustomers, 300);
});



document.getElementById('searchCustomerBtn').addEventListener('click', searchCustomers);
removeCustomerBtn.addEventListener('click', () => {
    customerId = null;
    selectedCustomerInfo.style.display = 'none';
    checkoutBtn.disabled = true;
});


addPaymentMethodBtn.addEventListener('click', addPaymentMethodRow);


const paymentAmountInput = paymentMethods.querySelector('.payment-amount');
if (paymentAmountInput) {
    paymentAmountInput.addEventListener('input', updatePaymentTotal);
}

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
            switch (sectionId) {
                case 'dashboard':
                    fetchDashboardSummary();
                    fetchDailySalesTrend();
                    fetchLowStockProducts();
                    break;
                case 'product-catalog':
                    fetchProducts();
                    break;
                case 'cart-section':
                    updateCartTable();
                    break;
                case 'inventory':
                    fetchInventory();
                    break;
                case 'discounts':
                    fetchDiscounts();
                    break;
                case 'customers':
                    fetchCustomers();
                    break;
                case 'stock_adjustments':
                    fetchStockAdjustments();
                    break;
                case 'sales-report':
                    fetchSalesReport();
                    break;
                case 'most-sold-products':
                    fetchMostSoldProducts();
                    break;
                case 'sales-forecasting':
                    fetchForecast();
                    break;
            }
        }
    });
});

function addToCart(productId, quantity) {
    fetch(`api.php?action=get_product&id=${productId}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(product => {
            if (!product || product.success === false) {
                throw new Error(product.message || 'Product not found');
            }
            const availableQuantity = parseInt(product.quantity) || 0;
            if (availableQuantity < quantity) {
                alert(`Cannot add ${quantity} items. Only ${availableQuantity} available in stock.`);
                return;
            }
            const existingItem = cart.find(item => item.id === product.id);
            const newQuantity = existingItem ? existingItem.quantity + quantity : quantity;
            if (newQuantity > availableQuantity) {
                alert(`Cannot add ${newQuantity} items. Only ${availableQuantity} available in stock.`);
                return;
            }
            if (existingItem) {
                existingItem.quantity = newQuantity;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: quantity,
                    category_id: product.category_id
                });
            }
            updateCartBadge();
            showToast(`Added ${quantity} x ${product.name} to cart.`);
            updateProductQuantity(product.id, availableQuantity - quantity);
            updateCartTable(); // Refresh cart display
        })
        .catch(error => {
            logError('Error adding to cart: ' + error.message);
            alert('Error adding to cart: ' + error.message);
        });
}

function updateProductQuantity(productId, newQuantity) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            action: 'update_product_quantity',
            id: productId,
            quantity: newQuantity,
            csrf_token: csrfToken
        })
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                fetchProducts(); // Refresh product catalog to reflect updated stock
                fetchLowStockProducts(); // Update low stock alerts
            } else {
                throw new Error(data.message || 'Unknown error');
            }
        })
        .catch(error => {
            logError('Error updating product quantity: ' + error.message);
            alert('Error updating product quantity: ' + error.message);
        });
}

function updateCartBadge() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartItemCountBadge.textContent = totalItems;
    cartItemCount.textContent = `${totalItems} items`;
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-notification show';
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}


// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event Listeners for Product Catalog
const debouncedFetchProducts = debounce(() => {
    const search = productSearch.value.trim();
    const categoryId = productCategoryFilter.value || null;
    console.log(`Fetching products with search: ${search}, category: ${categoryId}`);
    fetchProducts(1, 10, search, categoryId);
}, 300);

searchProductsBtn.addEventListener('click', debouncedFetchProducts);
resetFiltersBtn.addEventListener('click', () => {
    productSearch.value = '';
    productCategoryFilter.value = '';
    productSuggestions.innerHTML = '';
    productSuggestions.style.display = 'none';
    debouncedFetchProducts();
});
productSearch.addEventListener('input', debouncedFetchProducts);
productCategoryFilter.addEventListener('change', debouncedFetchProducts);

// Initialize Product Catalog
if (document.getElementById('product-catalog').classList.contains('active')) {
    debouncedFetchProducts();
}


productCategoryFilter.addEventListener('change', () => {
    const categoryId = productCategoryFilter.value || null;
    console.log(`Category filter changed: ${categoryId}`);
    fetchProducts(1, 10, productSearch.value.trim(), categoryId);
});

// Initialize Product Catalog
fetchProducts();


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

let customerTimeout;

function setupAutocomplete(input, suggestionsContainer, apiAction, minLength = 2, onSelect) {
    let timeout;
    input.addEventListener('input', () => {
        clearTimeout(timeout);
        const query = input.value.trim();
        suggestionsContainer.style.display = query.length >= minLength ? 'block' : 'none';
        if (query.length < minLength) {
            suggestionsContainer.innerHTML = '';
            return;
        }
        timeout = setTimeout(async () => {
            try {
                const response = await fetch(`api.php?action=${apiAction}&term=${encodeURIComponent(query)}`, {
                    headers: { 'X-CSRF-Token': csrfToken }
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                const items = await response.json();
                const suggestions = Array.isArray(items) ? items : [];
                suggestionsContainer.innerHTML = suggestions.length > 0
                    ? suggestions.map(item => `<div class="autocomplete-item">${item}</div>`).join('')
                    : '<div class="autocomplete-item">No suggestions found.</div>';
                suggestionsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
                    item.addEventListener('click', () => {
                        input.value = item.textContent;
                        suggestionsContainer.style.display = 'none';
                        onSelect(item.textContent);
                    });
                });
            } catch (error) {
                logError(`Autocomplete error for ${apiAction}: ${error.message}`);
                suggestionsContainer.innerHTML = '<div class="autocomplete-item text-danger">Error loading suggestions</div>';
                suggestionsContainer.style.display = 'block';
            }
        }, 300);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

setupAutocomplete(customerSearchBar, customerSuggestions, 'search_customers', 2, query => searchCustomers());
setupAutocomplete(productSearchBar, productSuggestions, 'search_products', 2, query => fetchProducts());
setupAutocomplete(adjustmentSearch, suggestionList, 'get_product_suggestions', 2, query => fetchStockAdjustments());

// Product Search Autocomplete
// Product Search Autocomplete
let productTimeout;
productSearchBar.addEventListener('input', () => {
    clearTimeout(productTimeout);
    const query = productSearchBar.value.trim();
    if (query.length < 2) {
        productSuggestions.innerHTML = '';
        productSuggestions.style.display = 'none';
        fetchProducts(1, 10, ''); // Reset product list if query is too short
        return;
    }
    productTimeout = setTimeout(() => {
        fetch(`api.php?action=search_products&term=${encodeURIComponent(query)}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        })
            .then(response => {
                console.log('Search Products Response Status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                return response.json();
            })
            .then(data => {
                console.log('Search Products Data:', data);
                productSuggestions.innerHTML = '';
                // Handle API response: expect array or handle error object
                let products = [];
                if (Array.isArray(data)) {
                    products = data;
                } else if (data.status === 'error') {
                    console.warn('API error:', data.message);
                    products = [];
                } else {
                    console.warn('Unexpected response format:', data);
                    products = [];
                }
                if (products.length > 0) {
                    products.forEach(product => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.textContent = `${product.name} (Barcode: ${product.barcode || 'N/A'}) - $${parseFloat(product.price || 0).toFixed(2)} (Qty: ${product.quantity || 0})`;
                        div.addEventListener('click', () => {
                            productSearchBar.value = product.name;
                            productSuggestions.innerHTML = '';
                            productSuggestions.style.display = 'none';
                            fetchProducts(1, 10, product.name);
                        });
                        productSuggestions.appendChild(div);
                    });
                    productSuggestions.style.display = 'block';
                    positionSuggestions(productSearchBar, productSuggestions);
                } else {
                    productSuggestions.innerHTML = '<div class="autocomplete-item">No products found.</div>';
                    productSuggestions.style.display = 'block';
                    positionSuggestions(productSearchBar, productSuggestions);
                }
            })
            .catch(error => {
                logError('Error searching products: ' + error.message);
                console.error('Error searching products:', error);
                productSuggestions.innerHTML = '<div class="autocomplete-item text-danger">Error loading suggestions</div>';
                productSuggestions.style.display = 'block';
                positionSuggestions(productSearchBar, productSuggestions);
            });
    }, 300);
});

document.addEventListener('click', (e) => {
    if (!customerSearchBar.contains(e.target) && !customerSuggestions.contains(e.target)) {
        customerSuggestions.style.display = 'none';
    }
    if (!productSearchBar.contains(e.target) && !productSuggestions.contains(e.target)) {
        productSuggestions.style.display = 'none';
    }
});

// addToCartBtn.addEventListener('click', () => {
//     if (!selectedProduct) {
//         alert('Please select a product.');
//         return;
//     }
//     const quantity = parseInt(quantityInput.value);
//     if (quantity <= 0) {
//         alert('Please enter a valid quantity.');
//         return;
//     }
//     const maxQuantity = selectedProduct.maxQuantity;
//     if (quantity > maxQuantity) {
//         alert(`Only ${maxQuantity} units of ${selectedProduct.name} are available.`);
//         return;
//     }
//     const existingItem = cart.find(item => item.id === selectedProduct.id);
//     if (existingItem) {
//         const newQuantity = existingItem.quantity + quantity;
//         if (newQuantity > maxQuantity) {
//             alert(`Cannot add ${newQuantity} units. Only ${maxQuantity} units are available.`);
//             return;
//         }
//         existingItem.quantity = newQuantity;
//     } else {
//         cart.push({
//             id: selectedProduct.id,
//             name: selectedProduct.name,
//             price: selectedProduct.price,
//             quantity: quantity,
//             maxQuantity: maxQuantity
//         });
//     }
//     updateCartTable();
//     quantityInput.value = 1;
//     productSearchBar.value = '';
//     selectedProductDiv.textContent = '';
//     selectedProduct = null;
// });

function generateReceipt(saleId, customerId) {
    const receiptPayload = {
        action: 'generate_receipt',
        sale_id: saleId,
        customer_id: customerId,
        csrf_token: csrfToken
    };
    return fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify(receiptPayload)
    })
        .then(response => {
            const contentType = response.headers.get('Content-Type');
            if (!response.ok) {
                if (contentType.includes('application/json')) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Failed to generate receipt');
                    });
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            if (!contentType.includes('application/pdf')) {
                return response.text().then(text => {
                    throw new Error('Expected PDF but received: ' + contentType);
                });
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Receipt_${saleId}.pdf`;
            a.click();
            window.URL.revokeObjectURL(url);
            retryReceiptBtn.classList.add('d-none');
        });
}




checkoutBtn.addEventListener('click', () => {
    if (!customerId) {
        alert('Please select a customer.');
        return;
    }
    if (cart.length === 0) {
        alert('Cart is empty. Please add products to proceed.');
        return;
    }
    const pointsToRedeem = parseInt(document.getElementById('loyaltyPointsRedeem').value) || 0;
    const availablePoints = parseInt(document.getElementById('loyaltyPointsInfo').textContent.match(/\d+/)[0]) || 0;
    if (pointsToRedeem > availablePoints) {
        alert(`Cannot redeem ${pointsToRedeem} points. Only ${availablePoints} points available.`);
        return;
    }

    // Validate inventory
    const invalidItems = cart.filter(item => item.quantity > item.maxQuantity);
    if (invalidItems.length > 0) {
        alert(`Invalid quantities for: ${invalidItems.map(item => item.name).join(', ')}`);
        return;
    }

    // Validate payment methods
    const paymentMethods = Array.from(document.querySelectorAll('.payment-method-row')).map(row => {
        const method = row.querySelector('.payment-method-select').value;
        const amount = parseFloat(row.querySelector('.payment-method-amount').value) || 0;
        return { method, amount };
    });
    const paymentTotal = paymentMethods.reduce((sum, pm) => sum + pm.amount, 0);
    const cartTotal = parseFloat(document.getElementById('cartTotal').textContent);
    if (paymentTotal < cartTotal) {
        alert(`Total payment ($${paymentTotal.toFixed(2)}) is less than cart total ($${cartTotal.toFixed(2)}).`);
        return;
    }
    if (paymentTotal > cartTotal) {
        alert(`Total payment ($${paymentTotal.toFixed(2)}) exceeds cart total ($${cartTotal.toFixed(2)}). Please adjust payment amounts.`);
        return;
    }
    if (paymentMethods.some(pm => pm.amount < 0)) {
        alert('Payment amounts cannot be negative.');
        return;
    }

    const subtotal = parseFloat(document.getElementById('subtotal').textContent);
    if (subtotal > 1000 && !confirm('Confirm high-value transaction of $' + subtotal.toFixed(2) + '?')) {
        return;
    }
    const pointsEarned = Math.floor(subtotal * POINTS_PER_DOLLAR);
    const payload = {
        action: 'checkout',
        customer_id: customerId,
        items: cart.map(item => ({ id: item.id, quantity: item.quantity })),
        points_earned: pointsEarned,
        points_redeemed: pointsToRedeem,
        payment_methods: paymentMethods,
        csrf_token: csrfToken
    };
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Sale processed successfully! Sale ID: ' + data.sale_id);
                document.getElementById('loyaltyPointsInfo').textContent = `${data.new_points || 0} points available (100 points = $1 discount)`;
                generateReceipt(data.sale_id, customerId)
                    .catch(error => {
                        logError('Error generating receipt: ' + error.message);
                        alert('Sale processed, but error generating receipt: ' + error.message);
                        retryReceiptBtn.classList.remove('d-none');
                        retryReceiptBtn.dataset.saleId = data.sale_id;
                        retryReceiptBtn.dataset.customerId = customerId;
                    })
                    .finally(() => {
                        fetchDashboardSummary();
                        fetchDailySalesTrend();
                        fetchLowStockProducts();
                        cart = [];
                        updateCartTable();
                        customerSearchBar.value = '';
                        selectedCustomerDiv.textContent = '';
                        customerId = null;
                        document.getElementById('loyaltyPointsRedeem').value = 0;
                        document.getElementById('loyaltyPointsInfo').textContent = '0 points available (100 points = $1 discount)';
                        // Reset payment methods
                        paymentMethodsContainer.innerHTML = `
                            <div class="payment-method-row" style="display: flex; margin-bottom: 5px;">
                                <select class="payment-method-select" style="margin-right: 10px;">
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Loyalty Points">Loyalty Points</option>
                                </select>
                                <input type="number" class="payment-method-amount" placeholder="Amount" step="0.01" min="0" style="margin-right: 10px;" />
                                <button type="button" class="remove-payment-method" style="display: none;">Remove</button>
                            </div>
                        `;
                        paymentTotalSpan.textContent = '0.00';
                        const paymentAmountInput = paymentMethodsContainer.querySelector('.payment-amount');
if (paymentAmountInput) {
    paymentAmountInput.addEventListener('input', updatePaymentTotal);
}
                    });
            } else {
                alert('Error processing sale: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            logError('Error during checkout: ' + error.message);
            alert('Error during checkout: ' + error.message);
        });
});



// if (retryReceiptBtn) {
//     retryReceiptBtn.addEventListener('click', () => {
//         const saleId = retryReceiptBtn.dataset.saleId;
//         const customerId = retryReceiptBtn.dataset.customerId;
//         generateReceipt(saleId, customerId).catch(error => {
//             logError('Error retrying receipt: ' + error.message);
//             alert('Error retrying receipt: ' + error.message);
//         });
//     });
// }




        // Sales Report Functions


    

       

        

        



       

       

        // Inventory Functions
  
       
      



// Hide suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!adjustmentSearch.contains(e.target) && !suggestionList.contains(e.target)) {
        suggestionList.style.display = 'none';
    }
});

     



       

        async function submitForm(form, modal, action, payload, successMessage, refreshCallback) {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...payload, action, csrf_token: csrfToken })
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        const data = await response.json();
        if (data.success || data.status === 'success') {
            alert(successMessage);
            modal.hide();
            form.reset();
            refreshCallback();
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    } catch (error) {
        logError(`Error in ${action}: ${error.message}`);
        alert(`Error in ${action}: ${error.message}`);
    }
}

productForm.addEventListener('submit', e => {
    e.preventDefault();
    const id = productIdInput.value;
    submitForm(
        productForm,
        productModal,
        id ? 'update_product' : 'add_product',
        {
            id: id || null,
            name: productNameInput.value,
            category_id: productCategoryInput.value,
            price: parseFloat(productPriceInput.value),
            quantity: parseInt(productQuantityInput.value),
            barcode: productBarcodeInput.value,
            image_url: document.getElementById('productImage').value,
            description: document.getElementById('productDescription').value
        },
        id ? 'Product updated successfully.' : 'Product added successfully.',
        () => { fetchInventory(); fetchLowStockProducts(); }
    );
});

discountForm.addEventListener('submit', e => {
    e.preventDefault();
    const id = discountIdInput.value;
    submitForm(
        discountForm,
        discountModal,
        id ? 'update_discount' : 'add_discount',
        {
            id: id || null,
            name: discountNameInput.value,
            type: discountTypeInput.value,
            value: parseFloat(discountValueInput.value),
            min_purchase_amount: discountMinPurchaseInput.value ? parseFloat(discountMinPurchaseInput.value) : null,
            product_id: discountProductInput.value || null,
            category_id: discountCategoryInput.value || null,
            start_date: discountStartDateInput.value.replace('T', ' '),
            end_date: discountEndDateInput.value.replace('T', ' '),
            is_active: discountIsActiveInput.checked
        },
        id ? 'Discount updated successfully.' : 'Discount added successfully.',
        fetchDiscounts
    );
});

        document.getElementById('addProductBtn').addEventListener('click', () => {
            productModalLabel.textContent = 'Add Product';
            productForm.reset();
            productIdInput.value = '';
        });






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


document.addEventListener('click', (e) => {
    if (!customerSearch.contains(e.target) && !customerSuggestions.contains(e.target)) {
        customerSuggestions.style.display = 'none';
        customerSearch.classList.remove('has-suggestions');
    }
});

customerSearch.addEventListener('keydown', (e) => {
    const items = customerSuggestions.querySelectorAll('.suggestion-item');
    let activeItem = customerSuggestions.querySelector('.suggestion-item.active');
    if (!customerSuggestions.style.display || customerSuggestions.style.display === 'none') return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!activeItem) {
            items[0]?.classList.add('active');
        } else {
            activeItem.classList.remove('active');
            const next = activeItem.nextElementSibling;
            if (next) next.classList.add('active');
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (activeItem) {
            activeItem.classList.remove('active');
            const prev = activeItem.previousElementSibling;
            if (prev) prev.classList.add('active');
        }
    } else if (e.key === 'Enter' && activeItem) {
        e.preventDefault();
        activeItem.click();
    }
});

customerSearchBtn.addEventListener('click', () => {
    fetchCustomers(customerSearch.value);
});

customerSearch.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        fetchCustomers(customerSearch.value);
    }
});

customerCreateForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const payload = {
        action: 'add_customer',
        username: document.getElementById('createUsername').value,
        email: document.getElementById('createEmail').value,
        first_name: document.getElementById('createFirstName').value || null,
        last_name: document.getElementById('createLastName').value || null,
        phone: document.getElementById('createPhone').value || null,
        address: document.getElementById('createAddress').value || null,
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
            if (data.status === 'success') {
                alert('Customer created successfully.');
                customerCreateModal.hide();
                customerCreateForm.reset();
                fetchCustomers();
            } else {
                throw new Error(data.message || 'Unknown error');
            }
        })
        .catch(error => {
            logError('Error creating customer: ' + error.message);
            alert('Error creating customer: ' + error.message);
        });
});

customerEditForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const payload = {
        action: 'update_customer',
        id: document.getElementById('editCustomerId').value,
        username: document.getElementById('editUsername').value,
        email: document.getElementById('editEmail').value,
        first_name: document.getElementById('editFirstName').value || null,
        last_name: document.getElementById('editLastName').value || null,
        phone: document.getElementById('editPhone').value || null,
        address: document.getElementById('editAddress').value || null,
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
            if (data.status === 'success') {
                alert('Customer updated successfully.');
                customerEditModal.hide();
                fetchCustomers();
            } else {
                throw new Error(data.message || 'Unknown error');
            }
        })
        .catch(error => {
            logError('Error updating customer: ' + error.message);
            alert('Error updating customer: ' + error.message);
        });
});










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



document.getElementById('addDiscountBtn').addEventListener('click', () => {
    discountModalLabel.textContent = 'Add Discount';
    discountForm.reset();
    discountIdInput.value = '';
});




document.addEventListener('DOMContentLoaded', function() {
    const adjustmentTable = document.getElementById('adjustmentTable');
    const adjustmentSearch = document.getElementById('adjustmentSearch');
    const adjustmentStartDate = document.getElementById('adjustmentStartDate');
    const adjustmentEndDate = document.getElementById('adjustmentEndDate');
    const suggestionList = document.getElementById('suggestionList');

    // Debug: Log if elements are missing
    if (!adjustmentTable) console.error('adjustmentTable element not found');
    if (!adjustmentSearch) console.error('adjustmentSearch element not found');
    if (!adjustmentStartDate) console.error('adjustmentStartDate element not found');
    if (!adjustmentEndDate) console.error('adjustmentEndDate element not found');
    if (!suggestionList) console.error('suggestionList element not found');

    // Exit if critical elements are missing
    if (!adjustmentTable || !adjustmentSearch || !suggestionList) {
        console.error('Required DOM elements missing, cannot initialize Stock Adjustments.');
        return;
    }



// Add exportStockAdjustmentsCsv function


// Add Export CSV button event listener
document.getElementById('exportStockAdjustmentsCsvBtn')?.addEventListener('click', exportStockAdjustmentsCsv);

    // Autocomplete logic
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
        if (adjustmentSearch && suggestionList && 
            !adjustmentSearch.contains(e.target) && 
            !suggestionList.contains(e.target)) {
            suggestionList.style.display = 'none';
        }
    });

    adjustmentSearch.addEventListener('input', fetchStockAdjustments);
    adjustmentStartDate.addEventListener('change', fetchStockAdjustments);
    adjustmentEndDate.addEventListener('change', fetchStockAdjustments);

    // Initial fetch
    fetchStockAdjustments();
});



document.getElementById('fetchForecastBtn')?.addEventListener('click', fetchForecast);

// Initialize Sales Forecasting section when shown
document.querySelector('.nav-link[data-section="sales-forecasting"]')?.addEventListener('click', () => {
    fetchForecast();
});

function positionSuggestions(inputElement, suggestionsElement) {
    const rect = inputElement.getBoundingClientRect();
    const parentRect = inputElement.closest('.input-group').getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    suggestionsElement.style.width = `${parentRect.width}px`;
    suggestionsElement.style.left = `${parentRect.left + window.pageXOffset}px`;
    suggestionsElement.style.top = `${parentRect.bottom + scrollTop}px`;
}

// Call this whenever the input is focused or window is resized
customerSearchBar.addEventListener('focus', () => {
    positionSuggestions(customerSearchBar, customerSuggestions);
});
productSearchBar.addEventListener('focus', () => {
    positionSuggestions(productSearchBar, productSuggestions);
});
window.addEventListener('resize', () => {
    if (customerSuggestions.style.display === 'block') {
        positionSuggestions(customerSearchBar, customerSuggestions);
    }
    if (productSuggestions.style.display === 'block') {
        positionSuggestions(productSearchBar, productSuggestions);
    }
});
// Event Listeners
    document.getElementById('refreshLowStockBtn').addEventListener('click', fetchLowStockProducts);
    fetchSalesReportBtn.addEventListener('click', fetchSalesReport);
    exportSalesCsvBtn.addEventListener('click', exportSalesCsv);
    exportSalesPdfBtn.addEventListener('click', exportSalesPdf);
    fetchMostSoldBtn.addEventListener('click', fetchMostSoldProducts);
    exportMostSoldCsvBtn.addEventListener('click', exportMostSoldCsv);
    document.getElementById('exportStockAdjustmentsCsvBtn')?.addEventListener('click', exportStockAdjustmentsCsv);
    productSearch.addEventListener('input', fetchProducts);
    productCategoryFilter.addEventListener('change', fetchProducts);
    searchProductsBtn.addEventListener('click', fetchProducts);
    resetFiltersBtn.addEventListener('click', () => {
        productSearch.value = '';
        productCategoryFilter.value = '';
        fetchProducts();
    });
    customerSearchBar.addEventListener('input', searchCustomers);
    removeCustomerBtn.addEventListener('click', () => {
        customerId = null;
        selectedCustomerInfo.style.display = 'none';
        loyaltyPointsToUse.value = 0;
        updateCartTable();
    });
    
    
    checkoutBtn.addEventListener('click', checkout);
    productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = productIdInput.value;
        const action = id ? 'update_product' : 'add_product';
        const payload = {
            action: action,
            id: id || null,
            name: productNameInput.value,
            category_id: productCategoryInput.value,
            price: parseFloat(productPriceInput.value),
            quantity: parseInt(productQuantityInput.value),
            barcode: productBarcodeInput.value,
            image_url: document.getElementById('productImage').value,
            description: document.getElementById('productDescription').value,
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
                    fetchLowStockProducts();
                } else {
                    alert('Error saving product: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                logError('Error saving product: ' + error.message);
                alert('Error saving product: ' + error.message);
            });
    });
    document.getElementById('addProductBtn').addEventListener('click', () => {
        productModalLabel.textContent = 'Add Product';
        productForm.reset();
        productIdInput.value = '';
    });
    discountForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const action = discountIdInput.value ? 'update_discount' : 'add_discount';
        const id = discountIdInput.value ? parseInt(discountIdInput.value) : null;
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
            csrf_token: csrfToken
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
                logError('Error saving discount: ' + error.message);
                alert('Error saving discount: ' + error.message);
            });
    });
    document.getElementById('addDiscountBtn').addEventListener('click', () => {
        discountModalLabel.textContent = 'Add Discount';
        discountForm.reset();
        discountIdInput.value = '';
    });
    customerCreateForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const payload = {
            action: 'add_customer',
            username: document.getElementById('createUsername').value,
            email: document.getElementById('createEmail').value,
            first_name: document.getElementById('createFirstName').value || null,
            last_name: document.getElementById('createLastName').value || null,
            phone: document.getElementById('createPhone').value || null,
            address: document.getElementById('createAddress').value || null,
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
                if (data.status === 'success') {
                    alert('Customer created successfully.');
                    customerCreateModal.hide();
                    customerCreateForm.reset();
                    fetchCustomers();
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                logError('Error creating customer: ' + error.message);
                alert('Error creating customer: ' + error.message);
            });
    });
    customerEditForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const payload = {
            action: 'update_customer',
            id: document.getElementById('editCustomerId').value,
            username: document.getElementById('editUsername').value,
            email: document.getElementById('editEmail').value,
            first_name: document.getElementById('editFirstName').value || null,
            last_name: document.getElementById('editLastName').value || null,
            phone: document.getElementById('editPhone').value || null,
            address: document.getElementById('editAddress').value || null,
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
                if (data.status === 'success') {
                    alert('Customer updated successfully.');
                    customerEditModal.hide();
                    fetchCustomers();
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                logError('Error updating customer: ' + error.message);
                alert('Error updating customer: ' + error.message);
            });
    });
    let searchTimeout;
    customerSearch.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const query = customerSearch.value.trim();
        if (query.length < 2) {
            customerSuggestions.innerHTML = '';
            customerSuggestions.style.display = 'none';
            return;
        }
        searchTimeout = setTimeout(() => {
            fetch(`api.php?action=get_all_customers&search=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    customerSuggestions.innerHTML = '';
                    const customers = Array.isArray(data) ? data : [];
                    if (customers.length > 0) {
                        customers.forEach(customer => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = `${customer.username} (${customer.email})`;
                            div.addEventListener('click', () => {
                                customerSearch.value = customer.username;
                                customerSuggestions.innerHTML = '';
                                customerSuggestions.style.display = 'none';
                                fetchCustomers(customer.username);
                            });
                            customerSuggestions.appendChild(div);
                        });
                        customerSuggestions.style.display = 'block';
                    } else {
                        customerSuggestions.style.display = 'none';
                    }
                })
                .catch(error => {
                    logError('Error searching customers: ' + error.message);
                    customerSuggestions.style.display = 'none';
                });
        }, 300);
    });


    customerSearchBtn.addEventListener('click', () => {
        fetchCustomers(customerSearch.value);
    });
    customerSearch.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            fetchCustomers(customerSearch.value);
        }
    });
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
            logError('Autocomplete error: ' + error.message);
        }
    });
    document.addEventListener('click', (e) => {
        if (!adjustmentSearch.contains(e.target) && !suggestionList.contains(e.target)) {
            suggestionList.style.display = 'none';
        }
        if (!customerSearch.contains(e.target) && !customerSuggestions.contains(e.target)) {
            customerSuggestions.style.display = 'none';
        }
    });
    adjustmentSearch.addEventListener('input', fetchStockAdjustments);
    adjustmentStartDate.addEventListener('change', fetchStockAdjustments);
    adjustmentEndDate.addEventListener('change', fetchStockAdjustments);
    document.getElementById('fetchForecastBtn')?.addEventListener('click', fetchForecast);

    // Initialize Dashboard
    fetchDashboardSummary();
    fetchDailySalesTrend();
    fetchLowStockProducts();

    </script>
</body>
</html>