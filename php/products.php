<?php
$title = "Products - Compare IT";
$cssFile = "products.css"; 
require_once 'header.php';
?>

<!-- Search Bar -->
<div class="search-container">
    <form id="search-form" class="search-form">
        <input type="text" id="search-input" placeholder="Search products..." class="search-input">
        <button type="submit" class="search-button">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<!-- Main Content -->
<div class="products-container">
    <div class="products-header">
        <h1><i class="fas fa-box-open"></i> Our Products</h1>
        <div class="products-controls">
            <span class="item-count">6 items</span>
            <div class="sort-filter">
                <button class="sort-btn"><i class="fas fa-sort"></i> Sort</button>
                <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
    </div>

    <div class="products-grid" id="productsItems">
        <!-- Products will be dynamically inserted here -->
    </div>
</div>

<script src="../js/products.js"></script>
</body>
</html>