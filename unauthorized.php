<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .unauthorized-container {
            max-width: 600px;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Unauthorized Access</h4>
            <p>You do not have permission to access this page or perform this action.</p>
            <hr>
            <p class="mb-0">If you believe this is an error, please contact your system administrator.</p>
        </div>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>