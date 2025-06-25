
document.addEventListener('DOMContentLoaded', () => {



    
    
    // Sidebar collapse
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (!sidebar || !sidebarToggle) {
        console.error('Sidebar or sidebarToggle element not found');
        return;
    }
    sidebarToggle.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
        const isExpanded = !document.body.classList.contains('sidebar-collapsed');
        sidebarToggle.setAttribute('aria-expanded', isExpanded);
        console.log('Sidebar toggled, expanded:', isExpanded);
    });

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) {
        console.error('themeToggle element not found');
        return;
    }
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        const icon = themeToggle.querySelector('i');
        const span = themeToggle.querySelector('span');
        if (icon && span) {
            icon.classList.toggle('fa-moon', !isDarkMode);
            icon.classList.toggle('fa-sun', isDarkMode);
            span.textContent = isDarkMode ? 'Light Mode' : 'Dark Mode';
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
            console.log('Theme toggled, dark mode:', isDarkMode);
        } else {
            console.error('Theme toggle icon or span not found');
        }
    });

    // Restore theme from localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        const icon = themeToggle.querySelector('i');
        const span = themeToggle.querySelector('span');
        if (icon && span) {
            icon.classList.replace('fa-moon', 'fa-sun');
            span.textContent = 'Light Mode';
        }
    }

    // Navigation and section switching
    document.querySelectorAll('.sidebar-link[data-section]').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            console.log('Section link clicked:', sectionId);
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
                section.classList.remove('active');
            });
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.style.display = 'block';
                targetSection.classList.add('active');
            } else {
                console.error(`Section with ID ${sectionId} not found`);
            }
            document.querySelectorAll('.sidebar-link').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Logout debug
    document.querySelectorAll('.dropdown-item[href="logout.php"]').forEach(logoutLink => {
        logoutLink.addEventListener('click', function (e) {
            console.log('Logout link clicked, navigating to logout.php');
        });
    });

    // Ensure only active section is visible on page load
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = section.classList.contains('active') ? 'block' : 'none';
    });

    // Show More/Show Less button functionality
    document.querySelectorAll('.show-more-btn').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            if (targetId === 'inventory') return;
            const selector = targetId === 'dashboard-low-stock' ? '#dashboard .table-responsive' : `#${targetId} .table-responsive`;
            const tableResponsive = document.querySelector(selector);
            if (tableResponsive) {
                if (tableResponsive.style.maxHeight === '400px' || !tableResponsive.style.maxHeight) {
                    tableResponsive.style.maxHeight = 'none';
                    this.textContent = 'Show Less';
                    this.setAttribute('aria-expanded', 'true');
                } else {
                    tableResponsive.style.maxHeight = '400px';
                    this.textContent = 'Show More';
                    this.setAttribute('aria-expanded', 'false');
                }
                tableResponsive.scrollTop = 0;
                console.log(`Show More toggled for ${targetId}`);
            } else {
                console.error(`Table with selector ${selector} not found`);
            }
        });
    });

    // Initialize Bootstrap dropdowns
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        new bootstrap.Dropdown(toggle);
    });
});




document.addEventListener('DOMContentLoaded', () => {
    // Existing code (sidebar, theme toggle, etc.) ...

    // Inventory search/filter feedback
    const inventorySearch = document.getElementById('inventorySearch');
    const inventoryCategoryFilter = document.getElementById('inventoryCategoryFilter');
    const inventoryTable = document.getElementById('inventoryTable');

    if (inventorySearch && inventoryCategoryFilter && inventoryTable) {
        inventorySearch.addEventListener('input', () => {
            inventoryTable.classList.add('loading');
            // Placeholder: Simulate search (replace with actual AJAX/filter logic)
            setTimeout(() => {
                inventoryTable.classList.remove('loading');
                console.log('Search query:', inventorySearch.value);
            }, 500);
        });

        inventoryCategoryFilter.addEventListener('change', () => {
            inventoryTable.classList.add('loading');
            // Placeholder: Simulate filter (replace with actual AJAX/filter logic)
            setTimeout(() => {
                inventoryTable.classList.remove('loading');
                console.log('Category filter:', inventoryCategoryFilter.value);
            }, 500);
        });
    }

    // Action button placeholders
    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-edit')) {
            console.log('Edit clicked for product ID:', e.target.closest('tr').querySelector('td').textContent);
            // Replace with actual edit logic (e.g., open modal with product data)
        }
        if (e.target.closest('.btn-delete')) {
            console.log('Delete clicked for product ID:', e.target.closest('tr').querySelector('td').textContent);
            // Replace with actual delete logic (e.g., confirm dialog, AJAX delete)
        }
    });
});



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
  
    const selectedCustomerDiv = document.getElementById('selectedCustomerInfo'); // Changed from selectedCustomer

    const productSuggestions = document.getElementById('productSuggestions');
    const selectedProductDiv = document.getElementById('selectedProduct');
    // const quantityInput = document.getElementById('quantity');
    // const addToCartBtn = document.getElementById('addToCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    // const retryReceiptBtn = document.getElementById('retryReceiptBtn');
    // const paymentMethodsContainer = document.getElementById('paymentMethods'); // Changed from paymentMethodsContainer
    // const addPaymentMethodBtn = document.getElementById('addPaymentMethodBtn');
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
    // const paymentMethods = document.getElementById('paymentMethods');
    const totalPaid = document.getElementById('totalPaid');
    const remainingBalance = document.getElementById('remainingBalance');
    const productForm = document.getElementById('productForm');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productModalLabel = document.getElementById('productModalLabel');
    const productCompanyInput = document.getElementById('productCompany');
    const productModelInput = document.getElementById('productModel');
    const productImageInput = document.getElementById('productImage');
    const productDescriptionInput = document.getElementById('productDescription');
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
    // Stock Adjustment Functions
const stockAdjustmentModal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
const stockAdjustmentForm = document.getElementById('stockAdjustmentForm');
const adjustmentProductSearch = document.getElementById('adjustmentProductSearch');
const adjustmentProductSuggestions = document.getElementById('adjustmentProductSuggestions');
const adjustmentProductId = document.getElementById('adjustmentProductId');





adjustmentProductSearch.addEventListener('input', async () => {
    const term = adjustmentProductSearch.value.trim();
    adjustmentProductSuggestions.style.display = term.length >= 2 ? 'block' : 'none';
    if (term.length < 2) {
        adjustmentProductSuggestions.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`api.php?action=search_products&term=${encodeURIComponent(term)}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const products = await response.json();
        
        adjustmentProductSuggestions.innerHTML = '';
        if (Array.isArray(products) && products.length > 0) {
            adjustmentProductSuggestions.innerHTML = products.map(p => `<div class="autocomplete-item" data-id="${p.id}">${p.name}</div>`).join('');
            adjustmentProductSuggestions.querySelectorAll('.autocomplete-item').forEach(item => {
                item.addEventListener('click', () => {
                    adjustmentProductSearch.value = item.textContent;
                    adjustmentProductId.value = item.dataset.id;
                    adjustmentProductSuggestions.style.display = 'none';
                });
            });
            positionSuggestions(adjustmentProductSearch, adjustmentProductSuggestions);
        } else {
            adjustmentProductSuggestions.innerHTML = '<div class="autocomplete-item text-muted">No products found</div>';
            adjustmentProductSuggestions.style.display = 'block';
            positionSuggestions(adjustmentProductSearch, adjustmentProductSuggestions);
        }
    } catch (error) {
        console.error('Product search error:', error);
        adjustmentProductSuggestions.innerHTML = '<div class="autocomplete-item text-danger">Error loading suggestions</div>';
        adjustmentProductSuggestions.style.display = 'block';
        positionSuggestions(adjustmentProductSearch, adjustmentProductSuggestions);
    }
});

async function fetchStockAdjustments() {
    const search = document.getElementById('adjustmentSearch').value.trim();
    const startDate = document.getElementById('adjustmentStartDate').value;
    const endDate = document.getElementById('adjustmentEndDate').value;
    const productId = document.getElementById('adjustmentProductId').value || '';

    let url = 'api.php?action=get_stock_adjustments';
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (startDate) url += `&start_date=${encodeURIComponent(startDate)}`;
    if (endDate) url += `&end_date=${encodeURIComponent(endDate)}`;
    if (productId) url += `&product_id=${encodeURIComponent(productId)}`;

    try {
        const response = await fetch(url, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
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
                    <td>${adjustment.adjustment_type || 'N/A'}</td>
                    <td>${adjustment.reason || 'N/A'}</td>
                    <td>${adjustment.adjusted_at || 'N/A'}</td>
                    <td>${adjustment.username || 'N/A'}</td>

                    
                `;
                adjustmentTable.appendChild(row);
            });
        } else {
            adjustmentTable.innerHTML = '<tr><td colspan="8" class="text-center">No adjustments found</td></tr>';
        }
    } catch (error) {
        console.error('Error fetching stock adjustments:', error);
        adjustmentTable.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading adjustments</td></tr>';
    }
}




