<?php
require_once 'config.php';
error_log("Admin Page Session Data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0 || $_SESSION['role'] !== 'admin') {
    error_log("Admin access denied - User ID: " . ($_SESSION['user_id'] ?? 'null') . 
             ", Role: " . ($_SESSION['role'] ?? 'null'));
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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_fixes.css">
    <link rel="stylesheet" href="css/quick_add_cart.css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
      <script src="script.js" defer></script>
</head>
<body>
    <?php include 'navbar.php'; ?>


<main class="flex-grow-1">
    <div class="custom-container mt-4">
        <!-- <h1 class="mb-4">Supplier Dashboard</h1> -->
        <div class="admin-welcome-card">
            <i class="fas fa-user-circle welcome-icon"></i>
            <div class="welcome-details">
                <h1>Welcome, <span class="username"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</span></h1>
                <div class="user-meta">
                    <span><i class="fas fa-user-shield"></i> Role: Admin</span>
                    <span><i class="fas fa-sign-in-alt"></i> Last Login: <?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <!-- Dashboard Summary -->
         <div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-sales">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h5 class="card-title">Total Sales (Last 30 Days)</h5>
                <p class="card-text" id="totalSales">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-revenue">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h5 class="card-title">Total Revenue (Last 30 Days)</h5>
                <p class="card-text" id="totalRevenue">$0.00</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-products">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-boxes"></i>
                </div>
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
                        <b><h1 class="card-title">Low Stock Alerts</h1></b>
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
     <div id="product-catalog" class="section">
            <div class="card card-catalog mb-4">
                <div class="card-body">
                    <h2 class="card-title">Product Catalog</h2>
                    <div class="search-filter-container row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="search-container input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="productSearch" class="form-control" placeholder="Search by product name..." aria-label="Search products">
                                <button class="btn btn-primary" id="searchProductsBtn" aria-label="Search products">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <div id="searchResults" class="search-results"></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select id="productCategoryFilter" class="form-select" aria-label="Filter by category">
                                <option value="">All Categories</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, name FROM categories");
                                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select id="productSort" class="form-select sort-select" aria-label="Sort products">
                                <option value="">Sort By</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="quantity_asc">Stock: Low to High</option>
                                <option value="quantity_desc">Stock: High to Low</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <button class="btn btn-outline-secondary w-100" id="resetFiltersBtn" aria-label="Reset filters">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="row" id="productGrid">
                        <!-- Dynamically populated by fetchProducts() -->
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
                                    <div class="customer-search-container">
                                        <div class="input-group mb-3">
                                            <input type="text" id="cartCustomerSearch" class="form-control" placeholder="Search customer by name, email or phone">
                                            <button class="btn btn-outline-secondary" type="button" id="searchCustomerBtn">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <div id="customerSearchResults" class="list-group"></div>
                                    </div>
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
            <th width="10%">Action</th>
        </tr>
      </thead>
     <tbody id="cartItems">
        <tr>
            <td colspan="5" class="text-center py-5">Your cart is empty</td>
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
  <div class="card mb-4 w-100"> <!-- Added w-100 -->
    <div class="card-body p-0"> <!-- Removed padding -->
      <h2 class="card-title p-3">Sales Forecasting</h2> <!-- Added padding to title -->
      <div class="row mb-3 px-3"> <!-- Added horizontal padding -->
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
      <div class="chart-container px-3" style="position: relative; height:400px; width:calc(100% - 1.5rem)"> <!-- Adjusted width -->
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

       <!---------Inventory--------->
           
<div id="inventory" class="section">
    <div class="card card-inventory mb-4">
        <div class="card-body">
            <h2 class="card-title">Inventory</h2>
            <div class="search-filter-container d-flex mb-3 flex-wrap gap-2">
                <div class="input-group search-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="inventorySearch" class="form-control" placeholder="Search by name or category..." aria-label="Search inventory">
                </div>
                <select id="inventoryCategoryFilter" class="form-select" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM categories");
                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                    }
                    ?>
                </select>
                <button class="btn btn-primary btn-add-product" data-bs-toggle="modal" data-bs-target="#productModal" onclick="prepareAddProduct()" aria-label="Add new product">
                    <i class="fas fa-plus me-1"></i>Add Product
                </button>
            </div>
            <div class="table-responsive-flex table-inventory" role="region" aria-label="Inventory Table">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Image</th>
                            <th scope="col">Name</th>
                            <th scope="col">Company</th>
                            <th scope="col">Model</th>
                            <th scope="col">Category</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Barcode</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTable">
                        <!-- Sample row for testing; populated dynamically -->
                        <tr>
                            <td>1001</td>
                            <td><img src="POS/uploads/sample.jpg" alt="Sample Product" class="product-img"></td>
                            <td>Sample Product</td>
                            <td>Sample Co.</td>
                            <td>Model X</td>
                            <td>Electronics</td>
                            <td>$199.99</td>
                            <td class="stock-status in-stock">50</td>
                            <td>123456789012</td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm btn-edit" aria-label="Edit product"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-sm btn-delete" aria-label="Delete product"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



       <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="productForm">
                            <input type="hidden" id="productId" name="id">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="productName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="productName" name="name" required aria-label="Product name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productCategory" class="form-label">Category</label>
                                    <select class="form-select" id="productCategory" name="category_id" required aria-label="Product category">
                                        <option value="">Select Category</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
                                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productCompany" class="form-label">Company</label>
                                    <input type="text" class="form-control" id="productCompany" name="company" aria-label="Product company">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productModel" class="form-label">Model</label>
                                    <input type="text" class="form-control" id="productModel" name="model" aria-label="Product model">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productPrice" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" id="productPrice" name="price" min="0" required aria-label="Product price">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productQuantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="productQuantity" name="quantity" min="0" required aria-label="Product quantity">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productBarcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="productBarcode" name="barcode" aria-label="Product barcode">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="productImage" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="productImage" name="image" accept="image/*" aria-label="Upload product image">
                                    <small class="form-text">Accepted: JPG, JPEG, PNG</small>
                                    <div id="imagePreview" class="mt-3">
                                        <img id="previewImage" src="" alt="Image preview" class="preview-img">
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="productDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="productDescription" name="description" rows="4" aria-label="Product description"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-save-product">Save Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

                


        <!-- Discounts Section -->
        <div id="discounts" class="section">
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
                                <input type="hidden" id="discountId" name="id">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="mb-3">
                        <label for="discountName" class="form-label">Discount Name</label>
                        <input type="text" class="form-control" id="discountName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="discountType" class="form-label">Discount Type</label>
                        <select class="form-control" id="discountType" name="type" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="discountValue" class="form-label">Discount Value</label>
                        <input type="number" step="0.01" class="form-control" id="discountValue" name="value" required>
                    </div>
                    <div class="mb-3">
                        <label for="discountMinPurchase" class="form-label">Minimum Purchase Amount (Optional)</label>
                        <input type="number" step="0.01" class="form-control" id="discountMinPurchase" name="min_purchase_amount">
                    </div>
                    <div class="mb-3">
                        <label for="discountProduct" class="form-label">Specific Product (Optional)</label>
                        <select class="form-control" id="discountProduct" name="product_id">
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
                        <select class="form-control" id="discountCategory" name="category_id">
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
                        <input type="datetime-local" class="form-control" id="discountStartDate" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="discountEndDate" class="form-label">End Date</label>
                        <input type="datetime-local" class="form-control" id="discountEndDate" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="discountIsActive" class="form-label">Active</label>
                        <input type="checkbox" class="form-check-input" id="discountIsActive" name="is_active" checked>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Discount</button>
                            </form>
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title">Stock Adjustment History</h2>
                            <button id="addAdjustmentBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">Add Adjustment</button>
                        </div>
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
                                        <th>Type</th>
                                        <th>Reason</th> 
                                        <th>Date</th>
                                        <th>Adjusted By</th>     
                                    
                                        
                                    </tr>
                                </thead>
                                <tbody id="adjustmentTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Adjustment Modal -->
            <div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="stockAdjustmentModalLabel">Add Stock Adjustment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="stockAdjustmentForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" id="adjustmentProductId" name="product_id">
                                <div class="mb-3 position-relative">
                                    <label for="adjustmentProductSearch" class="form-label">Product</label>
                                    <input type="text" class="form-control" id="adjustmentProductSearch" placeholder="Search product..." required>
                                    <div id="adjustmentProductSuggestions" class="autocomplete-suggestions" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="adjustmentType" class="form-label">Adjustment Type</label>
                                    <select class="form-control" id="adjustmentType" name="adjustment_type" required>
                                        <option value="ADD">Add</option>
                                        <option value="SUBTRACT">Subtract</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="adjustmentQuantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="adjustmentQuantity" name="quantity" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="adjustmentReason" class="form-label">Reason</label>
                                    <textarea class="form-control" id="adjustmentReason" name="reason" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Adjustment</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    </div>
</main>
 


   
  
</body>
</html>