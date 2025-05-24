<?php
// Set page-specific variables before including header
$pageTitle = "Product View - Compare IT";
$cssFile = 'view.css'; // Array of CSS files needed for this page
require_once("header.php");

if (!isset($_COOKIE['productId']))
{
    header('Location: home.php');
    exit;
}
?>

<!-- view page main content -->
<div class="product-container">
    <div class="product-images">
        <img id="main-image" class="main-image" src="<?php echo isset($_SESSION['current_product']['main_image']) ? $_SESSION['current_product']['main_image'] : ''; ?>" alt="Main Product Image">
        <div class="thumbnail-container" id="thumbnail-container">
            <?php
            if (isset($_SESSION['current_product']['thumbnails'])) {
                foreach ($_SESSION['current_product']['thumbnails'] as $thumb) {
                    echo '<img src="'.$thumb.'" class="thumbnail" alt="Product Thumbnail">';
                }
            }
            ?>
        </div>
    </div>
    
    <div class="product-info">
        <h1 id="product-title"><?php echo isset($_SESSION['current_product']['name']) ? $_SESSION['current_product']['name'] : 'Product Title'; ?></h1>
        <div class="price" id="price"><?php echo isset($_SESSION['current_product']['price']) ? '$'.number_format($_SESSION['current_product']['price'], 2) : '$0.00'; ?></div>
        <div class="description" id="description"><?php echo isset($_SESSION['current_product']['description']) ? $_SESSION['current_product']['description'] : 'Product description will load here...'; ?></div>
        <div class="rating">
            <span class="stars">★★★★★</span>
            <span class="review-count">(0 reviews)</span>
        </div>
        <button class="add-to-wishlist" id="add-to-wishlist">❤</button>
    </div>
</div>

<div class="offers-section">
    <h2>Available Offers</h2>
    <div class="offers-container" id="offers-container">
        <?php
        if (isset($_SESSION['current_product']['offers'])) {
            foreach ($_SESSION['current_product']['offers'] as $offer) {
                echo '<div class="offer">';
                echo '<h3>'.$offer['store'].'</h3>';
                echo '<p>'.$offer['price'].'</p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</div>

<div class="reviews-section">
    <h2>Customer Reviews</h2>
    <div class="reviews-container" id="reviews-container">
        <?php
        if (isset($_SESSION['current_product']['reviews'])) {
            foreach ($_SESSION['current_product']['reviews'] as $review) {
                echo '<div class="review">';
                echo '<div class="review-rating">'.str_repeat('★', $review['rating']).str_repeat('☆', 5 - $review['rating']).'</div>';
                echo '<p class="review-text">'.$review['text'].'</p>';
                echo '</div>';
            }
        }
        ?>
    </div>
    <button id="leave-review-btn" class="leave-review-btn">Leave a Review</button>
    <div class="review-form-container" id="review-form-container" style="display: none;">
        <h3>Write Your Review</h3>
        <form id="review-form" method="POST" action="submit_review.php">
            <div class="form-group">
                <label for="review-rating">Rating:</label>
                <select id="review-rating" name="review-rating" required>
                    <option value="">Select rating</option>
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★☆</option>
                    <option value="3">★★★☆☆</option>
                    <option value="2">★★☆☆☆</option>
                    <option value="1">★☆☆☆☆</option>
                </select>
            </div>
            <div class="form-group">
                <label for="review-text">Review:</label>
                <textarea id="review-text" name="review-text" rows="4" required></textarea>
            </div>
            <button type="submit" class="submit-review">Submit Review</button>
        </form>
    </div>
</div>

<script src="../js/view.js"></script>
<?php require_once 'footer.php'; ?>
</body>
</html>