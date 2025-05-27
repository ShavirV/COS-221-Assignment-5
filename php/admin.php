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
    <div class="background-overlay"></div>

    <nav class="top-navbar">
      <div class="brand">
        <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo" />
        <span>Admin Dashboard</span>
      </div>
      <ul class="nav-links">
        <li><a href="products.php">CUSTOMER STOREFRONT</a></li>
      </ul>
      <div class="admin-controls">
        <button class="admin-btn" id="addProductBtn">
          <i class="fas fa-plus-circle"></i> Add Product
        </button>
        <button class="admin-btn" id="addRetailerBtn">
          <i class="fas fa-store"></i> Add Retailer
        </button>
        <button class="admin-btn" id="logoutBtn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </div>
    </nav>

    <div class="search-container" style="visibility: hidden;">
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
          <div class="sort-filter" style="visibility: hidden;">
            <button class="sort-btn"><i class="fas fa-sort"></i> Sort</button>
            <button class="filter-btn" style="visibility: hidden;">
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
        <form id="productForm">
          <input type="hidden" id="productId" />
          <div class="form-group">
            <label for="productName">Product Name</label>
            <input type="text" id="productName" required />
          </div>
          <div class="form-group">
            <label for="productCategory">Category</label>
            <input type="text" id="productCategory" required />
          </div>
          <div class="form-group">
            <label for="productImage">Image URL</label>
            <input type="text" id="productImage" required />
          </div>
          <div class="form-group">
            <label for="productDescription">Description</label>
            <textarea id="productDescription" rows="4" required></textarea>
          </div>
          <div class="form-group">
            <label for="productBrand">Brand</label>
            <input type="text" id="productBrand" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="save-btn" id="edit-save">Save</button>
            <button type="button" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-overlay" id="retailerModal">
      <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="retailerModalTitle">Add New Retailer</h2>
        <form id="retailerForm">
          <input type="hidden" id="retailerId" />
          <div class="form-group">
            <label for="retailerName">Retailer Name</label>
            <input type="text" id="retailerName" required />
          </div>
          <div class="form-group">
            <label for="retailerType">Retailer Type</label>
            <input type="text" id="retailerType" required />
          </div>
          <div class="form-group">
            <label for="openingTime">Opening Time</label>
            <input type="time" id="openingTime" required />
          </div>
          <div class="form-group">
            <label for="closingTime">Closing Time</label>
            <input type="time" id="closingTime" required />
          </div>
          <div class="form-group">
            <label for="retailerAddress">Address</label>
            <input type="text" id="retailerAddress" required />
          </div>
          <div class="form-group">
            <label for="postalCode">Postal Code</label>
            <input type="text" id="postalCode" required />
          </div>
          <div class="form-group">
            <label for="retailerWebsite">Website</label>
            <input type="url" id="retailerWebsite" required />
          </div>
          <div class="form-group">
            <label for="retailerCountry">Country</label>
            <input type="text" id="retailerCountry" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="save-btn">Save</button>
            <button type="button" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Offers Modal -->
<div id="offersModal" class="modal-overlay">
  <div class="modal-content">
    <span class="close-modal" id="closeOffersModal">&times;</span>
    <h2>Offers</h2>

    <!-- All Offers Section -->
<div id="allOffersSection">
  <h3>All Offers</h3>
  <div class="form-group">
    <label for="offerSelect">Select Existing Offer:</label>
    <select id="offerSelect">
      <option value="">-- Choose an Offer --</option>
    </select>
  </div>
</div>


    <!-- New Offer Section -->
    <div id="newOfferSection">
      <h3>Add New Offer</h3>
      <form id="newOfferForm">
        <div class="form-group">
          <label for="retailerSelect">Retailer:</label>
          <select id="retailerSelect" name="retailer"></select>
        </div>
        <div class="form-group">
          <label for="stockInput">Stock:</label>
          <input type="number" id="stockInput" name="stock" required>
        </div>
        <div class="form-group">
          <label for="priceInput">Price:</label>
          <input type="number" id="priceInput" name="price" step="0.01" required>
        </div>
        <div class="form-actions">
          <button type="submit" class="save-btn">Add Offer</button>
        </div>
      </form>
    </div>
  </div>
</div>


    <script src="../js/admin.js"></script>
</body>
</html>