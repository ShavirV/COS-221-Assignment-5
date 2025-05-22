document.addEventListener("DOMContentLoaded", function () {
  const wishlistItems = document.getElementById("wishlistItems");
  const itemCountElement = document.querySelector(".item-count");
  const sortBtn = document.querySelector(".sort-btn");
  const filterBtn = document.querySelector(".filter-btn");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");

  //random products that yall can get rid of:
  const mockProducts = [
    {
      id: 1,
      name: 'Ultra HD Smart TV 55" with Quantum Dot',
      price: 799.99,
      image:
        "https://media.istockphoto.com/id/506550846/photo/monitor.jpg?s=1024x1024&w=is&k=20&c=NHFA6MmA4vBX-xWN6eK5J8UTgeoQVBs3pRkWeaMeYGw=",
      category: "Television",
    },
    {
      id: 2,
      name: "Wireless Noise-Canceling Headphones Pro",
      price: 349.99,
      image:
        "https://cdn.pixabay.com/photo/2016/11/19/16/01/audio-1840073_1280.jpg",
      category: "Audio",
    },
    {
      id: 3,
      name: "Gaming Laptop Pro with RTX 3080",
      price: 1499.99,
      image:
        "https://media.istockphoto.com/id/906347962/photo/gaming-laptop-with-connected-mouse-and-headphones.jpg?s=2048x2048&w=is&k=20&c=6GMW6j7M7Lt2JcVslRrFwC4nlrsHjZt1wQj7Rmr07XE=",
      category: "Computers",
    },
    {
      id: 4,
      name: "Smartphone X12 Pro Max 256GB",
      price: 899.99,
      image:
        "https://media.istockphoto.com/id/2117741634/photo/abstract-modern-mobile-phone-smartphone-front-and-back-view-3d-rendering.jpg?s=2048x2048&w=is&k=20&c=ScPh7T-SZMP5zqKhWdrnslaVMRInInNgnlmXTS0qbE8=",
      category: "Phones",
    },
    {
      id: 5,
      name: "4K Action Camera with Stabilization",
      price: 299.99,
      image:
        "https://cdn.pixabay.com/photo/2014/08/29/14/53/camera-431119_1280.jpg",
      category: "Cameras",
    },
    {
      id: 6,
      name: "Smart Watch Series 5 with ECG",
      price: 249.99,
      image:
        "https://cdn.pixabay.com/photo/2023/10/07/14/24/smartwatch-8300238_1280.jpg",
      category: "Wearables",
    },
  ];

  // Current filtered/sorted products
  let displayedProducts = [...mockProducts];

  // Sort options
  const sortOptions = {
    default: "Default",
    "name-asc": "Name (A-Z)",
    "name-desc": "Name (Z-A)",
    "price-asc": "Price (Low to High)",
    "price-desc": "Price (High to Low)",
  };

  // Filter options
  const filterOptions = {
    all: "All Categories",
    Television: "Television",
    Audio: "Audio",
    Computers: "Computers",
    Phones: "Phones",
    Cameras: "Cameras",
    Wearables: "Wearables",
  };

  // Current selections
  let currentSort = "default";
  let currentFilter = "all";

  // Initialize the page
  function init() {
    renderWishlist();
    setupEventListeners();
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
  }

  //search function
  // Search functionality
  searchInput.addEventListener("input", function () {
    const searchTerm = searchInput.value.trim().toLowerCase();
    let filteredProducts = [...mockProducts];

    if (searchTerm) {
      filteredProducts = filteredProducts.filter((product) =>
        product.name.toLowerCase().includes(searchTerm)
      );
    }

    // Apply current filter and sort to the filtered results
    if (currentFilter !== "all") {
      filteredProducts = filteredProducts.filter(
        (product) => product.category === currentFilter
      );
    }

    switch (currentSort) {
      case "name-asc":
        filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "name-desc":
        filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
        break;
      case "price-asc":
        filteredProducts.sort((a, b) => a.price - b.price);
        break;
      case "price-desc":
        filteredProducts.sort((a, b) => b.price - a.price);
        break;
      // Default: no sorting
    }

    displayedProducts = filteredProducts;
    renderWishlist();
  });

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

    // Add click handlers for dropdown items
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
    dropdown.innerHTML = Object.entries(filterOptions)
      .map(
        ([value, text]) => `
        <div class="dropdown-item ${value === currentFilter ? "active" : ""}" 
             data-value="${value}">
          ${text}
        </div>
      `
      )
      .join("");

    filterBtn.appendChild(dropdown);

    // Add click handlers for dropdown items
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
    let products = [...mockProducts];
    if (currentFilter !== "all") {
      products = products.filter(
        (product) => product.category === currentFilter
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
        products.sort((a, b) => a.price - b.price);
        break;
      case "price-desc":
        products.sort((a, b) => b.price - a.price);
        break;
      // Default case keeps original order
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
          <p>No items match your filters</p>
          <button class="browse-btn" id="resetFilters">Reset Filters</button>
        </div>
      `;

      document
        .getElementById("resetFilters")
        .addEventListener("click", function () {
          currentFilter = "all";
          currentSort = "default";
          applySortAndFilter();
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
      item.className = "wishlist-card";
      item.innerHTML = `
        <span class="card-badge">${product.category}</span>
        <div class="card-image-container">
          <img src="${product.image}" alt="${product.name}" class="card-image">
        </div>
        <div class="card-details">
          <h3 class="card-title">${product.name}</h3>
          <div class="card-price">$${product.price.toFixed(2)}</div>
          <div class="card-actions">
            <button class="add-to-view">Compare</button>
            <button class="remove-btn" data-id="${product.id}">
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

    // Add event listeners to add to view buttons
    document.querySelectorAll(".add-to-view").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        // Add to view functionality would go here
      });
    });
  }

  // Remove item from wishlist
  function removeFromWishlist(productId) {
    const index = mockProducts.findIndex((product) => product.id === productId);
    if (index !== -1) {
      mockProducts.splice(index, 1);
      applySortAndFilter();
    }
  }

  // Initialize the page
  init();
});