// Safer customer search event listener
const customerSearchBar = document.getElementById('cartCustomerSearch');
if (customerSearchBar) {
    customerSearchBar.addEventListener('focus', () => {
        const suggestions = document.getElementById('customerSearchResults');
        if (suggestions) {
            positionSuggestions(customerSearchBar, suggestions);
        }
    });
}

// Safer product search event listener
const productSearchBar = document.getElementById('productSearch');
if (productSearchBar) {
    productSearchBar.addEventListener('focus', () => {
        const suggestions = document.getElementById('searchResults');
        if (suggestions) {
            positionSuggestions(productSearchBar, suggestions);
        }
    });
}

// Safer window resize handler
window.addEventListener('resize', () => {
    const customerSearchBar = document.getElementById('cartCustomerSearch');
    const customerSuggestions = document.getElementById('customerSearchResults');
    const productSearchBar = document.getElementById('productSearch');
    const productSuggestions = document.getElementById('searchResults');
    
    if (customerSuggestions && customerSuggestions.style.display === 'block' && customerSearchBar) {
        positionSuggestions(customerSearchBar, customerSuggestions);
    }
    
    if (productSuggestions && productSuggestions.style.display === 'block' && productSearchBar) {
        positionSuggestions(productSearchBar, productSuggestions);
    }
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


async function updateCartTable() {
    try {
        const response = await fetch('api.php?action=get_cart', {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${await response.text()}`);
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Invalid response from server');

        // Convert server cart to array
        const cart = data.cart && typeof data.cart === 'object'
            ? Object.values(data.cart).map(item => ({
                  id: parseInt(item.id),
                  name: item.name || 'Unknown Product',
                  price: parseFloat(item.price) || 0,
                  quantity: parseInt(item.quantity) || 1,
                  maxQuantity: parseInt(item.maxQuantity) || Infinity,
                  image: item.image || 'https://via.placeholder.com/50'
              }))
            : [];

        cartItemsTable.innerHTML = '';
        if (cart.length === 0) {
            cartItemsTable.innerHTML = '<tr><td colspan="5" class="text-center py-5">Your cart is empty</td></tr>';
            checkoutBtn.disabled = true;
            cartSubtotal.textContent = '$0.00';
            cartDiscount.textContent = '$0.00';
            cartTax.textContent = '$0.00';
            cartTotal.textContent = '$0.00';
            return;
        }

        let subtotal = 0;
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            const row = document.createElement('tr');
            row.dataset.id = item.id;
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${item.image}" alt="${item.name}" class="me-2 cart-item-image" style="width: 50px; height: 50px;">
                        <span class="cart-item-name">${item.name}</span>
                    </div>
                </td>
                <td class="price">$${item.price.toFixed(2)}</td>
                <td>
                    <div class="input-group input-group-sm quantity-control">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <input type="number" class="form-control text-center quantity-input" value="${item.quantity}" min="1" max="${item.maxQuantity}" onchange="updateCartItemQuantity(${item.id}, this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                </td>
                <td class="total">$${itemTotal.toFixed(2)}</td>
                <td>
                    <span class="remove-item-btn" onclick="removeCartItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </span>
                </td>
            `;
            cartItemsTable.appendChild(row);
        });

        const tax = subtotal * TAX_RATE;
        const pointsToUse = parseInt(loyaltyPointsToUse.value) || 0;
        let discount = 0;
        if (pointsToUse > 0 && customerId) {
            const availablePoints = parseInt(selectedCustomerPoints.textContent.match(/\d+/)?.[0]) || 0;
            if (pointsToUse <= availablePoints) {
                discount = pointsToUse / 100; // 100 points = $1
            } else {
                showToast('Invalid points redemption.', 'error');
                loyaltyPointsToUse.value = '';
            }
        }
        const total = subtotal + tax - discount;

        cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
        cartDiscount.textContent = `$${discount.toFixed(2)}`;
        cartTax.textContent = `$${tax.toFixed(2)}`;
        cartTotal.textContent = `$${total.toFixed(2)}`;
        checkoutBtn.disabled = !customerId || cart.length === 0;

        updateCartBadge();
    } catch (error) {
        console.error('Error updating cart table:', error);
        cartItemsTable.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">Error loading cart</td></tr>';
        checkoutBtn.disabled = true;
    }
}

function updateCartSummary() {
    fetch('api.php?action=get_cart', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        const cart = data.cart || [];
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.05;
        const total = subtotal + tax;
        cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
        cartTax.textContent = `$${tax.toFixed(2)}`;
        cartTotal.textContent = `$${total.toFixed(2)}`;
        checkoutBtn.disabled = cart.length === 0 || !customerId;
    })
    .catch(error => {
        logError('Error updating cart summary: ' + error.message);
        cartSubtotal.textContent = '$0.00';
        cartTax.textContent = '$0.00';
        cartTotal.textContent = '$0.00';
    });
}

async function updateCartItemQuantity(productId, quantity) {
    try {
        if (quantity < 1) {
            await removeCartItem(productId);
            return;
        }
        const response = await fetch('api.php?action=update_cart_item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        const data = await response.json();
        if (data.status !== 'success') throw new Error(data.message || 'Failed to update cart item');
        await updateCartTable();
        updateCartBadge();
    } catch (error) {
        logError('Error updating cart item quantity: ' + error.message);
        alert('Error updating cart: ' + error.message);
    }
}

   async function removeCartItem(productId) {
    try {
        const response = await fetch(`api.php?action=remove_cart_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ product_id: productId })
        });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to remove cart item.');
        }
        await updateCartTable();
        updateCartBadge();
    } catch (error) {
        logError('Error removing cart item: ' + error.message);
        alert('Error removing cart: ' + error.message);
    }
}


async function clearCart() {
    try {
        const response = await fetch('api.php?action=clear_cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to clear cart');
        }
        cart = [];
        await updateCartTable();
        updateCartBadge();
        showToast('Cart cleared successfully!');
    } catch (error) {
        logError('Error clearing cart: ' + error.message);
        alert('Error clearing cart: ' + error.message);
    }
}


document.addEventListener('DOMContentLoaded', () => {
    cart = JSON.parse(localStorage.getItem('cart') || '[]');
    updateCartTable();
    updateCartBadge();
});


// Preload placeholder image (data URI for a 1x1 transparent pixel)
const placeholderImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

 const productSort = document.getElementById('productSort');
  


    async function fetchProducts(page = 1, pageSize = 10, search = '', categoryId = null, sort = '') {
        try {
            let url = `api.php?action=get_product_catalog&page=${page}&page_size=${pageSize}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (categoryId) url += `&category_id=${categoryId}`;
            if (sort) url += `&sort=${sort}`;
            const response = await fetch(url, {
                headers: { 'X-CSRF-Token': csrfToken }
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const data = await response.json();
            if (data.status !== 'success') throw new Error(data.message || 'Failed to fetch products.');

            const productGrid = document.getElementById('productGrid');
            productGrid.innerHTML = '';
            if (data.products.length === 0) {
                productGrid.innerHTML = '<div class="col-12 text-center py-5">No products found.</div>';
                document.getElementById('pagination-controls').innerHTML = '';
                return;
            }

            data.products.forEach(product => {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-4';
                col.innerHTML = `
                    <div class="card product-card h-100">
                        <div class="product-img-container">
                            <img src="${product.products_image || 'https://via.placeholder.com/150'}" alt="${product.name}" class="product-img">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="card-text">
                                Company: ${product.company || 'N/A'}<br>
                                Model: ${product.model || 'N/A'}<br>
                                Category: ${product.category_name || 'N/A'}<br>
                                Description: ${product.description ? product.description.substring(0, 60) + '...' : 'No description'}
                            </p>
                            <p class="product-stock ${product.quantity > 0 ? 'in-stock' : 'out-of-stock'}">
                                ${product.quantity > 0 ? `In Stock: ${product.quantity}` : 'Out of Stock'}
                            </p>
                            <div class="input-group mt-auto">
                                <span class="input-group-text">$${parseFloat(product.price || 0).toFixed(2)}</span>
                                <input type="number" class="form-control quantity-input" value="1" min="1" max="${product.quantity || 0}" ${product.quantity <= 0 ? 'disabled' : ''}>
                                <button class="btn btn-primary add-to-cart-btn" 
                                        data-id="${product.id}" 
                                        data-name="${product.name}" 
                                        data-price="${product.price || 0}" 
                                        data-quantity="${product.quantity || 0}" 
                                        ${product.quantity <= 0 ? 'disabled' : ''}>
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                productGrid.appendChild(col);
            });

            // Remove existing listeners to prevent duplicates
            const existingButtons = document.querySelectorAll('.add-to-cart-btn');
            existingButtons.forEach(button => {
                button.removeEventListener('click', handleAddToCart);
            });

            // Add new listeners
            const buttons = document.querySelectorAll('.add-to-cart-btn');
            buttons.forEach(button => {
                button.addEventListener('click', handleAddToCart);
            });

            // Render pagination
            const paginationControls = document.getElementById('pagination-controls');
            paginationControls.innerHTML = '';
            if (data.total_pages > 1) {
                const ul = document.createElement('ul');
                ul.className = 'pagination justify-content-center';
                ul.innerHTML += `
                    <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${page - 1}">Previous</a>
                    </li>
                `;
                for (let i = 1; i <= data.total_pages; i++) {
                    ul.innerHTML += `
                        <li class="page-item ${i === page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }
                ul.innerHTML += `
                    <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${page + 1}">Next</a>
                    </li>
                `;
                paginationControls.appendChild(ul);
                ul.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const newPage = parseInt(link.dataset.page);
                        if (newPage && newPage !== page) {
                            fetchProducts(newPage, pageSize, search, categoryId, sort);
                        }
                    });
                });
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            document.getElementById('productGrid').innerHTML = '<div class="col-12 text-center py-5 text-danger">Error loading products.</div>';
            document.getElementById('pagination-controls').innerHTML = '';
        }
    }

    // Event listeners for filters and sort
    productSort.addEventListener('change', () => {
        const sort = productSort.value;
        const search = productSearch.value.trim();
        const categoryId = productCategoryFilter.value || null;
        fetchProducts(1, 10, search, categoryId, sort);
    });

    searchProductsBtn.addEventListener('click', () => {
        const search = productSearch.value.trim();
        const categoryId = productCategoryFilter.value || null;
        const sort = productSort.value;
        fetchProducts(1, 10, search, categoryId, sort);
    });

    productCategoryFilter.addEventListener('change', () => {
        const search = productSearch.value.trim();
        const categoryId = productCategoryFilter.value || null;
        const sort = productSort.value;
        fetchProducts(1, 10, search, categoryId, sort);
    });

    resetFiltersBtn.addEventListener('click', () => {
        productSearch.value = '';
        productCategoryFilter.value = '';
        productSort.value = '';
        fetchProducts(1, 10, '', null, '');
    });




// Centralized handler for add-to-cart clicks
async function handleAddToCart(e) {
    e.preventDefault();
    e.stopPropagation();
    const button = e.currentTarget;
    if (button.disabled) return;

    const productId = parseInt(button.dataset.id);
    const quantityInput = button.parentElement.querySelector('.quantity-input');
    const quantity = parseInt(quantityInput.value);

    button.disabled = true; // Prevent multiple clicks
    try {
        const success = await addToCart(productId, quantity);
        if (success) {
            showToast('Product added to cart.');
            updateCartBadge();
            await updateCartTable();
            quantityInput.value = 1; // Reset quantity input
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast(`Error adding to cart: ${error.message}`, 'error');
    } finally {
        button.disabled = false; // Re-enable button
    }
}


function updatePagination(totalPages, currentPage) {
    const paginationControls = document.getElementById('pagination-controls');
    paginationControls.innerHTML = '';
    if (totalPages <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    // Previous Button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
    ul.appendChild(prevLi);

    // Page Numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        ul.appendChild(li);
    }

    // Next Button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
    ul.appendChild(nextLi);

    paginationControls.appendChild(ul);

    // Add event listeners
    paginationControls.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(e.target.dataset.page);
            if (page && !isNaN(page)) {
                const search = productSearch.value.trim();
                const categoryId = productCategoryFilter.value || null;
                fetchProducts(page, 10, search, categoryId);
            }
        });
    });
}

