// DOM Elements
const inventoryBody = document.getElementById('inventoryBody');
const productSelect = document.getElementById('productSelect');
const salesBody = document.getElementById('salesBody');

// API Base URL
const API_URL = 'api.php';

// Load all data on page load
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    loadSalesHistory();
});

// Load products from API
async function loadProducts() {
    try {
        const response = await fetch(`${API_URL}?action=get_products`);
        const data = await response.json();
        
        inventoryBody.innerHTML = '';
        productSelect.innerHTML = '<option value="">Select Product</option>';

        data.forEach(product => {
            // Add to inventory table
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.quantity}</td>
                <td class="action-buttons">
                    <button onclick="editProduct(${product.id})" class="btn btn-primary" style="padding: 8px 12px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteProduct(${product.id})" class="btn btn-danger" style="padding: 8px 12px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            inventoryBody.appendChild(row);

            // Add to product select dropdown
            if (product.quantity > 0) {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} ($${parseFloat(product.price).toFixed(2)} - Stock: ${product.quantity}`;
                productSelect.appendChild(option);
            }
        });
    } catch (error) {
        showAlert('Error loading products', 'error');
        console.error('Error:', error);
    }
}

// Load sales history from API
async function loadSalesHistory() {
    try {
        const response = await fetch(`${API_URL}?action=get_sales`);
        const data = await response.json();
        
        salesBody.innerHTML = '';
        
        data.forEach(sale => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${new Date(sale.sale_date).toLocaleString()}</td>
                <td>${sale.product_name}</td>
                <td>${sale.quantity}</td>
                <td>$${(sale.quantity * sale.price).toFixed(2)}</td>
            `;
            salesBody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading sales history:', error);
    }
}

// Add a new product
async function addProduct() {
    const name = document.getElementById('productName').value.trim();
    const price = parseFloat(document.getElementById('productPrice').value);
    const quantity = parseInt(document.getElementById('productQuantity').value);

    if (!name || isNaN(price) || price <= 0 || isNaN(quantity) || quantity < 0) {
        showAlert('Please fill all fields correctly.', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=add_product`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, price, quantity })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('Product added successfully!', 'success');
            document.getElementById('productName').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productQuantity').value = '';
            loadProducts();
        } else {
            showAlert(data.message || 'Error adding product', 'error');
        }
    } catch (error) {
        showAlert('Error adding product', 'error');
        console.error('Error:', error);
    }
}

// Process a sale
async function processSale() {
    const productId = productSelect.value;
    const quantity = parseInt(document.getElementById('saleQuantity').value);

    if (!productId || isNaN(quantity) || quantity <= 0) {
        showAlert('Please select a product and enter a valid quantity.', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=process_sale`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('Sale processed successfully!', 'success');
            document.getElementById('saleQuantity').value = '';
            loadProducts();
            loadSalesHistory();
        } else {
            showAlert(data.message || 'Error processing sale', 'error');
        }
    } catch (error) {
        showAlert('Error processing sale', 'error');
        console.error('Error:', error);
    }
}

// Edit product (placeholder for future implementation)
function editProduct(id) {
    showAlert('Edit functionality will be implemented in the future.', 'info');
}

// Delete product
async function deleteProduct(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${API_URL}?action=delete_product`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showAlert('Product deleted successfully!', 'success');
                loadProducts();
            } else {
                showAlert(data.message || 'Error deleting product', 'error');
            }
        } catch (error) {
            showAlert('Error deleting product', 'error');
            console.error('Error:', error);
        }
    }
}

// Show alert using SweetAlert2
function showAlert(message, type) {
    Swal.fire({
        title: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}