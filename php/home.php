<?php
$title = "Home - Compare IT";
$cssFile = "home.css";
require_once 'header.php';

// Mock product data
$featuredProducts = [
    [
        'id' => 1,
        'name' => 'Wireless Gaming Mouse',
        'price' => 59.99,
        'image' => 'img/products/mouse.jpg',
        'brand' => 'Logitech'
    ],
    [
        'id' => 2,
        'name' => 'Mechanical Keyboard',
        'price' => 89.99,
        'image' => 'img/products/keyboard.jpg',
        'brand' => 'Razer'
    ],
    [
        'id' => 3,
        'name' => 'Noise Cancelling Headphones',
        'price' => 199.99,
        'image' => 'img/products/headphones.jpg',
        'brand' => 'Sony'
    ],
    [
        'id' => 4,
        'name' => '4K Webcam',
        'price' => 129.99,
        'image' => 'img/products/webcam.jpg',
        'brand' => 'Logitech'
    ]
];
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
    <div class="featured-grid">
        <?php foreach ($featuredProducts as $product): ?>
        <div class="product-card">
            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            <div class="product-info">
                <h3><?php echo $product['name']; ?></h3>
                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                <div class="product-brand"><?php echo $product['brand']; ?></div>
                <button class="view-btn" onclick="window.location.href='view.php?id=<?php echo $product['id']; ?>'">View Details</button>
            </div>
        </div>
        <?php endforeach; ?>
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