// Initial call to fetch products
document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
});


   async function fetchSalesReport() {
    try {
        const startDate = document.getElementById('salesStartDate').value;
        const endDate = document.getElementById('salesEndDate').value;
        const searchQuery = document.getElementById('salesSearchQuery').value.trim();
        
        if (!startDate || !endDate) {
            throw new Error('Please select both start and end dates');
        }

        // Ensure dates are in correct format and include full day
        const formattedStartDate = `${startDate} 00:00:00`;
        const formattedEndDate = `${endDate} 23:59:59`;
        
        const params = new URLSearchParams({
            action: 'get_detailed_sales_report',
            start_date: startDate,
            end_date: endDate,
            search_query: searchQuery,
            csrf_token: csrfToken
        });

        const response = await fetch(`api.php?${params}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        if (!Array.isArray(data)) {
            throw new Error(data.message || 'Invalid response format');
        }

        const salesReportTable = document.getElementById('salesReportTable');
        salesReportTable.innerHTML = '';

        if (data.length === 0) {
            salesReportTable.innerHTML = '<tr><td colspan="11" class="text-center">No sales found for selected dates</td></tr>';
            return;
        }

        data.forEach(sale => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sale.sale_id}</td>
                <td>${sale.client_name || 'N/A'}</td>
                <td>${sale.product_name || 'N/A'}</td>
                <td>$${parseFloat(sale.price || 0).toFixed(2)}</td>
                <td>${sale.quantity || 0}</td>
                <td>$${parseFloat(sale.total || 0).toFixed(2)}</td>
                <td>${sale.category_name || 'N/A'}</td>
                <td>${sale.barcode || 'N/A'}</td>
                <td>${sale.discount_name || 'None'}</td>
                <td>$${parseFloat(sale.discount_amount || 0).toFixed(2)}</td>
                <td>${new Date(sale.sale_date).toLocaleString()}</td>
            `;
            salesReportTable.appendChild(row);
        });
    } catch (error) {
        console.error('Error fetching sales report:', error);
        alert('Error fetching sales report: ' + error.message);
        document.getElementById('salesReportTable').innerHTML = '<tr><td colspan="11" class="text-center">Error loading sales report</td></tr>';
    }
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

  // Populate category dropdowns
async function populateCategories() {
    try {
        const response = await fetch('api.php?action=get_categories', {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await response.json();
        const categorySelects = [document.getElementById('inventoryCategoryFilter'), document.getElementById('productCategory')];
        categorySelects.forEach(select => {
            if (select) {
                select.innerHTML = '<option value="">All Categories</option>';
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });
            }
        });
    } catch (error) {
        logError('Error fetching categories: ' + error.message);
    }
}

