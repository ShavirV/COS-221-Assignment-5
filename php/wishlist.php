<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wishlist - Compare IT</title>
    <link rel="stylesheet" href="../css/wishlist.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body>
    <div class="background-overlay"></div>

    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
      <div class="brand">
        <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo" />
        <span>Compare IT</span>
      </div>
      <ul class="nav-links">
        <li><a href="home.php">HOME</a></li>
        <li><a href="products.php">PRODUCTS</a></li>
        <li><a href="wishlist.php" class="active">WISHLIST</a></li>
        <li><a href="aboutUs.php">ABOUT US</a></li>
        <li><a href="login.php">LOGIN</a></li>
        <li><a href="signup.php">SIGN UP</a></li>
      </ul>
    </nav>

    <!-- Search Bar -->
    <div class="search-container">
      <form id="search-form" class="search-form">
        <input
          type="text"
          id="search-input"
          placeholder="Search your wishlist..."
          class="search-input"
        />
        <button type="submit" class="search-button">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>

    <!-- Main Content -->
    <div class="wishlist-container">
      <div class="wishlist-header">
        <h1><i class="fas fa-heart"></i> Your Wishlist</h1>
        <div class="wishlist-controls">
          <span class="item-count">6 items</span>
          <div class="sort-filter">
            <button class="sort-btn"><i class="fas fa-sort"></i> Sort</button>
            <button class="filter-btn">
              <i class="fas fa-filter"></i> Filter
            </button>
          </div>
        </div>
      </div>

      <div class="wishlist-grid" id="wishlistItems">
        <!-- Wishlist items will be dynamically inserted here -->
        <?php
        // Example PHP code to display wishlist items from session
        if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])) {
            foreach ($_SESSION['wishlist'] as $item) {
                echo '<div class="wishlist-item">';
                echo '<img src="'.$item['image'].'" alt="'.$item['name'].'">';
                echo '<h3>'.$item['name'].'</h3>';
                echo '<p>'.$item['price'].'</p>';
                echo '</div>';
            }
        } else {
            echo '<p class="empty-wishlist">Your wishlist is empty</p>';
        }
        ?>
      </div>
    </div>

    <script src="../js/wishlist.js"></script>
  </body>
</html>