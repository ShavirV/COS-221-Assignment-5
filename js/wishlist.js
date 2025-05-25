document.addEventListener("DOMContentLoaded", function () {
  const wishlistItems = document.getElementById("wishlistItems");
  const itemCountElement = document.querySelector(".item-count");
  const sortBtn = document.querySelector(".sort-btn");
  const filterBtn = document.querySelector(".filter-btn");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");

  const apiKey = getCookie('api_key');
  
  if (!apiKey) {
    showLoginPrompt();
    return;
  }

  //let apiKey = getCookie('api_key'); // Get API key from cookie
  let wishlistProducts = [];
  let displayedProducts = [];
  let productBrands = new Set();

  // Sort options
  const sortOptions = {
    default: "Default",
    "name-asc": "Name (A-Z)",
    "name-desc": "Name (Z-A)",
    "price-asc": "Price (Low to High)",
    "price-desc": "Price (High to Low)",
  };

  // Current selections
  let currentSort = "default";
  let currentFilter = "all";

  // Initialize the page
  async function init() {
    if (!apiKey) {
      showLoginPrompt();
      return;
    }
    await fetchWishlist();
    setupEventListeners();
  }

  function showLoginPrompt() {
    wishlistItems.innerHTML = `
      <div class="empty-wishlist">
        <i class="fas fa-heart-broken"></i>
        <p>Please log in to view your wishlist</p>
        <button class="browse-btn" id="loginRedirect">Login</button>
        <button class="browse-btn" id="continueShopping">Continue Shopping</button>
      </div>
    `;
    
    document.getElementById('loginRedirect').addEventListener('click', () => {
      window.location.href = 'login.php';
    });
    
    document.getElementById('continueShopping').addEventListener('click', () => {
      window.location.href = 'products.php';
    });
    
    itemCountElement.textContent = "0 items";
  }

  // Fetch wishlist from API
  async function fetchWishlist() {
    try {
      wishlistItems.innerHTML = `
        <div class="loading-products">
          <div class="spinner"></div>
          <p>Loading your wishlist...</p>
        </div>
      `;

      const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          type: 'GetWishlist',
          api_key: apiKey
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      if (response.status === 204) {
        renderWishlist();
        return;
      }

      const data = await response.json();
      
      if (data.status === 'success') {
        wishlistProducts = data.data;
        
        // Fetch prices for each product in wishlist
        await fetchPricesForWishlist();
        
        // Get unique brands for filtering
        wishlistProducts.forEach(product => {
          if (product.brand) {
            productBrands.add(product.brand);
          }
        });
        
        displayedProducts = [...wishlistProducts];
        renderWishlist();
      } else {
        throw new Error(data.message || 'Failed to fetch wishlist');
      }
    } catch (error) {
      console.error('Error fetching wishlist:', error);
      showErrorState(error);
    }
  }

  // Fetch prices for wishlist items
  async function fetchPricesForWishlist() {
    try {
      const pricePromises = wishlistProducts.map(product => {
        const requestBody = {
          type: 'GetBestOffer',
          product_id: String(product.product_id)
        };

        return fetch('../api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(requestBody)
        })
        .then(response => {
          if (!response.ok) {
            console.error(`Failed to fetch price for product ${product.product_id}`, response.status);
            return null;
          }
          return response.json();
        })
        .catch(error => {
          console.error(`Error fetching price for product ${product.product_id}:`, error);
          return null;
        });
      });

      const priceResponses = await Promise.all(pricePromises);

      priceResponses.forEach((response, index) => {
        if (response && response.status === 'success') {
          if (typeof response.data === 'object' && !Array.isArray(response.data) && response.data.price) {
            wishlistProducts[index].price = parseFloat(response.data.price);
            wishlistProducts[index].currency = response.data.currency || 'ZAR';
            wishlistProducts[index].hasStock = true;
          } else if (typeof response.data === 'string') {
            wishlistProducts[index].price = null;
            wishlistProducts[index].hasStock = false;
          } else {
            wishlistProducts[index].price = null;
            wishlistProducts[index].hasStock = false;
            console.warn('Unexpected response format for product', wishlistProducts[index].product_id, response);
          }
        } else {
          wishlistProducts[index].price = null;
          wishlistProducts[index].hasStock = false;
          if (response) {
            console.warn('Error response for product', wishlistProducts[index].product_id, response);
          }
        }
      });

      displayedProducts = [...wishlistProducts];
      renderWishlist();
    } catch (error) {
      console.error('Error fetching prices:', error);
      renderWishlist(); // Render with what we have
    }
  }

  function showErrorState(error) {
    wishlistItems.innerHTML = `
      <div class="error-products">
        <i class="fas fa-exclamation-triangle"></i>
        <p>Failed to load wishlist</p>
        <p>${error.message}</p>
        <button class="retry-btn" id="retryFetch">Try Again</button>
      </div>
    `;
    
    document.getElementById("retryFetch").addEventListener("click", fetchWishlist);
  }

  // Set up event listeners
  function setupEventListeners() {
    sortBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      showSortDropdown();
    });

    filterBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      showFilterDropdown();
    });

    document.addEventListener("click", closeDropdowns);

    searchInput.addEventListener("input", function () {
      const searchTerm = searchInput.value.trim().toLowerCase();
      let filteredProducts = [...wishlistProducts];

      if (searchTerm) {
        filteredProducts = filteredProducts.filter((product) =>
          product.name.toLowerCase().includes(searchTerm) ||
          product.description.toLowerCase().includes(searchTerm) ||
          product.brand.toLowerCase().includes(searchTerm)
        );
      }

      if (currentFilter !== "all") {
        filteredProducts = filteredProducts.filter(
          (product) => product.brand === currentFilter
        );
      }

      displayedProducts = filteredProducts;
      applySortAndFilter();
    });
  }

  // Remove item from wishlist
  async function removeFromWishlist(productId) {
    try {
      const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          type: 'DeleteFromWishlist',
          api_key: apiKey,
          product_id: productId
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.status === 'success') {
        // Remove from local array
        const index = wishlistProducts.findIndex(p => p.product_id === productId);
        if (index !== -1) {
          wishlistProducts.splice(index, 1);
          displayedProducts = [...wishlistProducts];
          renderWishlist();
        }
      } else {
        throw new Error(data.message || 'Failed to remove from wishlist');
      }
    } catch (error) {
      console.error('Error removing from wishlist:', error);
      alert('Failed to remove from wishlist. Please try again.');
    }
  }

  // Show sort dropdown
  function showSortDropdown() {
    closeDropdowns();

    const dropdown = document.createElement("div");
    dropdown.className = "sort-dropdown dropdown";
    dropdown.innerHTML = Object.entries(sortOptions)
      .map(
        ([value, text]) => `
        <div class="dropdown-item ${value === currentSort ? "active" : ""}" 
             data-value="${value}">
          ${text}
        </div>
      `
      )
      .join("");

    sortBtn.appendChild(dropdown);

    dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", function () {
        currentSort = this.getAttribute("data-value");
        applySortAndFilter();
        closeDropdowns();
      });
    });
  }

  // Show filter dropdown
  function showFilterDropdown() {
    closeDropdowns();

    const dropdown = document.createElement("div");
    dropdown.className = "filter-dropdown dropdown";
    
    let filterOptionsHTML = `
      <div class="dropdown-item ${"all" === currentFilter ? "active" : ""}" 
           data-value="all">
        All Brands
      </div>
    `;
    
    Array.from(productBrands).forEach(brand => {
      filterOptionsHTML += `
        <div class="dropdown-item ${brand === currentFilter ? "active" : ""}" 
             data-value="${brand}">
          ${brand}
        </div>
      `;
    });

    dropdown.innerHTML = filterOptionsHTML;
    filterBtn.appendChild(dropdown);

    dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", function () {
        currentFilter = this.getAttribute("data-value");
        applySortAndFilter();
        closeDropdowns();
      });
    });
  }

  // Close all dropdowns
  function closeDropdowns() {
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
      dropdown.remove();
    });
  }

  // Apply current sort and filter
  function applySortAndFilter() {
    // Filter first
    let products = [...wishlistProducts];
    if (currentFilter !== "all") {
      products = products.filter(
        (product) => product.brand === currentFilter
      );
    }

    // Then sort
    switch (currentSort) {
      case "name-asc":
        products.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "name-desc":
        products.sort((a, b) => b.name.localeCompare(a.name));
        break;
      case "price-asc":
        products.sort((a, b) => (a.price || 0) - (b.price || 0));
        break;
      case "price-desc":
        products.sort((a, b) => (b.price || 0) - (a.price || 0));
        break;
    }

    displayedProducts = products;
    renderWishlist();
  }

  // Render wishlist items
  function renderWishlist() {
    wishlistItems.innerHTML = "";

    if (displayedProducts.length === 0) {
      wishlistItems.innerHTML = `
        <div class="empty-wishlist">
          <i class="fas fa-heart-broken"></i>
          <p>No items in your wishlist</p>
          <button class="browse-btn" onclick="window.location.href='products.php'">Browse Products</button>
        </div>
      `;

      itemCountElement.textContent = "0 items";
      return;
    }

    itemCountElement.textContent = `${displayedProducts.length} ${
      displayedProducts.length === 1 ? "item" : "items"
    }`;

    displayedProducts.forEach((product) => {
      const item = document.createElement("div");
      item.className = "wishlist-card";
      
      // Price display logic
      let priceDisplay = '';
      if (product.price !== null) {
        priceDisplay = `
          <div class="card-price">${product.currency || 'ZAR'} ${product.price.toFixed(2)}</div>
        `;
      } else {
        priceDisplay = `
          <div class="card-price out-of-stock">Out of stock</div>
        `;
      }

      item.innerHTML = `
        <span class="card-badge">${product.brand || 'No Brand'}</span>
        <div class="card-image-container">
          <img src="${product.image_url || 'https://via.placeholder.com/300?text=No+Image'}" 
               alt="${product.name}" class="card-image">
        </div>
        <div class="card-details">
          <h3 class="card-title">${product.name}</h3>
          ${priceDisplay}
          <div class="card-actions">
            <button class="add-to-view" data-id="${product.product_id}">Compare</button>
            <button class="remove-btn" data-id="${product.product_id}">
              <i class="fas fa-heart-broken"></i>
            </button>
          </div>
        </div>
      `;
      wishlistItems.appendChild(item);
    });

    // Add event listeners to remove buttons
    document.querySelectorAll(".remove-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const productId = parseInt(this.getAttribute("data-id"));
        removeFromWishlist(productId);
      });
    });

    // Add event listeners to view buttons
    document.querySelectorAll(".add-to-view").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        const productId = parseInt(this.getAttribute("data-id"));
        setCookie("productId", productId, 2);
        window.location.href = '../php/view.php';
      });
    });
  }

  // Initialize the page
  init();
});

function setCookie(name, value, time) {
  let expires = "";
  if (time) {
    const date = new Date();
    date.setTime(date.getTime() + time * 60 * 60 * 1000);
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for(let i=0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1);
    if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
  }
  return null;
}
