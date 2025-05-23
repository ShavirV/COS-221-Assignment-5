<?php
session_start();
// Add admin authentication check
if (!isset($_COOKIE['isAdmin']) || $_COOKIE['isAdmin'] == 'false')
 {
    header('Location: login.php');
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
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
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

      <div class="products-grid" id="productsItems">
        <?php
        // Example PHP code to display products from database
        // This would be replaced with actual database connection
        if (isset($_SESSION['products'])) {
            foreach ($_SESSION['products'] as $product) {
                echo '<div class="product-item">';
                echo '<img src="'.$product['image'].'" alt="'.$product['name'].'">';
                echo '<h3>'.$product['name'].'</h3>';
                echo '<p>'.$product['price'].'</p>';
                echo '</div>';
            }
        }
        ?>
      </div>
    </div>

    <div class="modal-overlay" id="productModal">
      <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTitle">Add New Product</h2>
        <form id="productForm" method="POST" action="admin_process.php">
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

    <script src="../js/admin.js"></script>
  </body>
</html>