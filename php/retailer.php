<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


ini_set('log_errors', 1); // Enable logging
ini_set('error_log', __DIR__ . '/../error.log'); // Path to your log file
error_reporting(E_ALL); // Report all types of errors

if (!isset($_COOKIE['api_key']) || !isset($_COOKIE['isAdmin']) || $_COOKIE['isAdmin'] === 'false')
{
    $userKey = $_COOKIE['apiKey'] ?? 'unknown key';

    error_log("Unauthorized attempt to access admin page by " . $userKey);

    header('Location: logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Products Management</title>
    <link rel="stylesheet" href="../css/admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
    <div class="background-overlay"></div>

    <nav class="top-navbar">
        <div class="brand">
            <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo" />
            <span>Admin Dashboard</span>
        </div>
        <ul class="nav-links">
            <li><a href="admin.php">PRODUCTS</a></li>
            <li><a href="retailers.php">RETAILERS</a></li>
            <li><a href="products.php">CUSTOMER STOREFRONT</a></li>

        </ul>
        <div class="admin-controls">
            <button class="admin-btn" id="addProductBtn">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
            <button class="admin-btn" id="addRetailerBtn">
                <i class="fas fa-tag"></i> Add Retailer
            </button>
            <button class="admin-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </nav>

    <div class="search-container">
        <form id="search-form" class="search-form">
            <input
                type="text"
                id="search-input"
                placeholder="Search products..."
                class="search-input"
            />
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <script src="../js/retailer.js"></script>
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    </script>
</body>
</html>