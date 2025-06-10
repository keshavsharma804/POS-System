<?php
require_once 'config.php';
if (!isClient() || !isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('Location: client_login.php');
    exit;
}
$csrf_token = getCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <link href="css/login.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <style>
        body { font-family: 'Open Sans', Arial, sans-serif; }
        .error { color: red; }
        .success { color: green; }
        #barcode-scanner { display: none; width: 100%; height: 300px; }
        #scanner-container { margin-top: 20px; }
        .loading, .spinner { display: none; }
        .spinner-border { color: #007bff; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        #productResults { max-height: 200px; overflow-y: auto; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { display: none; }
        .section.active { display: block; }
        .nav-link { cursor: pointer; }
        body.dark-mode {
    background-color: #1a1a1a;
    color: #ffffff;
}
body.dark-mode .card,
body.dark-mode .navbar,
body.dark-mode .table,
body.dark-mode .list-group-item {
    background-color: #2c2c2c;
    color: #ffffff;
}
body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
    background-color: #333333;
}
body.dark-mode .nav-link,
body.dark-mode .navbar-brand {
    color: #ffffff !important;
}
body.dark-mode .btn-primary {
    background-color: #0056b3;
    border-color: #0056b3;
}
body.dark-mode .alert-success {
    background-color: #1a3c34;
    color: #d4edda;
}
body.dark-mode .alert-danger {
    background-color: #5a1a1a;
    color: #f8d7da;
}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">POS Client</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" data-section="process-sale">Process Sale</a></li>
                <li class="nav-item"><a class="nav-link" data-section="purchase-history">Purchase History</a></li>
                <li class="nav-item"><a class="nav-link" data-section="barcode-scanner">Barcode Scanner</a></li>
                <li class="nav-item"><a class="nav-link" data-section="wishlist">Wishlist</a></li>
                <li class="nav-item">
                    <button id="themeToggle" class="btn btn-link nav-link">Toggle Theme</button>
                </li>
                
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
    <div class="container mt-4">
    <h1>Client Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>

    <!-- Alert Message -->
    <div id="alertMessage" class="alert d-none"></div>

    <!-- Process Sale Section -->
    <div id="process-sale" class="section active">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Process Sale</h2>
                <form id="saleForm" class="mb-4">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="client_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="product_search" class="form-label">Search Product:</label>
                            <div class="input-group">
                                <input type="text" id="product_search" class="form-control" placeholder="Enter 3+ letters for product name or barcode">
                                <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                            </div>
                            <div id="searchSpinner" class="spinner-border spinner mt-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div id="productResults" class="list-group mt-2"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="product_id" class="form-label">Product:</label>
                            <select id="product_id" name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                            </select>
                            <span id="product_loading" class="loading">Loading products...</span>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="quantity" class="form-label">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2 mb-3 align-self-end">
                            <button type="button" id="addToCartBtn" class="btn btn-primary">Add to Cart</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cart Section -->
    <div id="cart-section" class="section active">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Cart (Total: $<span id="cartTotal">0.00</span>)</h2>
                <button id="clearCartBtn" class="btn btn-danger mb-3">Clear Cart</button>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartTable"></tbody>
                    </table>
                </div>
                <button id="checkoutBtn" class="btn btn-primary mt-3" disabled>Checkout</button>
            </div>
        </div>
    </div>

    <!-- Purchase History Section -->
    <div id="purchase-history" class="section">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Purchase History</h2>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Category</th>
                                <th>Barcode</th>
                                <th>Sale Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="salesTable">
                            <tr><td colspan="9" class="text-center">Loading purchase history...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Scanner Section -->
    <div id="barcode-scanner" class="section">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Barcode Scanner</h2>
                <button id="toggleScanner" class="btn btn-primary mb-3">Start Scanner</button>
                <div id="scanner-container">
                    <div id="barcode-scanner"></div>
                    <div id="scanResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Section -->