// Fetch inventory with search and filter
// Inventory Functions
async function fetchInventory() {
    try {
        const search = document.getElementById('inventorySearch').value.trim();
        const categoryId = document.getElementById('inventoryCategoryFilter').value || null;
        let url = 'api.php?action=get_inventory';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (categoryId) url += `&category_id=${categoryId}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        inventoryTable.innerHTML = '';
        if (!data.products || data.products.length === 0) {
            inventoryTable.innerHTML = '<tr><td colspan="10" class="text-center">No products found.</td></tr>';
            return;
        }
        
        data.products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td><img src="${product.image || 'https://via.placeholder.com/50'}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td>${product.name}</td>
                <td>${product.company || '-'}</td>
                <td>${product.model || '-'}</td>
                <td>${product.category_name || '-'}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.quantity}</td>
                <td>${product.barcode || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-product" data-id="${product.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">Delete</button>
                </td>
            `;
            inventoryTable.appendChild(row);
        });
        
        document.querySelectorAll('.edit-product').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                fetch(`api.php?action=get_product&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            productIdInput.value = data.product.id;
                            productNameInput.value = data.product.name;
                            productCompanyInput.value = data.product.company || '';
                            productModelInput.value = data.product.model || '';
                            productCategoryInput.value = data.product.category_id;
                            productPriceInput.value = data.product.price;
                            productQuantityInput.value = data.product.quantity;
                            productBarcodeInput.value = data.product.barcode || '';
                            productDescriptionInput.value = data.product.description || '';
                            document.getElementById('previewImage').src = data.product.image || '';
                            document.getElementById('imagePreview').style.display = data.product.image ? 'block' : 'none';
                            productModalLabel.textContent = 'Edit Product';
                            productModal.show();
                        } else {
                            alert('Error loading product data');
                        }
                    })
                    .catch(error => {
                        logError(`Error fetching product: ${error.message}`);
                        alert('Error fetching product');
                    });
            });
        });
        
        document.querySelectorAll('.delete-product').forEach(btn => {
            btn.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete this product?')) {
                    fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'delete_product',
                            id: btn.dataset.id,
                            csrf_token: csrfToken
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Product deleted successfully');
                                fetchInventory();
                                fetchProducts();
                            } else {
                                alert('Error deleting product: ' + data.message);
                            }
                        })
                        .catch(error => {
                            logError(`Error deleting product: ${error.message}`);
                            alert('Error deleting product');
                        });
                }
            });
        });
        
    } catch (error) {
        logError(`Error fetching inventory: ${error.message}`);
        inventoryTable.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading inventory.</td></tr>';
    }
}

function prepareEditProduct(id) {
    console.log('Preparing to edit product ID:', id);
    fetch(`api.php?action=get_product&id=${id}`, {
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(response => {
        console.log('Get Product Response Status:', response.status);
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        console.log('Product Data:', data);
        if (!data.success || !data.product) {
            throw new Error('Invalid product data');
        }
        const product = data.product;
        productIdInput.value = product.id || '';
        productNameInput.value = product.name || '';
        productCompanyInput.value = product.company || '';
        productModelInput.value = product.model || '';
        productCategoryInput.value = product.category_id || '';
        productPriceInput.value = parseFloat(product.price || 0).toFixed(2);
        productQuantityInput.value = product.quantity || 0;
        productBarcodeInput.value = product.barcode || '';
        productDescriptionInput.value = product.description || '';
        productModalLabel.textContent = 'Edit Product';
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        if (product.image) {
            previewImage.src = product.image;
            imagePreview.style.display = 'block';
        } else {
            previewImage.src = '';
            imagePreview.style.display = 'none';
        }
        productImageInput.value = ''; // Clear file input
        productModal.show();
    })
    .catch(error => {
        logError('Error fetching product data: ' + error.message);
        alert('Error loading product data: ' + error.message);
    });
}

function prepareAddProduct() {
    productModalLabel.textContent = 'Add Product';
    productForm.reset();
    productIdInput.value = '';
    productCategoryInput.value = '';
    document.getElementById('previewImage').src = '';
    document.getElementById('imagePreview').style.display = 'none';
}

async function editProduct(id) {
    try {
        const response = await fetch(`api.php?action=get_inventory`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        const data = await response.json();
        if (data.status !== 'success') throw new Error(data.message || 'Failed to fetch product');

        const product = data.products.find(p => p.id == id);
        if (product) {
            productModalLabel.textContent = 'Edit Product';
            productIdInput.value = product.id;
            productNameInput.value = product.name;
            productCompanyInput.value = product.company || '';
            productModelInput.value = product.model || '';
            productCategoryInput.value = product.category_id || '';
            productPriceInput.value = parseFloat(product.price).toFixed(2);
            productQuantityInput.value = product.quantity;
            productBarcodeInput.value = product.barcode || '';
            productImageInput.value = product.image || '';
            productDescriptionInput.value = product.description || '';
            productModal.show();
        } else {
            throw new Error('Product not found');
        }
    } catch (error) {
        logError('Error editing product: ' + error.message);
        alert('Error editing product: ' + error.message);
    }
}

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ action: 'delete_product', product_id: id, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') {
            alert('Product deleted successfully.');
            fetchInventory();
        } else {
            throw new Error(data.message || 'Failed to delete product.');
        }
    } catch (error) {
        logError('Error deleting product: ' + error.message);
        alert('Error deleting product: ' + error.message);
    }
}

document.getElementById('productImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewImage = document.getElementById('previewImage');
    const imagePreview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImage.src = '';
        imagePreview.style.display = 'none';
    }
});

productForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const action = productIdInput.value ? 'update_product' : 'add_product';
    const formData = new FormData();
    formData.append('action', action);
    formData.append('csrf_token', csrfToken);
    formData.append('name', productNameInput.value);
    formData.append('company', productCompanyInput.value || '');
    formData.append('model', productModelInput.value || '');
    formData.append('category_id', productCategoryInput.value);
    formData.append('price', productPriceInput.value);
    formData.append('quantity', productQuantityInput.value);
    formData.append('barcode', productBarcodeInput.value || '');
    formData.append('description', productDescriptionInput.value || '');
    if (productIdInput.value) {
        formData.append('id', productIdInput.value);
    }
    if (productImageInput.files.length > 0) {
        formData.append('image', productImageInput.files[0]);
    }

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        console.log('Product Submit Response Status:', response.status);
        const data = await response.json();
        console.log('Product Submit Data:', data);
        if (data.success) {
            alert(data.message || 'Product saved successfully');
            productModal.hide();
            productForm.reset();
            document.getElementById('imagePreview').style.display = 'none';
            fetchInventory();
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    } catch (error) {
        logError('Error saving product: ' + error.message);
        alert('Error saving product: ' + error.message);
    }
});

productImageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            previewImage.src = event.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImage.src = '';
        imagePreview.style.display = 'none';
    }
});

// Debounce for search input
let searchTimeout;
document.getElementById('inventorySearch').addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchInventory, 300);
});
document.getElementById('inventoryCategoryFilter').addEventListener('change', fetchInventory);

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    fetchInventory();
});

// document.getElementById('addProductBtn').addEventListener('click', () => {
//             productModalLabel.textContent = 'Add Product';
//             productForm.reset();
//             productIdInput.value = '';
//         });

// Update your existing add to cart event listener or add this:
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-to-cart-btn')) {
        e.preventDefault();
        const button = e.target.closest('.add-to-cart-btn');
        const productId = parseInt(button.dataset.id);
        const quantityInput = button.parentElement.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value) || 1;
        
        if (isNaN(productId) || productId <= 0) {
            alert('Invalid product selection');
            return;
        }

        addToCart(productId, quantity).catch(error => {
            console.error('Add to cart error:', error);
            alert('Failed to add to cart. Please try again.');
        });
    }
});

async function addToCart(productId, quantity = 1) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (!csrfToken) throw new Error('CSRF token not found');
        if (!Number.isInteger(productId) || productId <= 0 || !Number.isInteger(quantity) || quantity <= 0) {
            throw new Error('Invalid product ID or quantity');
        } // Fixed missing parenthesis

        // Fetch product data to validate quantity
        const productResponse = await fetch(`api.php?action=get_product&id=${productId}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!productResponse.ok) throw new Error(`HTTP ${productResponse.status}: ${await productResponse.text()}`);
        const productData = await productResponse.json();
        if (!productData.success || !productData.product) throw new Error('Failed to fetch product data');

        const product = productData.product;
        if (quantity > product.quantity) throw new Error(`Only ${product.quantity} units available`);

        // Add to cart via API
        const cartResponse = await fetch('api.php?action=add_to_cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                csrf_token: csrfToken
            })
        });
        if (!cartResponse.ok) throw new Error(`HTTP ${cartResponse.status}: ${await cartResponse.text()}`);
        const cartData = await cartResponse.json();
        if (cartData.status !== 'success') throw new Error(cartData.message || 'Failed to add to cart');

        // Sync local cart with server
        const serverCartResponse = await fetch('api.php?action=get_cart', {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!serverCartResponse.ok) throw new Error(`HTTP ${serverCartResponse.status}: ${await serverCartResponse.text()}`);
        const serverCartData = await serverCartResponse.json();
        if (!serverCartData.success) throw new Error(serverCartData.message || 'Failed to fetch cart');

        // Handle server cart as array or object
        const cartItems = Array.isArray(serverCartData.cart)
            ? serverCartData.cart
            : serverCartData.cart && typeof serverCartData.cart === 'object'
                ? Object.values(serverCartData.cart)
                : [];

        // Update localStorage with server cart
        const cart = cartItems.map(item => ({
            id: parseInt(item.id),
            name: item.name || 'Unknown Product',
            price: parseFloat(item.price) || 0,
            quantity: parseInt(item.quantity) || 1,
            maxQuantity: parseInt(item.maxQuantity || product.quantity) || Infinity,
            image: item.image || product.image || 'https://via.placeholder.com/50',
            description: item.description || product.description || '',
            barcode: item.barcode || product.barcode || '',
            company: item.company || product.company || '',
            model: item.model || product.model || '',
            category_name: item.category_name || product.category_name || ''
        }));

        localStorage.setItem('cart', JSON.stringify(cart));
        await updateCartTable();
        updateCartBadge();
        showToast(`${product.name} added to cart`);

        // Hide search results
        const productSuggestions = document.getElementById('searchResults');
        if (productSuggestions) {
            productSuggestions.style.display = 'none';
        }

        return true;
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast(`Error adding to cart: ${error.message}`, 'error');
        return false;
    }
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


