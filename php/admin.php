<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


ini_set('log_errors', 1); // Enable logging
ini_set('error_log', __DIR__ . '/../error.log'); // Path to your log file
error_reporting(E_ALL); // Report all types of errors

if (!isset($_COOKIE['apiKey']) || !isset($_COOKIE['isAdmin']) || $_COOKIE['isAdmin'] === 'false')
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
            <li><a href="products.php">PRODUCTS</a></li>
        </ul>
        <div class="admin-controls">
            <button class="admin-btn" id="addProductBtn">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
            <button class="admin-btn" id="editOfferBtn">
                <i class="fas fa-tag"></i> Edit Offers
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

    <div class="products-container">
        <div class="products-header">
            <h1><i class="fas fa-boxes"></i> Products Management</h1>
            <div class="products-controls">
                <span class="item-count">6 items</span>
                <div class="sort-filter">
                    <button class="sort-btn"><i class="fas fa-sort"></i> Sort</button>
                    <button class="filter-btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="products-grid" id="productsItems"></div>
    </div>

    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Add New Product</h2>
            <form id="productForm" action="process_product.php" method="POST">
                <input type="hidden" id="productId" name="productId" />
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" required />
                </div>
                <div class="form-group">
                    <label for="productPrice">Price</label>
                    <input type="number" id="productPrice" name="productPrice" step="0.01" required />
                </div>
                <div class="form-group">
                    <label for="productCategory">Category</label>
                    <select id="productCategory" name="productCategory" required>
                        <option value="">Select Category</option>
                        <option value="Television">Television</option>
                        <option value="Audio">Audio</option>
                        <option value="Computers">Computers</option>
                        <option value="Phones">Phones</option>
                        <option value="Cameras">Cameras</option>
                        <option value="Wearables">Wearables</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productImage">Image URL</label>
                    <input type="text" id="productImage" name="productImage" required />
                </div>
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" name="productDescription" rows="4" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="save-btn">Save</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="offerModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Manage Product Offers</h2>
            <form id="offerForm" action="process_offer.php" method="POST">
                <div class="form-group">
                    <label for="offerProduct">Product</label>
                    <select id="offerProduct" name="offerProduct" required>
                        <option value="">Select Product</option>
                        <?php
                        // You would typically fetch products from database here
                        // Example:
                        // $products = getProductsFromDatabase();
                        // foreach ($products as $product) {
                        //     echo '<option value="'.$product['id'].'">'.$product['name'].'</option>';
                        // }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="offerRetailer">Retailer</label>
                    <input type="text" id="offerRetailer" name="offerRetailer" required />
                </div>
                <div class="form-group">
                    <label for="offerPrice">Offer Price</label>
                    <input type="number" id="offerPrice" name="offerPrice" step="0.01" required />
                </div>
                <div class="form-actions">
                    <button type="submit" class="save-btn">Save Offer</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    </script>
</body>
</html>