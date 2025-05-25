document.addEventListener("DOMContentLoaded", function () {
  const productsItems = document.getElementById("productsItems");
  const itemCountElement = document.querySelector(".item-count");
  const sortBtn = document.querySelector(".sort-btn");
  const filterBtn = document.querySelector(".filter-btn");
  const searchInput = document.getElementById("search-input");

  const apiKey = getCookie('api_key');
  
  // validation check for api key -> debug stuff, can keep tho
  if (!apiKey) 
  {
    console.warn('No API key found - some features will be disabled');
  }

  // data pulled from api
  let allProducts = [];
  let displayedProducts = [];
  let productBrands = new Set(); 
  let productPrices = {};
  // dont uncomment, doesnt work this way
  //let apiKey = getCookie('api_key'); 

  let currentSort = "default";
  let currentFilter = "all";

  // Sort options
  const sortOptions = {
    default: "Default",
    "name-asc": "Name (A-Z)",
    "name-desc": "Name (Z-A)",
  };

  // init  page
  async function init() {
    await fetchProducts();
    await fetchPrices();
    setupEventListeners();
  }

  // fetch goodies from API
  async function fetchProducts() {
    try {
      productsItems.innerHTML = `
        <div class="loading-products">
          <div class="spinner"></div>
          <p>Loading products...</p>
        </div>
      `;

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

      if (!response.ok) 
      {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.status === 'success') 
      {
        allProducts = data.data.map(product => ({
          id: product.product_id,
          name: product.name,
          description: product.description,
          brand: product.brand,
          image: product.image_url,
          price: null,
          // set all to false, cookie will store boolean after
          inWishlist: false 
        }));
        
        // Get unique brands for filtering
        allProducts.forEach(product => {
          if (product.brand) {
            productBrands.add(product.brand);
          }
        });
        
        displayedProducts = [...allProducts];
        
        // login apikey debuggin, validation -> keep these types of checks!
        if (apiKey) 
        {
          await checkWishlistStatus();
        } 
        
        else 
        {
          renderProducts();
        }
      } 
      
      else 
      {
        throw new Error(data.message || 'Unknown API error');
      }
    } catch (error) {
      console.error('Error fetching products:', error);
      showErrorState(error);
    }
  }

  // cehck which products in whislist
  async function checkWishlistStatus() {
    try {
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

      if (!response.ok) 
      {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.status === 'success') 
      {
        const wishlistProductIds = data.data.map(item => item.product_id);
        
        // update inWishlist T/F for each product
        allProducts.forEach(product => {
          product.inWishlist = wishlistProductIds.includes(product.id);
        });
        
        displayedProducts = [...allProducts];
        renderProducts();
      } 
      
      else 
      {
        throw new Error(data.message || 'Failed to fetch wishlist');
      }
    } catch (error) {
      console.error('Error checking wishlist:', error);
      renderProducts(); // Render anyway
    }
  }

  async function fetchPrices() {
    try {
      const pricePromises = allProducts.map(product => {
        const requestBody = {
          type: 'GetBestOffer',
          product_id: String(product.id) 
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
            console.error(`Failed to fetch price for product ${product.id}`, response.status);
            return null; 
          }
          return response.json();
        })
        .catch(error => {
          console.error(`Error fetching price for product ${product.id}:`, error);
          return null;
        });
      });

      const priceResponses = await Promise.all(pricePromises);

      priceResponses.forEach((response, index) => {
        if (response && response.status === 'success') 
        {
          if (typeof response.data === 'object' && !Array.isArray(response.data) && response.data.price)
          {
            allProducts[index].price = parseFloat(response.data.price);
            allProducts[index].currency = response.data.currency || 'ZAR';
            allProducts[index].hasStock = true;
          } 
          
          else if (typeof response.data === 'string') 
          {
            allProducts[index].price = null;
            allProducts[index].hasStock = false;
          } 
          
          else 
          {
            allProducts[index].price = null;
            allProducts[index].hasStock = false;
            console.warn('Unexpected response format for product', allProducts[index].id, response);
          }
        } 
        
        else 
        {
          allProducts[index].price = null;
          allProducts[index].hasStock = false;
          if (response) 
          {
            console.warn('Error response for product', allProducts[index].id, response);
          }
        }
      });

      displayedProducts = [...allProducts];
      renderProducts();
    } catch (error) {
      console.error('Error in fetchPrices:', error);
      renderProducts();
    }
  }

  function showErrorState(error) {
    productsItems.innerHTML = `
      <div class="error-products">
        <i class="fas fa-exclamation-triangle"></i>
        <p>Failed to load products</p>
        <p>${error.message}</p>
        <button class="retry-btn" id="retryFetch">Try Again</button>
      </div>
    `;
    
    document.getElementById("retryFetch").addEventListener("click", fetchProducts);
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
      let filteredProducts = [...allProducts];

      if (searchTerm) 
      {
        filteredProducts = filteredProducts.filter((product) =>
          product.name.toLowerCase().includes(searchTerm) ||
          product.description.toLowerCase().includes(searchTerm) ||
          product.brand.toLowerCase().includes(searchTerm)
        );
      }

      if (currentFilter !== "all") 
      {
        filteredProducts = filteredProducts.filter(
          (product) => product.brand === currentFilter
        );
      }

      displayedProducts = filteredProducts;
      applySort();
      renderProducts();
    });
  }

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
        applySort();
        closeDropdowns();
      });
    });
  }

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
        applyFilter();
        closeDropdowns();
      });
    });
  }

  function closeDropdowns() {
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
      dropdown.remove();
    });
  }

  function applyFilter() {
    if (currentFilter === "all") {
      displayedProducts = [...allProducts];
    } else {
      displayedProducts = allProducts.filter(
        (product) => product.brand === currentFilter
      );
    }
    applySort();
    renderProducts();
  }

  function applySort() {
    switch (currentSort) {
      case "name-asc":
        displayedProducts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "name-desc":
        displayedProducts.sort((a, b) => b.name.localeCompare(a.name));
        break;
    }

    renderProducts();
  }

  // Toggle product in wishlist
  async function toggleWishlist(productId) {
    if (!apiKey) {
      alert('Please log in to manage your wishlist');
      return;
    }

    const product = allProducts.find(p => p.id === productId);
    if (!product) return;

    try {
      const endpoint = product.inWishlist ? 'DeleteFromWishlist' : 'AddToWishlist';
      
      const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          type: endpoint,
          api_key: apiKey,
          product_id: productId
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.status === 'success') {
        // Update local state
        product.inWishlist = !product.inWishlist;
        renderProducts();
      } else {
        throw new Error(data.message || 'Failed to update wishlist');
      }
    } catch (error) {
      console.error('Error toggling wishlist:', error);
      alert('Failed to update wishlist. Please try again.');
    }
  }

  // Render products
  function renderProducts() {
    productsItems.innerHTML = "";

    if (displayedProducts.length === 0) {
      productsItems.innerHTML = `
        <div class="empty-products">
          <i class="fas fa-box-open"></i>
          <p>No products match your filters</p>
          <button class="browse-btn" id="resetFilters">Reset Filters</button>
        </div>
      `;

      document
        .getElementById("resetFilters")
        .addEventListener("click", function () {
          currentFilter = "all";
          currentSort = "default";
          searchInput.value = "";
          displayedProducts = [...allProducts];
          renderProducts();
        });

      itemCountElement.textContent = "0 items";
      return;
    }

    itemCountElement.textContent = `${displayedProducts.length} ${
      displayedProducts.length === 1 ? "item" : "items"
    }`;

    displayedProducts.forEach((product) => {
      const item = document.createElement("div");
      item.className = "product-card";
      
      let priceDisplay = '';
      if (product.price !== null) {
        priceDisplay = `
          <div class="product-price">
            <span class="price-amount">${product.price.toFixed(2)}</span>
            <span class="price-currency">${product.currency || 'ZAR'}</span>
          </div>
        `;
      } else {
        priceDisplay = `
          <div class="product-price out-of-stock">
            Out of stock
          </div>
        `;
      }
      
      // Heart icon based on wishlist status
      const heartIcon = product.inWishlist 
        ? '<i class="fas fa-heart" style="color: #ff6b6b;"></i>'
        : '<i class="fas fa-heart"></i>';
      
      item.innerHTML = `
        <div class="product-content">
          <div class="product-image-container">
            <div class="product-image-wrapper">
              <div class="product-image-front">
                <img src="${product.image || 'https://via.placeholder.com/300?text=No+Image'}" alt="${
        product.name
      }" class="product-image">
              </div>
              <div class="product-image-back">
                <p class="description">${product.description}</p>
              </div>
            </div>
          </div>
          <div class="product-details">
            <h3 class="product-title">${product.name}</h3>
            ${product.brand ? `<p class="product-brand">${product.brand}</p>` : ''}
            ${priceDisplay}
          </div>
        </div>
        <div class="product-actions">
          <button class="add-to-view" data-id="${product.id}">View Details</button>
          <button class="add-to-wishlist" data-id="${product.id}">
            ${heartIcon}
          </button>
        </div>
      `;
      productsItems.appendChild(item);
    });

    // Add event listeners to wishlist buttons
    document.querySelectorAll(".add-to-wishlist").forEach((button) => {
      button.addEventListener("click", async function (e) {
        e.stopPropagation();
        const productId = parseInt(this.getAttribute("data-id"));
        await toggleWishlist(productId);
      });
    });

    // Add event listeners to view buttons
    document.querySelectorAll(".add-to-view").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.stopPropagation();
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
  if (time) 
  {
    const date = new Date();
    date.setTime(date.getTime() + time * 60 * 60 * 1000);
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for(let i=0; i < ca.length; i++) 
  {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1);
    if (c.indexOf(nameEQ) === 0) 
    {
      return decodeURIComponent(c.substring(nameEQ.length));
    }
  }
  return null;
}