async function checkout() {
    if (!customerId) {
        showAlert('Please select a customer.', 'danger');
        return;
    }

    // Get cart items from the server-side cart
    try {
        const response = await fetch('api.php?action=get_cart', {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await response.json();
        
        if (!data.success || !data.cart) {
            throw new Error(data.message || 'Failed to retrieve cart');
        }

        // Convert cart object to array
        const cartItems = Object.values(data.cart).map(item => ({
            id: parseInt(item.id),
            quantity: parseInt(item.quantity) || 1,
            price: parseFloat(item.price) || 0
        })).filter(item => item.id > 0 && item.quantity > 0 && item.price >= 0);

        if (cartItems.length === 0) {
            showAlert('Cart is empty.', 'danger');
            return;
        }

        // Calculate totals from server-side data
        const subtotal = Object.values(data.cart).reduce((sum, item) => 
            sum + (parseFloat(item.price) * parseInt(item.quantity)), 0);
        const tax = subtotal * TAX_RATE;
        const discount = parseFloat(cartDiscount.textContent.replace('$', '')) || 0;
        const total = subtotal + tax - discount;

        // Prepare checkout payload
        const payload = {
            action: 'checkout',
            customer_id: customerId,
            discount_amount: discount,
            points_earned: Math.floor(total * POINTS_PER_DOLLAR),
            points_redeemed: parseInt(loyaltyPointsToUse.value) || 0,
            items: cartItems,
            csrf_token: csrfToken
        };

        const checkoutResponse = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(payload)
        });

        const checkoutData = await checkoutResponse.json();
        if (!checkoutData.success) {
            throw new Error(checkoutData.message || 'Checkout failed');
        }

        showToast(`Sale successful! Sale ID: ${checkoutData.sale_id}`);
        
        // Generate and download receipt
        if (checkoutData.invoice_url) {
            const a = document.createElement('a');
            a.href = checkoutData.invoice_url;
            a.download = '';
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            a.remove();
        }

        // Reset cart UI
        await updateCartTable();
        customerId = null;
        
        selectedCustomerInfo.style.display = 'none';
        loyaltyPointsToUse.value = '0';
        checkoutBtn.disabled = true;

    } catch (error) {
        logError('Checkout error: ' + error.message);
        showAlert('Checkout error: ' + error.message, 'danger');
    }
}
checkoutBtn.addEventListener('click', checkout);





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
    




       // Fetch Discounts
async function fetchDiscounts() {
    try {
        const response = await fetch('api.php?action=get_all_discounts', {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        const discounts = await response.json();
        const tbody = document.getElementById('discountTable');
        tbody.innerHTML = '';
        if (Array.isArray(discounts) && discounts.length > 0) {
            const now = new Date();
            discounts.forEach(discount => {
                const endDate = new Date(discount.end_date);
                // Only display discounts that haven't expired
                if (endDate > now) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${discount.id}</td>
                        <td>${discount.name}</td>
                        <td>${discount.type === 'percentage' ? 'Percentage' : 'Fixed'}</td>
                        <td>${discount.type === 'percentage' ? `${discount.value}%` : `$${parseFloat(discount.value).toFixed(2)}`}</td>
                        <td>${discount.min_purchase_amount ? `$${parseFloat(discount.min_purchase_amount).toFixed(2)}` : 'N/A'}</td>
                        <td>${discount.product_name || 'N/A'}</td>
                        <td>${discount.category_name || 'N/A'}</td>
                        <td>${new Date(discount.start_date).toLocaleString()}</td>
                        <td>${new Date(discount.end_date).toLocaleString()}</td>
                        <td><input type="checkbox" ${discount.is_active ? 'checked' : ''} disabled></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-discount" data-id="${discount.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-discount" data-id="${discount.id}">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                } else {
                    // Automatically delete expired discount
                    deleteExpiredDiscount(discount.id);
                }
            });

            // Add event listeners for edit buttons
            document.querySelectorAll('.edit-discount').forEach(button => {
                button.addEventListener('click', () => editDiscount(button.dataset.id));
            });

            // Add event listeners for delete buttons
            document.querySelectorAll('.delete-discount').forEach(button => {
                button.addEventListener('click', () => deleteDiscount(button.dataset.id));
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center">No active discounts found.</td></tr>';
        }
    } catch (error) {
        logError('Error fetching discounts: ' + error.message);
        alert('Error fetching discounts: ' + error.message);
        document.getElementById('discountTable').innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading discounts</td></tr>';
    }
}

async function deleteExpiredDiscount(id) {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ action: 'delete_discount', id, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to delete expired discount.');
        }
    } catch (error) {
        logError('Error deleting expired discount: ' + error.message);
    }
}

async function editDiscount(id) {
    try {
        const response = await fetch(`api.php?action=get_all_discounts`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        const discounts = await response.json();
        const discount = discounts.find(d => d.id == id);
        if (discount) {
            discountModalLabel.textContent = 'Edit Discount';
            discountIdInput.value = discount.id;
            discountNameInput.value = discount.name;
            discountTypeInput.value = discount.type;
            discountValueInput.value = discount.value;
            discountMinPurchaseInput.value = discount.min_purchase_amount || '';
            discountProductInput.value = discount.product_id || '';
            discountCategoryInput.value = discount.category_id || '';
            discountStartDateInput.value = discount.start_date.replace(' ', 'T');
            discountEndDateInput.value = discount.end_date.replace(' ', 'T');
            discountIsActiveInput.checked = discount.is_active;
            discountModal.show();
        } else {
            throw new Error('Discount not found');
        }
    } catch (error) {
        logError('Error editing discount: ' + error.message);
        alert('Error editing discount: ' + error.message);
    }
}

async function deleteDiscount(id) {
    if (!confirm('Are you sure you want to delete this discount?')) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ action: 'delete_discount', id, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.success) {
            alert('Discount deleted successfully.');
            fetchDiscounts();
        } else {
            throw new Error(data.message || 'Failed to delete discount.');
        }
    } catch (error) {
        logError('Error deleting discount: ' + error.message);
        alert('Error deleting discount: ' + error.message);
    }
}

discountForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = discountIdInput.value;
    const action = id ? 'update_discount' : 'add_discount';
    const payload = {
        action,
        id: id ? parseInt(id) : null,
        name: discountNameInput.value.trim(),
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

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            alert(id ? 'Discount updated successfully.' : 'Discount added successfully.');
            discountModal.hide();
            discountForm.reset();
            discountIdInput.value = '';
            discountModalLabel.textContent = 'Add Discount';
            fetchDiscounts();
        } else {
            throw new Error(data.message || 'Failed to save discount.');
        }
    } catch (error) {
        logError('Error saving discount: ' + error.message);
        alert('Error saving discount: ' + error.message);
    }
});

