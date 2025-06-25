<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="#">
            <img src="uploads/8572086.png" alt="QuickStock Logo" class="sidebar-logo">
            <span class="sidebar-title">QuickStock</span>
        </a>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a class="sidebar-link active" data-section="dashboard" href="#dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="sales-forecasting" href="#sales-forecasting">
                <i class="fas fa-chart-line"></i>
                <span>Forecasting</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="product-catalog" href="#product-catalog">
                <i class="fas fa-box-open"></i>
                <span>Products</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="sales-report" href="#sales-report">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="most-sold-products" href="#most-sold-products">
                <i class="fas fa-star"></i>
                <span>Top Products</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="inventory" href="#inventory">
                <i class="fas fa-warehouse"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="discounts" href="#discounts">
                <i class="fas fa-tag"></i>
                <span>Discounts</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="customers" href="#customers">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="stock_adjustments" href="#stock_adjustments">
                <i class="fas fa-sliders-h"></i>
                <span>Stock Adjust</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" data-section="cart-section" href="#cart-section">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart <span id="cartItemCountBadge" class="badge rounded-pill bg-danger">0</span></span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="sidebar-dropdown">
            <a class="sidebar-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark/light mode">
            <i class="fas fa-moon"></i>
            <span>Dark Mode</span>
        </button>
    </div>
</nav>