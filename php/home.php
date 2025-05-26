<?php
$title = "Home - Compare IT";
$cssFile = "home.css";
require_once 'header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <h1>Find the Best Deals on IT Products</h1>
        <p>Compare prices from multiple retailers in one place</p>
        <a href="products.php" class="cta-button">Browse Products</a>
    </div>
</div>

<!-- Featured Products -->

<div class="featured-section">
    <h2>Featured Products</h2>
    <div id="featuredProductContainer" class="featured-grid">
      </div>
</div>

<!-- Why Choose Us -->
<div class="benefits-section">
    <h2>Why Choose Compare IT?</h2>
    <div class="benefits-grid">
        <div class="benefit-card">
            <i class="fas fa-percentage"></i>
            <h3>Best Prices</h3>
            <p>We compare prices across multiple retailers to ensure you get the best deal</p>
        </div>
        <div class="benefit-card">
            <i class="fas fa-clock"></i>
            <h3>Save Time</h3>
            <p>No need to visit multiple websites - find all prices in one place</p>
        </div>
        <div class="benefit-card">
            <i class="fas fa-heart"></i>
            <h3>Wishlist</h3>
            <p>Save your favorite products and track price changes</p>
        </div>
    </div>
</div>
<script src="../js/home.js"></script>
<?php require_once 'footer.php'; ?>
</body>
</html>