document.getElementById('addDiscountBtn').addEventListener('click', () => {
    discountModalLabel.textContent = 'Add Discount';
    discountForm.reset();
    discountIdInput.value = '';
    discountModal.show();
});



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

// Prevent form reset on modal show
document.getElementById('productModal').addEventListener('show.bs.modal', (e) => {
    console.log('Modal show event triggered');
    // Only reset form for Add Product
    if (productModalLabel.textContent === 'Add Product') {
        console.log('Resetting form for Add Product');
        productForm.reset();
        productIdInput.value = '';
    }
});

// Prevent jQuery reset if present
$(document).ready(function() {
    $('#productModal').on('show.bs.modal', function(e) {
        console.log('jQuery modal show event triggered');
        // Only reset for Add Product
        if ($('#productModalLabel').text() === 'Add Product') {
            console.log('jQuery resetting form for Add Product');
            $('#productForm')[0].reset();
            $('#productId').val('');
        }
    });
});







       





async function updateCartItemQuantity(productId, quantity) {
    try {
        const response = await fetch(`api.php?action=update_cart_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ product_id: parseInt(productId), quantity: parseInt(quantity), csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to update cart item.');
        }
        await updateCartTable();
        updateCartBadge();
    } catch (error) {
        logError('Error updating cart item quantity: ' + error.message);
        alert('Error updating cart: ' + error.message);
    }
}

// async function removeCartItem(index) {
//     try {
//         const cart = JSON.parse(localStorage.getItem('cart') || '[]');
//         const productId = cart[index].id;

//         // Remove from server-side cart
//         const response = await fetch(`api.php?action=remove_cart_item`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-Token': csrfToken
//             },
//             body: JSON.stringify({ product_id: productId })
//         });
//         const data = await response.json();
//         if (data.status !== 'success') {
//             throw new Error(data.message || 'Failed to remove cart item.');
//         }

//         // Update client-side cart
//         cart.splice(index, 1);
//         localStorage.setItem('cart', JSON.stringify(cart));
//         updateCartTable();
//         updateCartBadge();
//     } catch (error) {
//         logError('Error removing cart item: ' + error.message);
//         alert('Error removing cart: ' + error.message);
//     }
// }

async function removeCartItem(productId) {
    try {
        const response = await fetch(`api.php?action=remove_cart_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ product_id: parseInt(productId), csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to remove cart item.');
        }
        await updateCartTable();
        updateCartBadge();
        showToast('Item removed from cart.');
    } catch (error) {
        logError('Error removing cart item: ' + error.message);
        alert('Error removing cart item: ' + error.message);
    }
}


// function updatePaymentTotal() {
//         const paymentInputs = paymentMethods.querySelectorAll('.payment-amount');
//         let total = 0;
//         paymentInputs.forEach(input => {
//             total += parseFloat(input.value) || 0;
//         });
//         totalPaid.textContent = `$${total.toFixed(2)}`;
//         const cartTotalValue = parseFloat(cartTotal.textContent.replace('$', '')) || 0;
//         remainingBalance.textContent = `$${(cartTotalValue - total).toFixed(2)}`;
//     }

// function addPaymentMethodRow() {
//     const row = document.createElement('div');
//     row.className = 'payment-method mb-3';
//     row.innerHTML = `
//         <div class="row g-2">
//             <div class="col-md-5">
//                 <select class="form-control payment-type">
//                     <option value="cash">Cash</option>
//                     <option value="credit_card">Credit Card</option>
//                     <option value="debit_card">Debit Card</option>
//                     <option value="loyalty_points">Loyalty Points</option>
//                 </select>
//             </div>
//             <div class="col-md-5">
//                 <input type="number" class="form-control payment-amount" placeholder="Amount" min="0" step="0.01">
//             </div>
//             <div class="col-md-2">
//                 <button class="btn btn-danger w-100 remove-payment">
//                     <i class="fas fa-trash"></i>
//                 </button>
//             </div>
//         </div>
//     `;
//     paymentMethods.appendChild(row);
//     row.querySelector('.payment-amount').addEventListener('input', updatePaymentTotal);
//     row.querySelector('.remove-payment').addEventListener('click', () => {
//         row.remove();
//         updatePaymentTotal();
//     });
//     updateRemoveButtons();
// }

function updateRemoveButtons() {
    const buttons = document.querySelectorAll('.remove-payment');
    buttons.forEach(btn => btn.disabled = buttons.length <= 1);
}

