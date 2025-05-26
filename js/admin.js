document.addEventListener("DOMContentLoaded", function () {
  const productsItems = document.getElementById("productsItems");
  const itemCountElement = document.querySelector(".item-count");
  const sortBtn = document.querySelector(".sort-btn");
  const filterBtn = document.querySelector(".filter-btn");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");
  const addProductBtn = document.getElementById("addProductBtn");
  const addRetailerBtn = document.getElementById("addRetailerBtn");
  const logoutBtn = document.getElementById("logoutBtn");
  const productModal = document.getElementById("productModal");
  const retailerModal = document.getElementById("retailerModal");
  const closeModals = document.querySelectorAll(".close-modal");
  const cancelBtns = document.querySelectorAll(".cancel-btn");
  const productForm = document.getElementById("productForm");
  const retailerForm = document.getElementById("retailerForm");
  const modalTitle = document.getElementById("modalTitle");
  //shav nonsense
  const apiKey = getCookie('api_key');
  let insert = false;


  //make the api request and return the response
function fetchData(request) {
  return new Promise((resolve, reject) => {
    const httpReq = new XMLHttpRequest();
    httpReq.open("POST", "../api.php", true);
    httpReq.setRequestHeader("Content-Type", "application/json");
    
    httpReq.onload = function () {
      if (httpReq.status >= 200 && httpReq.status < 300) {
        try {
          const result = JSON.parse(httpReq.responseText);
          resolve(result);
        } catch (error) {
          reject("Failed to parse response: " + error.message);
        }
      } else {
        reject("API request failed: " + httpReq.statusText);
      }
    };

    httpReq.onerror = function () {
      reject("Network error");
    };

    httpReq.send(JSON.stringify(request));
  });
}


  //Mock database
  let mockProducts = [
    {
      id: 1,
      name: 'Ultra HD Smart TV 55" with Quantum Dot',
      price: 799.99,
      offerPrice: 699.99,
      image:
        "https://media.istockphoto.com/id/506550846/photo/monitor.jpg?s=1024x1024&w=is&k=20&c=NHFA6MmA4vBX-xWN6eK5J8UTgeoQVBs3pRkWeaMeYGw=",
      category: "Television",
      description:
        "Experience stunning visuals with our 55-inch Ultra HD Smart TV featuring Quantum Dot technology. With 4K resolution, HDR support, and smart features, this TV brings your favorite content to life with vibrant colors and incredible detail. Includes voice control and access to all major streaming platforms.",
    },
    {
      id: 2,
      name: "Wireless Noise-Canceling Headphones Pro",
      price: 349.99,
      offerPrice: 299.99,
      image:
        "https://cdn.pixabay.com/photo/2016/11/19/16/01/audio-1840073_1280.jpg",
      category: "Audio",
      description:
        "Immerse yourself in pure sound with our premium wireless noise-canceling headphones. Featuring advanced active noise cancellation, 30-hour battery life, and crystal-clear call quality. The comfortable over-ear design and touch controls make these perfect for travel, work, or relaxation.",
    },
    {
      id: 3,
      name: "Gaming Laptop Pro with RTX 3080",
      price: 1499.99,
      image:
        "https://media.istockphoto.com/id/906347962/photo/gaming-laptop-with-connected-mouse-and-headphones.jpg?s=2048x2048&w=is&k=20&c=6GMW6j7M7Lt2JcVslRrFwC4nlrsHjZt1wQj7Rmr07XE=",
      category: "Computers",
      description:
        "Dominate the competition with our Gaming Laptop Pro featuring an NVIDIA RTX 3080 GPU, 16GB RAM, and a blazing-fast 1TB SSD. The 15.6-inch 240Hz display delivers buttery-smooth gameplay, while the advanced cooling system keeps performance at peak levels during marathon gaming sessions.",
    },
    {
      id: 4,
      name: "Smartphone X12 Pro Max 256GB",
      price: 899.99,
      image:
        "https://media.istockphoto.com/id/2117741634/photo/abstract-modern-mobile-phone-smartphone-front-and-back-view-3d-rendering.jpg?s=2048x2048&w=is&k=20&c=ScPh7T-SZMP5zqKhWdrnslaVMRInInNgnlmXTS0qbE8=",
      category: "Phones",
      description:
        "The Smartphone X12 Pro Max features a stunning 6.7-inch OLED display, professional-grade camera system with 4K video, and all-day battery life. With 256GB storage and 5G connectivity, this phone delivers premium performance for work and play. Includes water resistance and wireless charging.",
    },
    {
      id: 5,
      name: "4K Action Camera with Stabilization",
      price: 299.99,
      offerPrice: 249.99,
      image:
        "https://cdn.pixabay.com/photo/2014/08/29/14/53/camera-431119_1280.jpg",
      category: "Cameras",
      description:
        "Capture your adventures in stunning 4K resolution with our rugged action camera. Features advanced image stabilization, waterproof casing (up to 30m), and multiple mounting options. The wide-angle lens and slow-motion capabilities make it perfect for sports and outdoor activities.",
    },
    {
      id: 6,
      name: "Smart Watch Series 5 with ECG",
      price: 249.99,
      image:
        "https://cdn.pixabay.com/photo/2023/10/07/14/24/smartwatch-8300238_1280.jpg",
      category: "Wearables",
      description:
        "Stay connected and monitor your health with our advanced smart watch. Features ECG, blood oxygen monitoring, sleep tracking, and fitness metrics. With a bright always-on display, customizable watch faces, and 5-day battery life, it's the perfect companion for your active lifestyle.",
    },
  ];

  let mockRetailers = [];

  // Current filtered/sorted products
  let displayedProducts = [...mockProducts];

  const sortOptions = {
    default: "Default",
    "name-asc": "Name (A-Z)",
    "name-desc": "Name (Z-A)",
    "price-asc": "Price (Low to High)",
    "price-desc": "Price (High to Low)",
  };

  const filterOptions = {
    all: "All Categories",
    Television: "Television",
    Audio: "Audio",
    Computers: "Computers",
    Phones: "Phones",
    Cameras: "Cameras",
    Wearables: "Wearables",
  };

  let currentSort = "default";
  let currentFilter = "all";

  async function init() {
    renderProducts();
    setupEventListeners();
  }

  async function setupEventListeners() {
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
      let filteredProducts = [...mockProducts];

      if (searchTerm) {
        filteredProducts = filteredProducts.filter((product) =>
          product.name.toLowerCase().includes(searchTerm)
        );
      }

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
      }

      displayedProducts = filteredProducts;
      renderProducts();
    });

  addProductBtn.addEventListener("click", function () {
    insert = true; 
    openAddProductModal();
  });
  addRetailerBtn.addEventListener("click", openAddRetailerModal);

    logoutBtn.addEventListener("click", function () {
      window.location.href = "login.html";
    });

    closeModals.forEach((modal) => {
      modal.addEventListener("click", closeModalHandler);
    });

    cancelBtns.forEach((btn) => {
      btn.addEventListener("click", closeModalHandler);
    });

    productForm.addEventListener("submit", handleProductFormSubmit);
    retailerForm.addEventListener("submit", handleRetailerFormSubmit);
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
        applySortAndFilter();
        closeDropdowns();
      });
    });
  }

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

    dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
      item.addEventListener("click", function () {
        currentFilter = this.getAttribute("data-value");
        applySortAndFilter();
        closeDropdowns();
      });
    });
  }

  function closeDropdowns() {
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
      dropdown.remove();
    });
  }

  function applySortAndFilter() {
    let products = [...mockProducts];
    if (currentFilter !== "all") {
      products = products.filter(
        (product) => product.category === currentFilter
      );
    }

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
    }

    displayedProducts = products;
    renderProducts();
  }

  async function renderProducts() {
    productsItems.innerHTML = "";

    //make the request
    const request = {
      type: "GetAllProducts",
      return:"*"
    }

      const result = await fetchData(request);
      //display error if no products are found
      if (!result || result.status !== "success" || !Array.isArray(result.data)) {
        productsItems.innerHTML = `
        <div class="empty-products">
        <i class="fas fa-box-open"></i>
        <p>No products are matching your filters</p>
        <button class="browse-btn" id="resetFilters">Reset Filters</button>
        </div>
        `;
        
        document
        .getElementById("resetFilters")
        .addEventListener("click", function () {
          currentFilter = "all";
          currentSort = "default";
          searchInput.value = "";
          applySortAndFilter();
        });
        
        itemCountElement.textContent = "0 items";
        return;
      }
      itemCountElement.textContent = `${result.data.length} ${
        result.length === 1 ? "item" : "items"
      }`;
      
      //populate the page 
      for (const product of result.data) { //changed to for because async
        //get the best offer as well and store as data
        //console.log(product);
        const response = await fetch('../api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            type: 'GetBestOffer',
            product_id: product.product_id
          })
        });

        if (response.ok){
          const priceRes = await response.json();
          price = (priceRes.data.price) ? 'R ' + priceRes.data.price.toFixed(2) : "No offers yet";
        }
        

        const item = document.createElement("div");
        item.className = "product-card";
        item.innerHTML = `
        <div class="product-content">
        <div class="product-image-container">
        <img src="${product.image_url}" alt="${
          product.name
        }" class="product-image">
        </div>
        <div class="product-details">
            <h3 class="product-title">${product.name}</h3>
            <div class="product-price-container">
            <span class="current-price">${price}</span>
            </div> 
            <div class="product-category">${(product.category) ? product.category : "No category set"}</div>
            </div>
            </div>
            <div class="product-actions">
            <button class="offer-btn" data-id="${product.product_id}">
            <i class="fas fa-tag"></i> Offer
            </button>
            <button class="edit-btn" data-id="${product.product_id}">
            <i class="fas fa-edit"></i> Edit
            </button>
            <button class="delete-btn" data-id="${product.product_id}">
            <i class="fas fa-trash"></i>
            </button>
            </div>
            `;
            productsItems.appendChild(item);
      }

      document.querySelectorAll(".edit-btn").forEach((button) => {
        button.addEventListener("click", function (e) {
          e.stopPropagation();
          const productId = parseInt(this.getAttribute("data-id"));
          insert = false;
          openEditProductModal(productId);
        });
      });
  
      document.querySelectorAll(".offer-btn").forEach((button) => {
        button.addEventListener("click", function (e) {
          e.stopPropagation();
          const productId = parseInt(this.getAttribute("data-id"));
          openEditProductModal(productId, true);
        });
      });
  
      document.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", function (e) {
          e.stopPropagation();
          const productId = parseInt(this.getAttribute("data-id"));
          deleteProduct(productId);
        });
      });
  }

  function openAddProductModal() {
    document.getElementById("productId").value = "";
    document.getElementById("productName").value = "";
    //document.getElementById("productPrice").value = "";
    //document.getElementById("productOfferPrice").value = "";
    document.getElementById("productCategory").value = "";
    document.getElementById("productImage").value = "";
    document.getElementById("productDescription").value = "";
    document.getElementById("productBrand").value = "";
    modalTitle.textContent = "Add New Product";
    productModal.classList.add("active");
  }

  function openAddRetailerModal() {
    document.getElementById("retailerId").value = "";
    document.getElementById("retailerName").value = "";
    document.getElementById("retailerType").value = "";
    document.getElementById("openingTime").value = "";
    document.getElementById("closingTime").value = "";
    document.getElementById("retailerAddress").value = "";
    document.getElementById("postalCode").value = "";
    document.getElementById("retailerWebsite").value = "";
    document.getElementById("retailerCountry").value = "";
    retailerModal.classList.add("active");
    insert = true;
  }

  async function openEditProductModal(productId, isOffer = false) {
    insert = false;
    //get product based on ID
    const request = {
      type: "GetAllProducts",
      return: "*",
      search: {product_id: productId}
    };
    
    const response = await fetchData(request);
    const product = response.data[0]; //this code is ass i know

    document.getElementById("productId").value = product.product_id;
    document.getElementById("productName").value = product.name;
    document.getElementById("productBrand").value = product.brand;
    document.getElementById("productCategory").value = product.category;
    document.getElementById("productImage").value = product.image_url;
    document.getElementById("productDescription").value = product.description;
    modalTitle.textContent = isOffer ? "Add/Edit Offer" : "Edit Product";
    productModal.classList.add("active");
  }

  function closeModalHandler() {
    productModal.classList.remove("active");
    retailerModal.classList.remove("active");
  }

  function handleProductFormSubmit(e) {
    e.preventDefault();
    request = {
      type: insert? "CreateProduct":"UpdateProduct",
      api_key: apiKey,
      name: document.getElementById('productName').value,
      description: document.getElementById('productDescription').value,
      brand: document.getElementById('productBrand').value,
      category: document.getElementById('productCategory').value,
      product_id: document.getElementById('productId').value,
      image_url: document.getElementById('productImage').value
    };

    console.log(request);
    console.log(insert);

    apiRequest(request).then(response => {
      console.log(response);

      if (response.status === "success") {
        if (insert)
        {
          alert("Product added successfully!");
        }
        else
        {
          alert("Product updated successfully!");
        }
      }
    });

    renderProducts();
    closeModalHandler();
  }

  async function apiRequest (body)
{
    const response = await fetch('../api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(body)
      
    });

    return await response.json();
}

  function handleRetailerFormSubmit(e) {
    e.preventDefault();

    const name = document.getElementById("retailerName").value;
    const type = document.getElementById("retailerType").value;
    const openingTime = document.getElementById("openingTime").value;
    const closingTime = document.getElementById("closingTime").value;
    const address = document.getElementById("retailerAddress").value;
    const postalCode = document.getElementById("postalCode").value;
    const website = document.getElementById("retailerWebsite").value;
    const country = document.getElementById("retailerCountry").value;

    if (
      !name ||
      !type ||
      !openingTime ||
      !closingTime ||
      !address ||
      !postalCode ||
      !website ||
      !country
    ) {
      alert("Please fill in all fields correctly");
      return;
    }

    const newRetailer = {
      id:
        mockRetailers.length > 0
          ? Math.max(...mockRetailers.map((r) => r.id)) + 1
          : 1,
      name,
      retailer_type: type,
      opening_time: openingTime,
      closing_time: closingTime,
      address,
      postal_code: postalCode,
      website,
      country,
    };

    request = {
      type: "CreateRetailer",
      api_key: apiKey,
      name: newRetailer.name,
      retailer_type: newRetailer.retailer_type,
      opening_time: newRetailer.opening_time,
      closing_time: newRetailer.closing_time,
      address: newRetailer.address,
      postal_code: newRetailer.postal_code,
      website: newRetailer.website,
      country: newRetailer.country
     };

     console.log(request);

     apiRequest(request).then(response => {

      console.log(response);

      if (response.status === "success") {

        alert("Retailer added successfully!");
        mockRetailers.push(newRetailer);
        closeModalHandler();
      }
     });

    
    
  }

  function addProduct(product) {
    const newId =
      mockProducts.length > 0
        ? Math.max(...mockProducts.map((p) => p.id)) + 1
        : 1;
    const newProduct = {
      id: newId,
      ...product,
    };

    mockProducts.push(newProduct);
    applySortAndFilter();
  }

  function updateProduct(id, updatedProduct) {
    const index = mockProducts.findIndex((p) => p.id === id);
    if (index !== -1) {
      mockProducts[index] = {
        ...mockProducts[index],
        ...updatedProduct,
      };
      applySortAndFilter();
    }
  }

  function deleteProduct(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        let request = {
            "type": "DeleteProduct",
            "api_key": apiKey,
            "product_id": id
        };
        const result = fetchData(request);
        alert(result?.status === "success" ? "Product deleted from the database." : "Failed to delete.");
        renderProducts();
        //applySortAndFilter();
    }
  }

  //buttons that didnt already have event handlers
const editSaveBtn = document.getElementById("edit-save");
// editSaveBtn.addEventListener("click", async function(){
//   //just edit the product with the stuff from the thing
//    const id= document.getElementById("productId").value;
//    const title= document.getElementById("productName").value.trim();
//    const brand= document.getElementById("productBrand").value.trim();
//    const category = document.getElementById("productCategory").value.trim();
//    const image= document.getElementById("productImage").value.trim();
//    const description =  document.getElementById("productDescription").value.trim();

//    //create request
//    const request = {
//       type: "UpdateProduct",
//       api_key: apiKey,
//       product_id: id,
//       name: title,
//       brand: brand,
//       category: category,
//       image_url: image,
//       description: description
//    };

//    const response = await fetchData(request);

//    alert("Update made successfully!");
//    renderProducts();

// });

   init();
 });

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
    }
    return null;
}




