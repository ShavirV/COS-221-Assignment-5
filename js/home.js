document.addEventListener("DOMContentLoaded", function() {
    const featuredContainer = document.getElementById('featuredProductContainer');
    let allProducts = [];
    let currentIndex = 0;

    // Fetch products from API
    async function fetchFeaturedProducts() {
        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'GetAllProducts',
                    return: ['product_id', 'name', 'description', 'brand', 'image_url'],
                    limit: 50
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                allProducts = data.data;
                if (allProducts.length > 0) {
                    // Start rotating featured products
                    displayRandomProduct();
                    setInterval(displayRandomProduct, 10000); // Change every 10 seconds
                } else {
                    showNoProducts();
                }
            } else {
                throw new Error(data.message || 'Unknown API error');
            }
        } catch (error) {
            console.error('Error fetching featured products:', error);
            showErrorState(error);
        }
    }

    // Display a random product
    // Display a random product with transitions
function displayRandomProduct() {
    if (allProducts.length === 0) return;
    
    // Get current active card
    const currentCard = featuredContainer.querySelector('.product-card.active');
    
    // Fade out current card if exists
    if (currentCard) {
        currentCard.classList.remove('active');
        currentCard.classList.add('fade-out');
        
        // Remove the card after animation completes
        setTimeout(() => {
            featuredContainer.removeChild(currentCard);
        }, 500);
    }
    
    // Get a random product (or cycle through them sequentially)
    currentIndex = (currentIndex + 1) % allProducts.length;
    const product = allProducts[currentIndex];
    
    // Fetch the price for this product
    fetchProductPrice(product.product_id).then(priceData => {
        const priceDisplay = priceData && priceData.price 
            ? `<div class="product-price">${priceData.price.toFixed(2)} ${priceData.currency || 'ZAR'}</div>`
            : '<div class="product-price out-of-stock">Out of stock</div>';

        // Create new product card
        const productCard = document.createElement('div');
        productCard.className = 'product-card fade-in';
        // Inside displayRandomProduct function:
        productCard.innerHTML = `
            <div class="product-media">
                <img src="${product.image_url || 'https://via.placeholder.com/300?text=No+Image'}" alt="${product.name}">
                <h3 class="product-title">${product.name}</h3>
                ${product.brand ? `<div class="product-brand">${product.brand}</div>` : ''}
                ${priceDisplay}
            </div>
            <div class="product-description-box">
                <div class="product-description">${product.description || 'No description available'}</div>
                <button class="view-btn" onclick="setProductAndRedirect(${product.product_id})">View Details</button>
            </div>
        `;
        
        // Add to container
        featuredContainer.appendChild(productCard);
        
        // Activate after a small delay to allow DOM to update
        setTimeout(() => {
            productCard.classList.remove('fade-in');
            productCard.classList.add('active');
        }, 10);
    });
}

    // Fetch price for a product
    async function fetchProductPrice(productId) {
        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'GetBestOffer',
                    product_id: String(productId)
                })
            });

            if (!response.ok) {
                return null;
            }

            const data = await response.json();
            if (data.status === 'success' && typeof data.data === 'object' && data.data.price) {
                return {
                    price: parseFloat(data.data.price),
                    currency: data.data.currency || 'ZAR'
                };
            }
            return null;
        } catch (error) {
            console.error('Error fetching product price:', error);
            return null;
        }
    }

    function showErrorState(error) {
        featuredContainer.innerHTML = `
            <div class="error-products">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Failed to load featured products</p>
                <p>${error.message}</p>
                <button class="retry-btn" id="retryFeatured">Try Again</button>
            </div>
        `;
        
        document.getElementById("retryFeatured").addEventListener("click", fetchFeaturedProducts);
    }

    function showNoProducts() {
        featuredContainer.innerHTML = `
            <div class="empty-products">
                <i class="fas fa-box-open"></i>
                <p>No featured products available</p>
            </div>
        `;
    }
    

    // Initialize
    fetchFeaturedProducts();
});

function setProductAndRedirect(productId) {
    document.cookie = `productId=${productId}; path=/`;
    window.location.href = 'view.php';
}