function generateReceipt(saleId, customerId) {
    return fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            action: 'generate_invoice',
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
        a.download = `Invoice_${saleId}.pdf`;
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


function showAlert(message, type = 'info') {
    const alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type}`;
    alertBox.textContent = message;
    alertBox.style.marginTop = '10px';

    const container = document.getElementById('alertContainer') || document.body;
    container.prepend(alertBox);

    setTimeout(() => alertBox.remove(), 4000);
}

function logError(context, error) {
    console.error(`${context}:`, error);
}

checkoutBtn.addEventListener('click', async () => {
    const customerSelect = document.getElementById('customerSelect');
    const discountInput = document.getElementById('discountAmount');
    const cartTable = document.getElementById('cartItems');
    const totalAmountDisplay = document.getElementById('totalAmount');

    if (!customerSelect || !discountInput || !cartTable || !totalAmountDisplay) {
        showAlert('Required form elements are missing from the page.', 'danger');
        return;
    }

    const customer_id = customerSelect.value || null;
    const discount_amount = parseFloat(discountInput.value) || 0;

    const cartItems = Array.from(cartTable.querySelectorAll('tr')).map(row => {
        const quantityInput = row.querySelector('.quantity');
        const priceCell = row.querySelector('.price');

        return {
            product_id: row.dataset.productId,
            quantity: parseInt(quantityInput?.value || 0),
            price: parseFloat(priceCell?.textContent.replace('$', '') || 0)
        };
    }).filter(item => item.product_id && item.quantity > 0 && item.price >= 0);

    if (cartItems.length === 0) {
        showAlert('Cart is empty or items are invalid.', 'danger');
        return;
    }

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'checkout',
                customer_id,
                discount_amount,
                items: cartItems
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert(`✅ Sale successful! Sale ID: ${data.sale_id}`, 'success');

            if (data.invoice_url) {
        // ✅ Show a download link and click it
        const a = document.createElement('a');
        a.href = data.invoice_url;
        a.setAttribute('target', '_blank'); // show download tab or preview
        a.setAttribute('download', ''); // optional: force download
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

            resetCartUI();
        } else {
            showAlert(data.message || '❌ Checkout failed.', 'danger');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        showAlert('❌ Error during checkout: ' + error.message, 'danger');
    }

    function resetCartUI() {
        cartTable.innerHTML = '';
        totalAmountDisplay.textContent = '$0.00';
        discountInput.value = '';
        customerSelect.value = '';
        loadSales();
        loadCustomers();
    }
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


// addPaymentMethodBtn.addEventListener('click', addPaymentMethodRow);


// const paymentAmountInput = paymentMethods.querySelector('.payment-amount');
// if (paymentAmountInput) {
//     paymentAmountInput.addEventListener('input', updatePaymentTotal);
// }

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
    // Ensure elements exist
    if (!cartItemCountBadge || !cartItemCount) {
        console.error('Cart badge elements not found:', {
            cartItemCountBadge: !!cartItemCountBadge,
            cartItemCount: !!cartItemCount
        });
        return;
    }

    fetch('api.php?action=get_cart_items', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.debug('Cart items response:', data); // Debug API response
        const totalItems = Array.isArray(data.items) ? 
            data.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0) : 0;
        
        console.debug('Total items calculated:', totalItems); // Debug item count
        
        if (totalItems > 0) {
            cartItemCountBadge.textContent = totalItems;
            cartItemCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
            cartItemCountBadge.style.display = 'inline-block';
        } else {
            cartItemCountBadge.textContent = '';
            cartItemCount.textContent = '0 items';
            cartItemCountBadge.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error updating cart badge:', error);
        cartItemCountBadge.textContent = '';
        cartItemCount.textContent = '0 items';
        cartItemCountBadge.style.display = 'none';
    });
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




// Event Listeners for Product Catalog
// Debounce function to limit API calls
// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Product Catalog Event Listeners
const debouncedFetchProducts = debounce(() => {
    const search = productSearch.value.trim();
    const categoryId = productCategoryFilter.value ? parseInt(productCategoryFilter.value) : null;
    fetchProducts(1, 10, search, categoryId);
}, 300);

searchProductsBtn.addEventListener('click', debouncedFetchProducts);
resetFiltersBtn.addEventListener('click', () => {
    productSearch.value = '';
    productCategoryFilter.value = '';
    productSuggestions.innerHTML = '';
    productSuggestions.style.display = 'none';
    fetchProducts(1, 10, '', null);
});

productCategoryFilter.addEventListener('change', debouncedFetchProducts);

// Initialize Product Catalog
if (document.getElementById('product-catalog').classList.contains('active')) {
    fetchProducts(1, 10, '', null);
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
                    ? suggestions.map(item => `<div class="autocomplete-item" data-id="${item.id}">${item.name}</div>`).join('')
                    : '<div class="autocomplete-item">No suggestions found.</div>';
                if (suggestions.length > 0) {
                    positionSuggestions(input, suggestionsContainer); // Position suggestions
                }
                suggestionsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
                    item.addEventListener('click', () => {
                        input.value = item.textContent;
                        suggestionsContainer.style.display = 'none';
                        onSelect({ id: item.dataset.id, name: item.textContent });
                    });
                });
            } catch (error) {
                logError(`Autocomplete error for ${apiAction}: ${error.message}`);
                suggestionsContainer.innerHTML = '<div class="autocomplete-item text-danger">Error loading suggestions</div>';
                suggestionsContainer.style.display = 'block';
                positionSuggestions(input, suggestionsContainer); // Position even on error
            }
        }, 300);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

    

// Product Search Autocomplete
// Product Search Autocomplete
// Product Search Autocomplete
// Product Search Autocomplete
// Product Search Autocomplete
// Product Search Autocomplete
let productTimeout;
productSearch.addEventListener('input', () => {
    clearTimeout(productTimeout);
    const query = productSearch.value.trim();
    
    // Clear suggestions if search is empty
    if (query.length === 0) {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
        fetchProducts(1, 10, ''); // Reset product list
        return;
    }
    
    // Only show suggestions after 2 characters
    if (query.length < 2) {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
        return;
    }
    
    productTimeout = setTimeout(() => {
        fetch(`api.php?action=search_products&term=${encodeURIComponent(query)}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                searchResults.innerHTML = '';
                
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(product => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <div class="search-result-info">
                                <h6>${product.name}</h6>
                                <small class="${product.quantity > 0 ? 'in-stock' : 'out-of-stock'}">
                                    ${product.quantity > 0 ? 'In Stock' : 'Out of Stock'}
                                </small>
                            </div>
                        `;
                        item.addEventListener('click', () => {
                            productSearch.value = product.name;
                            searchResults.style.display = 'none';
                            fetchProducts(1, 10, product.name);
                        });
                        searchResults.appendChild(item);
                    });
                    searchResults.style.display = 'block';
                    
                    // Position the dropdown correctly
                    positionDropdown(searchResults, productSearch);
                } else {
                    searchResults.innerHTML = '<div class="search-result-item">No products found</div>';
                    searchResults.style.display = 'block';
                    positionDropdown(searchResults, productSearch);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="search-result-item text-danger">Error loading results</div>';
                searchResults.style.display = 'block';
                positionDropdown(searchResults, productSearch);
            });
    }, 300);
});

// Helper function to position dropdown
function positionDropdown(dropdown, input) {
    const searchContainer = input.closest('.search-container');
    const inputRect = input.getBoundingClientRect();
    const containerRect = searchContainer.getBoundingClientRect();
    
    dropdown.style.position = 'absolute';
    dropdown.style.width = `${inputRect.width}px`;
    dropdown.style.left = `${inputRect.left - containerRect.left}px`;
    dropdown.style.top = `${inputRect.height}px`; // Align directly below input
    dropdown.style.zIndex = '1000';
}

// Hide suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!productSearch.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

resetFiltersBtn.addEventListener('click', () => {
    productSearch.value = '';
    searchResults.innerHTML = '';
    searchResults.style.display = 'none';
    fetchProducts(1, 10, '', null);
});






document.addEventListener('click', (e) => {
    if (!customerSearchBar.contains(e.target) && !customerSuggestions.contains(e.target)) {
        customerSuggestions.style.display = 'none';
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


document.addEventListener('DOMContentLoaded', function() {
    const stockAdjustmentForm = document.getElementById('stockAdjustmentForm');
    const adjustmentProductId = document.getElementById('adjustmentProductId');
    const adjustmentProductSearch = document.getElementById('adjustmentProductSearch');
    const adjustmentProductSuggestions = document.getElementById('adjustmentProductSuggestions');
    const stockAdjustmentModal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));

    // Debug: Log if elements are missing
    if (!stockAdjustmentForm) console.error('stockAdjustmentForm not found');
    if (!adjustmentProductId) console.error('adjustmentProductId not found');
    if (!adjustmentProductSearch) console.error('adjustmentProductSearch not found');
    if (!adjustmentProductSuggestions) console.error('adjustmentProductSuggestions not found');
    if (!stockAdjustmentModal) console.error('stockAdjustmentModal not found');

    // Exit if critical elements are missing
    if (!stockAdjustmentForm || !adjustmentProductId || !adjustmentProductSearch || !adjustmentProductSuggestions) {
        console.error('Required DOM elements for stock adjustment are missing.');
        return;
    }

    // Handle Stock Adjustment Form Submission
    stockAdjustmentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const productId = adjustmentProductId.value;
    const adjustmentType = document.getElementById('adjustmentType')?.value;
    const quantity = parseInt(document.getElementById('adjustmentQuantity')?.value);
    const reason = document.getElementById('adjustmentReason')?.value.trim();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Validation
    if (!productId || isNaN(parseInt(productId))) {
        showToast('Please select a valid product from the suggestions.', 'error');
        return;
    }
    if (!adjustmentType) {
        showToast('Please select an adjustment type.', 'error');
        return;
    }
    if (quantity <= 0 || isNaN(quantity)) {
        showToast('Please enter a valid quantity greater than 0.', 'error');
        return;
    }
    if (!reason) {
        showToast('Please provide a reason for the adjustment.', 'error');
        return;
    }

    try {
        const productIdInt = parseInt(productId);
        if (isNaN(productIdInt) || productIdInt <= 0) {
            showToast('Invalid product ID.', 'error');
            return;
        }

        // Fetch product details
        const response = await fetch(`api.php?action=search_products&id=${productIdInt}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(`HTTP ${response.status}: ${errorData.message || 'Failed to fetch product'}`);
        }
        const products = await response.json();
        console.log('search_products response:', products);

        const productData = Array.isArray(products) ? products.find(p => p.id == productIdInt) : null;
        if (!productData) {
            showToast('Selected product does not exist.', 'error');
            return;
        }
        if (!('quantity' in productData)) {
            showToast('Product data is incomplete.', 'error');
            return;
        }

        const currentQuantity = parseInt(productData.quantity);
        let newQuantity;
        if (adjustmentType === 'ADD') {
            newQuantity = currentQuantity + quantity;
        } else { // SUBTRACT
            newQuantity = currentQuantity - quantity;
            if (newQuantity < 0) {
                showToast('Insufficient stock for subtraction.', 'error');
                return;
            }
        }

        // Update product quantity
        const updateResponse = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({
                action: 'update_product_quantity',
                id: productIdInt,
                quantity: newQuantity,
                csrf_token: csrfToken
            })
        });
        const updateData = await updateResponse.json();
        console.log('update_product_quantity response:', updateData);
        if (!updateData.success) {
            throw new Error(updateData.message || 'Failed to update stock');
        }

        // Log the adjustment
        const logResponse = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({
                action: 'log_stock_adjustment',
                product_id: productIdInt,
                quantity: quantity,
                adjustment_type: adjustmentType,
                reason: reason,
                csrf_token: csrfToken
            })
        });
        const logData = await logResponse.json();
        if (!logData.success) {
            console.warn('Stock updated but failed to log adjustment:', logData.message);
            showToast('Stock updated but failed to log adjustment.', 'warning');
        }

        showToast('Stock adjusted successfully.', 'success');
        stockAdjustmentModal.hide();
        stockAdjustmentForm.reset();
        adjustmentProductId.value = '';
        adjustmentProductSearch.value = '';
        adjustmentSearch.value = '';
        suggestionList.innerHTML = '';
        suggestionList.style.display = 'none';
        fetchStockAdjustments();
        fetchInventory();
        fetchLowStockProducts();
    } catch (error) {
        console.error('Error adjusting stock:', error);
        showToast(`Error adjusting stock: ${error.message}`, 'error');
    }
});
    document.addEventListener('click', (e) => {
        if (!adjustmentSearch.contains(e.target) && !suggestionList.contains(e.target)) {
            suggestionList.style.display = 'none';
        }
    });

    adjustmentSearch.addEventListener('input', fetchStockAdjustments);
    adjustmentStartDate.addEventListener('change', fetchStockAdjustments);
    adjustmentEndDate.addEventListener('change', fetchStockAdjustments);

    // Initial fetch
    fetchStockAdjustments();
});

