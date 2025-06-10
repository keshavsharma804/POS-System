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
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', Arial, sans-serif; background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .error { color: red; }
        .success { color: green; }
        .spinner-border { color: #007bff; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">POS System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_sales.php">Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sales_report.php">Sales Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="most_sold_products.php">Most Sold Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Sales Report</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Filter Sales Report</h2>
                <form id="filterForm" class="row">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('-90 days')); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="clientSearch" class="form-label">Client:</label>
                        <div class="input-group">
                            <input type="text" id="clientSearch" class="form-control" placeholder="Search client by username">
                            <button class="btn btn-primary" type="button" id="searchClientBtn">Search</button>
                        </div>
                        <select id="clientSelect" class="form-select mt-2">
                            <option value="">All Clients</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="productQuery" class="form-label">Product Name/Barcode:</label>
                        <input type="text" id="productQuery" class="form-control" placeholder="Search product">
                    </div>
                    <div class="col-md-12 mb-3">
                        <button type="button" id="fetchReportBtn" class="btn btn-primary">Fetch Report</button>
                        <button type="button" id="exportCsvBtn" class="btn btn-secondary">Export CSV</button>
                        <button type="button" id="exportPdfBtn" class="btn btn-info">Export PDF</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales Report Table -->
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
                                <th>Sale Date</th>
                            </tr>
                        </thead>
                        <tbody id="salesReportTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const filterForm = document.getElementById('filterForm');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const clientSearchInput = document.getElementById('clientSearch');
        const clientSelect = document.getElementById('clientSelect');
        const productQueryInput = document.getElementById('productQuery');
        const fetchReportBtn = document.getElementById('fetchReportBtn');
        const exportCsvBtn = document.getElementById('exportCsvBtn');
        const exportPdfBtn = document.getElementById('exportPdfBtn');
        const salesReportTable = document.getElementById('salesReportTable');
        let csrfToken = document.getElementById('csrf_token').value;

        function logError(message) {
            console.error(message);
            fetch('api.php?action=log_error', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
        }

        function fetchClients(username) {
            fetch(`api.php?action=search_clients&username=${encodeURIComponent(username)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.clients.length > 0) {
                        clientSelect.innerHTML = '<option value="">All Clients</option>';
                        data.clients.forEach(client => {
                            const option = document.createElement('option');
                            option.value = client.id;
                            option.textContent = client.username;
                            clientSelect.appendChild(option);
                        });
                    } else {
                        clientSelect.innerHTML = '<option value="">No clients found</option>';
                    }
                })
                .catch(error => {
                    logError('Error searching clients: ' + error.message);
                    alert('Error searching clients: ' + error.message);
                });
        }

        document.getElementById('searchClientBtn').addEventListener('click', () => {
            const username = clientSearchInput.value.trim();
            if (!username) {
                clientSelect.innerHTML = '<option value="">All Clients</option>';
                return;
            }
            fetchClients(username);
        });

        function fetchSalesReport() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const clientId = clientSelect.value;
            const productQuery = productQueryInput.value.trim();
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            let url = `api.php?action=get_detailed_sales_report&start_date=${startDate}&end_date=${endDate}`;
            if (clientId) url += `&client_id=${clientId}`;
            if (productQuery) url += `&product_query=${encodeURIComponent(productQuery)}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
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
                                <td>${sale.sale_date}</td>
                            `;
                            salesReportTable.appendChild(row);
                        });
                    } else {
                        salesReportTable.innerHTML = '<tr><td colspan="9" class="text-center">No sales found for the selected criteria.</td></tr>';
                    }
                })
                .catch(error => {
                    logError('Error fetching sales report: ' + error.message);
                    alert('Error fetching sales report: ' + error.message);
                });
        }

        function exportCsv() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const clientId = clientSelect.value;
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            let url = `api.php?action=export_sales_report_csv&start_date=${startDate}&end_date=${endDate}`;
            if (clientId) url += `&client_id=${clientId}`;
            window.location.href = url;
        }

        function exportPdf() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const clientId = clientSelect.value;
            const productQuery = productQueryInput.value.trim();
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            const payload = {
                action: 'generate_sales_report_pdf',
                start_date: startDate,
                end_date: endDate,
                client_id: clientId || null,
                product_query: productQuery || '',
                csrf_token: csrfToken
            };
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to generate PDF');
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

        fetchReportBtn.addEventListener('click', fetchSalesReport);
        exportCsvBtn.addEventListener('click', exportCsv);
        exportPdfBtn.addEventListener('click', exportPdf);

        // Initial fetch
        fetchSalesReport();
    </script>
</body>
</html>