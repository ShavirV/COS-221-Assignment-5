document.addEventListener("DOMContentLoaded", function () {
  const productsItems = document.getElementById("productsItems");
  const itemCountElement = document.querySelector(".item-count");
  const sortBtn = document.querySelector(".sort-btn");
  const filterBtn = document.querySelector(".filter-btn");
  const searchInput = document.getElementById("search-input");

  // ***this is ES6 JS since no restrictions in spec***
  // Product data from API
  let allProducts = [];
  let displayedProducts = [];
  let productBrands = new Set(); 
  // store prod prices
  let productPrices = {};

  // curr filtered/sorted products
  let currentSort = "default";
  let currentFilter = "all";

  // Sort options
  const sortOptions = {
    default: "Default",
    "name-asc": "Name (A-Z)",
    "name-desc": "Name (Z-A)",
  };

  // Initialize the page
  async function init() {
    await fetchProducts();
    await fetchPrices(); // Fetch prices after products
    setupEventListeners();
  }

  // Fetch products from API
  async function fetchProducts() {
    try {
      // show loading state, optional can remove 
      productsItems.innerHTML = `
        <div class="loading-products">
          <div class="spinner"></div>
          <p>Loading products...</p>
        </div>
      `;

      // path must be chnaged if your api.php is not located in the root directory
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
        allProducts = data.data.map(product => ({
          id: product.product_id,
          name: product.name,
          description: product.description,
          brand: product.brand,
          image: product.image_url,
          price: null
        }));
        
        // note the filtering and sorting doesnt seem to be working yet
        // focusing on main endpoints first
        // gets unique brands for filtering
        allProducts.forEach(product => {
          if (product.brand) {
            productBrands.add(product.brand);
          }
        });
        
        displayedProducts = [...allProducts];
        renderProducts();
      } else {
        throw new Error(data.message || 'Unknown API error');
      }
    } catch (error) {
      console.error('Error fetching products:', error);
      showErrorState(error);
    }
  }

  async function fetchPrices() {
    try {
      // create an array of promises for all price requests
      // my promises are being fulfilled 
      const pricePromises = allProducts.map(product => {
        const requestBody = {
          type: 'GetBestOffer',
          // id gives error when not specified as string, tried clamping to integer and it freaked out
          product_id: String(product.id) 
        };

        // remember file pathing!!!
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
            // null if failed 
            return null; 
          }
          return response.json();
        })
        .catch(error => {
          console.error(`Error fetching price for product ${product.id}:`, error);
          return null;
        });
      });

      // wait for all price requests to finish
      const priceResponses = await Promise.all(pricePromises);

      // process prices responses here
      priceResponses.forEach((response, index) => {
        if (response && response.status === 'success') 
        {
          if (typeof response.data === 'object' && !Array.isArray(response.data) && response.data.price) {
            // finds a valid best offer
            allProducts[index].price = parseFloat(response.data.price);
            allProducts[index].currency = response.data.currency || 'ZAR';
            allProducts[index].hasStock = true;
          } 
          
          else if (typeof response.data === 'string') 
          {
            // dispkay No stock available message
            allProducts[index].price = null;
            allProducts[index].hasStock = false;
          } 
          
          else 
          {
            // Unexpected response format throw a warn in console (debug)
            allProducts[index].price = null;
            allProducts[index].hasStock = false;
            console.warn('Unexpected response format for product', allProducts[index].id, response);
          }
        } 
        
        else 
        {
          // eror case or no response
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
      // render regardless
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
    // Sort button click
    sortBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      showSortDropdown();
    });

    // Filter button click
    filterBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      showFilterDropdown();
    });

    // Close dropdowns when clicking elsewhere
    document.addEventListener("click", closeDropdowns);

    // Search functionality
    searchInput.addEventListener("input", function () {
      const searchTerm = searchInput.value.trim().toLowerCase();
      let filteredProducts = [...allProducts];

      if (searchTerm) {
        filteredProducts = filteredProducts.filter((product) =>
          product.name.toLowerCase().includes(searchTerm) ||
          product.description.toLowerCase().includes(searchTerm) ||
          product.brand.toLowerCase().includes(searchTerm)
        );
      }

      //  appky curr filter to the filtered results
      if (currentFilter !== "all") {
        filteredProducts = filteredProducts.filter(
          (product) => product.brand === currentFilter
        );
      }

      displayedProducts = filteredProducts;
      applySort();
      renderProducts();
    });
  }

  // sort dropdown
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

    // click handlers for dropdown items
    dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", function () {
        currentSort = this.getAttribute("data-value");
        applySort();
        closeDropdowns();
      });
    });
  }

  // display filter dropdown
  function showFilterDropdown() {
    closeDropdowns();

    const dropdown = document.createElement("div");
    dropdown.className = "filter-dropdown dropdown";
    
    // filter options from brands
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

    // click handlers for dropdown items
    dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", function () {
        currentFilter = this.getAttribute("data-value");
        applyFilter();
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

  // current filter
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
      // Default: no sorting, no sort is by default so can leave default case commented out
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

    // Update item count
    itemCountElement.textContent = `${displayedProducts.length} ${
      displayedProducts.length === 1 ? "item" : "items"
    }`;

    displayedProducts.forEach((product) => {
      const item = document.createElement("div");
      item.className = "product-card";
      
      // Price display logic, can alter for frontend llook 
      let priceDisplay = '';
      if (product.price !== null) 
      {
        priceDisplay = `
          <div class="product-price">
            <span class="price-amount">${product.price.toFixed(2)}</span>
            <span class="price-currency">${product.currency || 'ZAR'}</span>
          </div>
        `;
      } 
      
      else 
      {
        priceDisplay = `
          <div class="product-price out-of-stock">
            Out of stock
          </div>
        `;
      }
      
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
          <button class="add-to-view" onclick = "viewProduct(${product.id})">View Details</button>
          <button class="add-to-wishlist" data-id="${product.id}">
            <i class="fas fa-heart"></i>
          </button>
        </div>
      `;
      productsItems.appendChild(item);
    });

    // wishlist listener
    document.querySelectorAll(".add-to-wishlist").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.stopPropagation();
        const productId = parseInt(this.getAttribute("data-id"));
        this.innerHTML = '<i class="fas fa-heart" style="color: #ff6b6b;"></i>';
      });
    });

    // view listener
    document.querySelectorAll(".add-to-view").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.stopPropagation();
        
        


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

function viewProduct(product)
{
    console.log(product);
}