function positionSuggestions(inputElement, suggestionsElement) {
    if (!inputElement || !suggestionsElement || !document.body.contains(inputElement)) {
        console.warn('positionSuggestions: Elements not found in DOM');
        return;
    }

    const inputRect = inputElement.getBoundingClientRect();
    const containerRect = inputElement.closest('.position-relative')?.getBoundingClientRect() || { left: 0, top: 0 };

    suggestionsElement.style.position = 'absolute';
    suggestionsElement.style.width = `${inputRect.width}px`;
    suggestionsElement.style.left = `${inputRect.left - containerRect.left}px`;
    suggestionsElement.style.top = `${inputRect.height}px`;
    suggestionsElement.style.zIndex = '1000';
}


// Update event listeners for customer and product search
document.addEventListener('DOMContentLoaded', () => {
    const customerSearchBar = document.getElementById('cartCustomerSearch');
    const customerSuggestions = document.getElementById('customerSearchResults');
    const productSearchBar = document.getElementById('productSearch');
    const productSuggestions = document.getElementById('searchResults');

    if (customerSearchBar && customerSuggestions) {
        customerSearchBar.addEventListener('focus', () => {
            if (customerSuggestions.innerHTML && customerSuggestions.style.display !== 'none') {
                positionSuggestions(customerSearchBar, customerSuggestions);
            }
        });
        customerSearchBar.addEventListener('input', () => {
            clearTimeout(customerTimeout);
            customerTimeout = setTimeout(searchCustomers, 300);
        });
    } else {
        console.warn('Customer search elements not found');
    }

    if (productSearchBar && productSuggestions) {
        productSearchBar.addEventListener('focus', () => {
            if (productSuggestions.innerHTML && productSuggestions.style.display !== 'none') {
                positionSuggestions(productSearchBar, productSuggestions);
            }
        });
        productSearchBar.addEventListener('input', () => {
            clearTimeout(productTimeout);
            const query = productSearchBar.value.trim();
            if (query.length < 2) {
                productSuggestions.innerHTML = '';
                productSuggestions.style.display = 'none';
                return;
            }
            productTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });
    } else {
        console.warn('Product search elements not found');
    }

    window.addEventListener('resize', () => {
        if (customerSearchBar && customerSuggestions && document.body.contains(customerSearchBar) && customerSuggestions.style.display === 'block') {
            positionSuggestions(customerSearchBar, customerSuggestions);
        }
        if (productSearchBar && productSuggestions && document.body.contains(productSearchBar) && productSuggestions.style.display === 'block') {
            positionSuggestions(productSearchBar, productSuggestions);
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (customerSearchBar && customerSuggestions && !customerSearchBar.contains(e.target) && !customerSuggestions.contains(e.target)) {
            customerSuggestions.style.display = 'none';
        }
        if (productSearchBar && productSuggestions && !productSearchBar.contains(e.target) && !productSuggestions.contains(e.target)) {
            productSuggestions.style.display = 'none';
        }
    });
});

// Call this whenever the input is focused or window is resized

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
                            customerSuggestions.style.display = 'none';
                            fetchCustomers(customer.username);
                        });
                        customerSuggestions.appendChild(div);
                    });
                    
                    // Position and show the suggestions
                    positionSuggestions(customerSearch, customerSuggestions);
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
   
    document.addEventListener('click', (e) => {
       
        if (!customerSearch.contains(e.target) && !customerSuggestions.contains(e.target)) {
            customerSuggestions.style.display = 'none';
        }
    });
  
    document.getElementById('fetchForecastBtn')?.addEventListener('click', fetchForecast);

    // Initialize Dashboard
    fetchDashboardSummary();
    fetchDailySalesTrend();
    fetchLowStockProducts();

    document.addEventListener('DOMContentLoaded', function() {
    const productSearch = document.getElementById('productSearch');
    const searchBtn = document.getElementById('searchProductsBtn');
    const searchResults = document.getElementById('searchResults');
    
    if (!productSearch || !searchBtn || !searchResults) return;

    // Perform search when button is clicked
    searchBtn.addEventListener('click', performSearch);
    
    // Also perform search when Enter is pressed
    productSearch.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Show/hide search results when input is focused/blurred
    productSearch.addEventListener('focus', () => {
        if (searchResults.innerHTML) {
            searchResults.style.display = 'block';
        }
    });
    
    document.addEventListener('click', (e) => {
        if (!productSearch.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    async function performSearch() {
        const query = productSearch.value.trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            fetchProducts(1, 10, ''); // Show all products if search is empty
            return;
        }

        try {
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Searching...';
            
            const response = await fetch(`api.php?action=search_products&term=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Search failed');
            const products = await response.json();
            
            searchResults.innerHTML = '';
            
            if (products.length > 0) {
                products.forEach(product => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.innerHTML = `
                        <img src="${product.image || 'https://via.placeholder.com/50'}" alt="${product.name}">
                        <div class="search-result-info">
                            <h6>${product.name}</h6>
                            <small>$${parseFloat(product.price || 0).toFixed(2)} • ${product.barcode || 'No barcode'}</small>
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        productSearch.value = product.name;
                        searchResults.style.display = 'none';
                        fetchProducts(1, 10, product.name);
                    });
                    searchResults.appendChild(item);
                });
                searchResults.style.display = 'block';
            } else {
                const noResults = document.createElement('div');
                noResults.className = 'search-result-item text-muted';
                noResults.textContent = 'No products found';
                searchResults.appendChild(noResults);
                searchResults.style.display = 'block';
            }
        } catch (error) {
            console.error('Search error:', error);
            const errorItem = document.createElement('div');
            errorItem.className = 'search-result-item text-danger';
            errorItem.textContent = 'Error loading results';
            searchResults.innerHTML = '';
            searchResults.appendChild(errorItem);
            searchResults.style.display = 'block';
        } finally {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i> Search';
        }
    }
});
