<?php
// session_start();
$title = "WishList - Compare IT";
$cssFile = "wishlist.css"; // CSS file for wishlist page
require_once("header.php");

?>


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
    <?php require_once 'footer.php'; ?>
  </body>
</html>