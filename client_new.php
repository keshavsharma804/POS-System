<?php
require 'config.php';

if (!isClient()) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="/POS/css/login.min.css" rel="stylesheet">
    <style>
        .spinner { display: none; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Client Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>! <a href="logout.php">Logout</a></p>
        
        <div class="mb-3">
            <label for="productSearch" class="form-label">Search Products:</label>
            <input type="text" id="productSearch" class="form-control" placeholder="Enter product name or barcode">
            <div id="searchSpinner" class="spinner-border spinner mt-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div id="productResults" class="list-group mt-2"></div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cartTable"></tbody>
            </table>
        </div>
        <button id="checkoutBtn" class="btn btn-primary mt-3">Checkout</button>
    </div>

    <script>
        let searchTimeout;
        const searchInput = document.getElementById('productSearch');
        const searchSpinner = document.getElementById('searchSpinner');
        const productResults = document.getElementById('productResults');
        const cartTable = document.getElementById('cartTable');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const csrfToken = '<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>';

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchProducts(searchInput.value);
            }, 500);
        });

        async function searchProducts(query) {
            searchSpinner.style.display = 'inline-block';
            productResults.innerHTML = '';
            try {
                const response = await fetch(`api.php?action=search_products&query=${encodeURIComponent(query)}`, {
                    headers: { 'X-CSRF-Token': csrfToken }
                });
                const data = await response.json();
                if (data.success) {
                    data.products.forEach(product => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item';
                        item.innerHTML = `${product.name} ($${product.price}) <button class="btn btn-sm btn-primary" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">Add</button>`;
                        productResults.appendChild(item);
                    });
                }
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                searchSpinner.style.display = 'none';
            }
        }

        function addToCart(id, name, price) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${name}</td>
                <td>$${price}</td>
                <td><input type="number" class="form-control qty" value="1" min="1"></td>
                <td><button class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
            `;
            cartTable.appendChild(row);
        }

        checkoutBtn.addEventListener('click', async () => {
            const items = Array.from(cartTable.querySelectorAll('tr')).map(row => ({
                id: row.querySelector('button').dataset.id,
                quantity: parseInt(row.querySelector('.qty').value)
            }));
            try {
                const response = await fetch('api.php?action=checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                    body: JSON.stringify({ items })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Checkout successful!');
                    cartTable.innerHTML = '';
                }
            } catch (error) {
                console.error('Checkout error:', error);
            }
        });
    </script>
</body>
</html>