<div id="wishlist" class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Wishlist</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="wishlistTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const saleForm = document.getElementById('saleForm');
        const productSearch = document.getElementById('product_search');
        const searchBtn = document.getElementById('searchBtn');
        const productSelect = document.getElementById('product_id');
        const productLoading = document.getElementById('product_loading');
        const searchSpinner = document.getElementById('searchSpinner');
        const productResults = document.getElementById('productResults');
        const cartTable = document.getElementById('cartTable');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const salesTable = document.getElementById('salesTable');
        const toggleScanner = document.getElementById('toggleScanner');
        const barcodeScanner = document.getElementById('barcode-scanner');
        const scanResult = document.getElementById('scanResult');
        let csrfToken = document.getElementById('csrf_token').value;
        let scannerActive = false;
        let searchTimeout;

        function logError(message) {
            console.error(message);
            fetch('api.php?action=log_error', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message, client_id: '<?php echo $_SESSION['user_id']; ?>' })
            });
        }

        function fetchProducts(search = '') {
            productLoading.style.display = 'inline';
            fetch(`api.php?action=get_available_products&search=${encodeURIComponent(search)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('fetchProducts data:', data); // Debug
                    productSelect.innerHTML = '<option value="">Select Product</option>';
                    if (!data || data.length === 0) {
                        productSelect.innerHTML += '<option value="">No available products</option>';
                    } else {
                        data.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = `${product.name} ($${parseFloat(product.price).toFixed(2)}, ${product.quantity} in stock)`;
                            option.dataset.quantity = product.quantity;
                            option.dataset.price = product.price;
                            productSelect.appendChild(option);
                        });
                    }
                    productLoading.style.display = 'none';
                })
                .catch(error => {
                    logError('Error fetching products: ' + error.message);
                    alert('Error fetching products: ' + error.message);
                    productLoading.style.display = 'none';
                });
        }

        function searchProducts(query) {
    if (query.length < 2) {
        productResults.innerHTML = '';
        searchSpinner.style.display = 'none';
        return;
    }
    searchSpinner.style.display = 'inline-block';
    productResults.innerHTML = '';
    fetch(`api.php?action=search_products&query=${encodeURIComponent(query)}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('searchProducts data:', data);
            productResults.innerHTML = '';
            if (data.success && data.products && data.products.length > 0) {
                data.products.forEach(product => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex align-items-center';
                                        item.innerHTML = `
                        <img src="${product.image_url || 'https://via.placeholder.com/50'}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                        <div class="flex-grow-1">
                            <span>${product.name} ($${parseFloat(product.price).toFixed(2)}, ${product.quantity} in stock)<br><small>${product.description || 'No description'}</small></span>
                        </div>
                        <button class="btn btn-sm btn-primary select-product me-2" data-id="${product.id}">Select</button>
                        <button class="btn btn-sm btn-outline-secondary add-to-wishlist" data-id="${product.id}">Add to Wishlist</button>
                    `;
                    productResults.appendChild(item);
                });
            } else {
                productResults.innerHTML = '<div class="list-group-item">No products found</div>';
            }
        })
        .catch(error => {
            logError('Error searching products: ' + error.message);
            productResults.innerHTML = '<div class="list-group-item error">Error searching products: ' + error.message + '</div>';
        })
        .finally(() => {
            searchSpinner.style.display = 'none';
        }); 
    }

        function selectProduct(productId) {
            console.log('selectProduct:', productId); // Debug
            fetchProducts(); // Refresh dropdown
            setTimeout(() => {
                productSelect.value = productId;
                if (!productSelect.value) {
                    alert('Product not found in dropdown. Please try again.');
                }
                productResults.innerHTML = '';
                productSearch.value = '';
            }, 500);
        }

        function fetchSales() {
            salesTable.innerHTML = '<tr><td colspan="9" class="text-center">Loading purchase history...</td></tr>';
            fetch(`api.php?action=get_detailed_sales_report&client_id=<?php echo $_SESSION['user_id']; ?>`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('fetchSales data:', data); // Debug
                    salesTable.innerHTML = '';
                    if (!data || data.length === 0) {
                        salesTable.innerHTML = '<tr><td colspan="9">No purchase history.</td></tr>';
                    } else {
                        data.forEach(sale => {
                            const price = parseFloat(sale.price) || 0;
                            const total = parseFloat(sale.total) || 0;
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${sale.sale_id || 'N/A'}</td>
                                <td>${sale.product_name || 'Unknown Product'}</td>
                                <td>$${price.toFixed(2)}</td>
                                <td>${sale.quantity || 0}</td>
                                <td>$${total.toFixed(2)}</td>
                                <td>${sale.category_name || 'N/A'}</td>
                                <td>${sale.barcode || 'N/A'}</td>
                                <td>${sale.sale_date || 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="generateInvoice(${sale.sale_id})"><i class="fas fa-file-pdf"></i> Invoice</button>
                                </td>
                            `;
                            salesTable.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    logError('Error fetching sales: ' + error.message);
                    salesTable.innerHTML = '<tr><td colspan="9" class="error">Error loading purchase history: ' + error.message + '</td></tr>';
                });
        }

        function generateInvoice(sale_id) {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ action: 'generate_invoice', sale_id: sale_id, csrf_token: csrfToken })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => { throw new Error(data.message || `HTTP ${response.status}`); });
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `INV-${sale_id}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    showAlert('Invoice generated successfully!', 'success');
                    refreshCsrfToken();
                })
                .catch(error => {
                    logError('Error generating invoice: ' + error.message);
                    alert('Error generating invoice: ' + error.message);
                });
        }

        function refreshCsrfToken() {
            return fetch('api.php?action=get_new_csrf_token')
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    if (data.csrf_token) {
                        csrfToken = data.csrf_token;
                        document.getElementById('csrf_token').value = data.csrf_token;
                        console.log('CSRF token refreshed:', csrfToken); // Debug
                        return csrfToken;
                    }
                    throw new Error('No CSRF token returned');
                })
                .catch(error => {
                    logError('Error refreshing CSRF token: ' + error.message);
                    alert('Session expired. Please log in again.');
                    window.location.href = 'client_login.php';
                });
        }

        function addToCart() {
            const productId = productSelect.value;
            const quantity = parseInt(document.getElementById('quantity').value);
            const maxQuantity = parseInt(productSelect.selectedOptions[0].dataset.quantity) || 0;
            const price = parseFloat(productSelect.selectedOptions[0].dataset.price) || 0;
            const name = productSelect.selectedOptions[0].textContent.split(' (')[0];

            if (!productId || quantity <= 0) {
                alert('Please select a product and enter a valid quantity.');
                return false;
            }
            if (quantity > maxQuantity) {
                alert(`Cannot add to cart: Requested quantity (${quantity}) exceeds available stock (${maxQuantity}).`);
                return false;
            }

            const existingRow = Array.from(cartTable.querySelectorAll('tr')).find(row => row.dataset.id === String(productId));
            if (existingRow) {
                const currentQty = parseInt(existingRow.querySelector('.qty').value);
                const newQty = currentQty + quantity;
                if (newQty > maxQuantity) {
                    alert(`Cannot add ${quantity} more. Total (${newQty}) exceeds stock (${maxQuantity}).`);
                    return false;
                }
                existingRow.querySelector('.qty').value = newQty;
                existingRow.querySelector('.total').textContent = `$${parseFloat(newQty * price).toFixed(2)}`;
            } else {
                const row = document.createElement('tr');
                row.dataset.id = String(productId);
                row.innerHTML = `
                    <td>${name}</td>
                    <td>$${parseFloat(price).toFixed(2)}</td>
                    <td><input type="number" class="form-control qty" value="${quantity}" min="1" max="${maxQuantity}"></td>
                    <td class="total">$${parseFloat(quantity * price).toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm remove-btn">Remove</button></td>
                `;
                cartTable.appendChild(row);
            }
            console.log('Cart table rows after add:', cartTable.querySelectorAll('tr').length); // Debug
            updateCheckoutButton();
            saveCart();
            saleForm.reset();
            productResults.innerHTML = '';
            updateCartTotal();
            showAlert('Product added to cart!', 'success');
            return true;
        }

        function saveCart() {
            const items = Array.from(cartTable.querySelectorAll('tr')).map(row => ({
                product_id: row.dataset.id,
                quantity: parseInt(row.querySelector('.qty').value),
                price: parseFloat(row.cells[1].textContent.replace('$', '')),
                name: row.cells[0].textContent
            }));
            sessionStorage.setItem('cartItems', JSON.stringify(items));
        }

        function loadCart() {
            const savedCart = sessionStorage.getItem('cartItems');
            console.log('Loading cart from sessionStorage:', savedCart); // Debug
            if (savedCart) {
                try {
                    const items = JSON.parse(savedCart);
                    console.log('Parsed cart items:', items); // Debug
                    if (!Array.isArray(items)) {
                        console.error('Invalid cart items format:', items);
                        sessionStorage.removeItem('cartItems');
                        return;
                    }
                    items.forEach(item => {
                        if (!item.product_id || !item.quantity || !item.price || !item.name) {
                            console.error('Invalid cart item:', item);
                            return;
                        }
                        const row = document.createElement('tr');
                        row.dataset.id = String(item.product_id);
                        row.innerHTML = `
                            <td>${item.name}</td>
                            <td>$${parseFloat(item.price).toFixed(2)}</td>
                            <td><input type="number" class="form-control qty" value="${item.quantity}" min="1"></td>
                            <td class="total">$${parseFloat(item.quantity * item.price).toFixed(2)}</td>
                            <td><button class="btn btn-danger btn-sm remove-btn">Remove</button></td>
                        `;
                        cartTable.appendChild(row);
                    });
                    console.log('Cart table rows after load:', cartTable.querySelectorAll('tr').length); // Debug
                    updateCheckoutButton();
                    updateCartTotal();
                } catch (e) {
                    console.error('Error parsing cart items:', e);
                    sessionStorage.removeItem('cartItems');
                }
            }
        }

        function updateCheckoutButton() {
            
            checkoutBtn.disabled = cartTable.querySelectorAll('tr').length === 0;
        }

        saleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            addToCart();
        });

        addToCartBtn.addEventListener('click', addToCart);

        cartTable.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('tr').remove();
                saveCart();
                updateCartTotal();
                updateCheckoutButton();
            }
        });

        cartTable.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty')) {
                const row = e.target.closest('tr');
                const maxQuantity = parseInt(productSelect.querySelector(`option[value="${row.dataset.id}"]`)?.dataset.quantity || 0);
                const quantity = parseInt(e.target.value);
                const price = parseFloat(row.cells[1].textContent.replace('$', ''));
                if (quantity > maxQuantity) {
                    alert(`Quantity cannot exceed available stock (${maxQuantity}).`);
                    e.target.value = maxQuantity;
                }
                row.querySelector('.total').textContent = `$${parseFloat(quantity * price).toFixed(2)}`;
                saveCart();
            }
        });

        productSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => searchProducts(this.value), 500);
        });

        searchBtn.addEventListener('click', () => searchProducts(productSearch.value));

        productResults.addEventListener('click', function(e) {
            if (e.target.classList.contains('select-product')) {
                e.preventDefault();
                const productId = e.target.dataset.id;
                selectProduct(productId);
            }
        });

        checkoutBtn.addEventListener('click', async () => {
            try {
                const rows = Array.from(cartTable.querySelectorAll('tr'));
                console.log('Raw cart table rows:', rows.map(row => ({
                    datasetId: row.dataset.id,
                    qtyValue: row.querySelector('.qty')?.value
                }))); // Debug

                // Validate rows before mapping
                if (rows.length === 0) {
                    console.warn('No rows found in cart table');
                    alert('Cart is empty.');
                    return;
                }

                const items = rows
                    .map((row, index) => {
                        const id = parseInt(row.dataset.id);
                        const quantity = parseInt(row.querySelector('.qty').value);
                        console.log(`Row ${index}: id=${id}, quantity=${quantity}`); // Debug
                        return { id, quantity };
                    })
                    .filter(item => !isNaN(item.id) && item.id > 0 && !isNaN(item.quantity) && item.quantity > 0);

                console.log('Cart items for checkout:', items); // Debug
                if (items.length === 0) {
                    console.warn('No valid items after filtering');
                    alert('Cart is empty or contains invalid items.');
                    return;
                }

                await refreshCsrfToken();
                const clientId = parseInt('<?php echo $_SESSION['user_id']; ?>');
                console.log('Client ID:', clientId); // Debug
                if (clientId <= 0) {
                    alert('Session error: Invalid client ID. Please log in again.');
                    window.location.href = 'client_login.php';
                    return;
                }

                const payload = {
                    items: items,
                    client_id: clientId,
                    csrf_token: csrfToken
                };
                console.log('Checkout payload:', JSON.stringify(payload)); // Debug

                const response = await fetch('api.php?action=checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                console.log('Checkout response:', data); // Debug
                if (data.success) {
                    alert('Checkout successful!');
                    cartTable.innerHTML = '';
                    sessionStorage.removeItem('cartItems');
                    fetchProducts();
                    fetchSales();
                    await refreshCsrfToken();
                    showAlert('Checkout successful!', 'success');
                    updateCheckoutButton();
                } else {
                    throw new Error(data.message || 'Checkout failed');
                }
            } catch (error) {
                logError('Checkout error: ' + error.message);
                alert('Checkout error: ' + error.message);
                if (error.message.includes('CSRF') || error.message.includes('Unauthorized')) {
                    window.location.href = 'client_login.php';
                }
            }
        });

        toggleScanner.addEventListener('click', function() {
            if (!scannerActive) {
                barcodeScanner.style.display = 'block';
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#barcode-scanner'),
                        constraints: { width: 640, height: 480, facingMode: "environment" }
                    },
                    decoder: { readers: ['ean_reader', 'upc_reader', 'code_128_reader'] }
                }, function(err) {
                    if (err) {
                        logError('Quagga init error: ' + err.message);
                        scanResult.innerHTML = '<p class="error">Error initializing scanner: ' + err.message + '</p>';
                        barcodeScanner.style.display = 'none';
                        toggleScanner.textContent = 'Start Scanner';
                        return;
                    }
                    Quagga.start();
                    scannerActive = true;
                    toggleScanner.textContent = 'Stop Scanner';
                });
                            Quagga.onDetected(function(result) {
                const barcode = result.codeResult.code;
                fetch(`api.php?action=get_product_by_barcode&barcode=${encodeURIComponent(barcode)}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Barcode scan data:', data);
                        if (data.status === 'success') {
                            const p = data.product;
                            scanResult.innerHTML = `
                                <p class="success">Product Found!</p>
                                <p>ID: ${p.id}</p>
                                <p>Name: ${p.name}</p>
                                <p>Price: $${parseFloat(p.price).toFixed(2)}</p>
                                <p>Quantity: ${p.quantity}</p>
                                <p>Category: ${p.category_name || ''}</p>
                                <p>Barcode: ${p.barcode}</p>
                                <p>Description: ${p.description || 'N/A'}</p>
                            `;
                            if (p.quantity > 0) {
                                selectProduct(p.id);
                                if (addToCart()) {
                                    showAlert(`Added ${p.name} to cart!`, 'success');
                                }
                            } else {
                                scanResult.innerHTML += '<p class="error">Product out of stock.</p>';
                                showAlert('Product out of stock.', 'danger');
                            }
                        } else {
                            scanResult.innerHTML = '<p class="error">' + data.message + '</p>';
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        logError('Error fetching product by barcode: ' + error.message);
                        scanResult.innerHTML = '<p class="error">Error fetching product: ' + error.message + '</p>';
                        showAlert('Error fetching product: ' + error.message, 'danger');
                    });
            });
            } else {
                Quagga.stop();
                barcodeScanner.style.display = 'none';
                scannerActive = false;
                toggleScanner.textContent = 'Start Scanner';
                scanResult.innerHTML = '';
            }
        });

        // Navigation Toggle
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const sections = document.querySelectorAll('.section');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    sections.forEach(s => s.classList.remove('active'));
                    const sectionId = link.dataset.section;
                    document.getElementById(sectionId)?.classList.add('active');
                    if (sectionId === 'cart-section') {
                        updateCartTotal();
                    }
                });
            });

                    function updateCartTotal() {
    fetch('api.php?action=get_active_discounts')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(discounts => {
            if (!Array.isArray(discounts)) discounts = [];
            let total = 0;
            const rows = Array.from(cartTable.querySelectorAll('tr'));
            rows.forEach(row => {
                const price = parseFloat(row.cells[1].textContent.replace('$', ''));
                const quantity = parseInt(row.querySelector('.qty').value);
                let discountAmount = 0;
                const productId = row.dataset.id;
                const discount = discounts.find(d => d.product_id == productId || (!d.product_id && !d.category_id));
                if (discount) {
                    if (discount.type === 'percentage') {
                        discountAmount = (price * discount.value) / 100;
                    } else {
                        discountAmount = parseFloat(discount.value);
                    }
                    discountAmount = Math.min(discountAmount, price);
                    row.cells[1].innerHTML = `
                        $${(price - discountAmount).toFixed(2)}
                        ${discount ? `<br><small class="text-success">${discount.name} (${discount.type === 'percentage' ? discount.value + '%' : '$' + discount.value})</small>` : ''}
                    `;
                }
                const itemTotal = (price - discountAmount) * quantity;
                row.querySelector('.total').textContent = `$${itemTotal.toFixed(2)}`;
                total += itemTotal;
            });
            document.getElementById('cartTotal').textContent = total.toFixed(2);
        })
        .catch(error => {
            logError('Error fetching discounts: ' + error.message);
            const total = Array.from(cartTable.querySelectorAll('tr')).reduce((sum, row) => {
                return sum + parseFloat(row.querySelector('.total').textContent.replace('$', ''));
            }, 0);
            document.getElementById('cartTotal').textContent = total.toFixed(2);
        });
}

                    document.getElementById('clearCartBtn').addEventListener('click', () => {
                        cartTable.innerHTML = '';
                        sessionStorage.removeItem('cartItems');
                        updateCheckoutButton();
                        updateCartTotal();
                        showAlert('Cart cleared successfully.', 'success');
                    });

                            function showAlert(message, type) {
                    const alert = document.getElementById('alertMessage');
                    alert.textContent = message;
                    alert.className = `alert alert-${type}`;
                    alert.classList.remove('d-none');
                    setTimeout(() => alert.classList.add('d-none'), 3000);
                }
        // Wishlist Functionality
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

function loadWishlist() {
    const wishlistTable = document.getElementById('wishlistTable');
    wishlistTable.innerHTML = '';
    wishlist.forEach(item => {
        const row = document.createElement('tr');
        row.dataset.id = item.id;
        row.innerHTML = `
            <td>${item.name}</td>
            <td>$${parseFloat(item.price).toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-primary add-to-cart-wishlist me-2" data-id="${item.id}">Add to Cart</button>
                <button class="btn btn-sm btn-danger remove-wishlist" data-id="${item.id}">Remove</button>
            </td>
        `;
        wishlistTable.appendChild(row);
    });
}

// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    document.body.classList.add('dark-mode');
}
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    const newTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    localStorage.setItem('theme', newTheme);
});

                        function saveWishlist() {
                            localStorage.setItem('wishlist', JSON.stringify(wishlist));
                        }

                        productResults.addEventListener('click', function(e) {
                            if (e.target.classList.contains('add-to-wishlist')) {
                                const productId = e.target.dataset.id;
                                fetch(`api.php?action=get_product_by_id&id=${productId}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            const product = data.product;
                                            if (!wishlist.find(item => item.id == productId)) {
                                                wishlist.push({ id: product.id, name: product.name, price: product.price });
                                                saveWishlist();
                                                loadWishlist();
                                                showAlert(`${product.name} added to wishlist!`, 'success');
                                            } else {
                                                showAlert('Product already in wishlist.', 'warning');
                                            }
                                        }
                                    });
                            }
                        });

                        document.getElementById('wishlistTable').addEventListener('click', function(e) {
                            const productId = e.target.dataset.id;
                            if (e.target.classList.contains('remove-wishlist')) {
                                wishlist = wishlist.filter(item => item.id != productId);
                                saveWishlist();
                                loadWishlist();
                                showAlert('Product removed from wishlist.', 'success');
                            } else if (e.target.classList.contains('add-to-cart-wishlist')) {
                                selectProduct(productId);
                                if (addToCart()) {
                                    wishlist = wishlist.filter(item => item.id != productId);
                                    saveWishlist();
                                    loadWishlist();
                                }
                            }
                        });

            // Initial load
            loadWishlist();

        // Initial load
        fetchProducts();
        fetchSales();
        loadCart();
    </script>
</body>
</html>