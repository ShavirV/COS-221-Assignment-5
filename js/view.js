// Mock data for the product
const productData = { //MOCK DATA
    id: 1,
    title: "Premium Wireless Headphones",
    description: "Experience crystal-clear sound with our premium wireless headphones. Featuring noise cancellation, 30-hour battery life, and comfortable over-ear design. Perfect for music lovers and professionals alike.",
    price: 199.99,
    images: [ //MOCK DATA
        "https://cdn.pixabay.com/photo/2016/11/19/16/01/audio-1840073_1280.jpg",

    ],
    rating: 4.5,
    reviewCount: 42
};

// Mock data for offers
const offersData = [
    { retailer: "ElectroWorld", price: 189.99, link: "#" },
    { retailer: "TechHaven", price: 195.50, link: "#" },
    { retailer: "GadgetGalaxy", price: 199.99, link: "#" },
    { retailer: "DigitalDream", price: 179.99, link: "#"}
];

// Mock data for reviews
const reviewsData = [
    { author: "Alex Johnson", rating: 5, text: "Absolutely love these headphones! The sound quality is incredible and they're so comfortable I can wear them all day." },
    { author: "Sam Wilson", rating: 4, text: "Great headphones overall. Battery life is as advertised. Only minor complaint is the ear cushions could be a bit softer." },
    { author: "Taylor Smith", rating: 5, text: "Worth every penny! The noise cancellation works perfectly on my daily commute." }
];

// Check if user is logged in (mock function - replace with actual auth check)
const prodCookie = getCookie("apiKey");
isLoggedIn = prodCookie !== null; //later come back and make an actual check if the key is valid (it may exist but be an invalid key)

//product id
const productId = getCookie("productId");

// DOM elements
const mainImage = document.getElementById('main-image');
const thumbnailContainer = document.getElementById('thumbnail-container');
const productTitle = document.getElementById('product-title');
const priceElement = document.getElementById('price');
const descriptionElement = document.getElementById('description');
const offersContainer = document.getElementById('offers-container');
const reviewsContainer = document.getElementById('reviews-container');
const leaveReviewBtn = document.getElementById('leave-review-btn');
const reviewFormContainer = document.getElementById('review-form-container');
const reviewForm = document.getElementById('review-form');

// Load product data
function loadProductData() {
    
    request = {
        "type": "GetAllProducts",
        "return": "*",
        "search": {"product_id":productId}
    };


    apiRequest(request).then(productData => {
        console.log(productData);
        if (productData.status === 'success')
        {   
            offerRequest = {
                "type": "GetBestOffer",
                "product_id": productId
            };

            apiRequest(offerRequest).then(offersData =>{

                console.log(offersData);
                productTitle.textContent = productData.data[0].name;
                priceElement.textContent = `R${offersData.data.price.toFixed(2)}`;
                descriptionElement.textContent = productData.data[0].description;

                
                mainImage.src = productData.data[0].image_url;
                mainImage.alt = productData.data[0].name;

                // Create thumbnails (USED FOR IMAGE CAROUSEL DID NOT IMPLEMENT)
                // productData.images.forEach((image, index) => {
                //         const thumbnail = document.createElement('img');
                //         thumbnail.src = image;
                //         thumbnail.alt = `${productData.title} - View ${index + 1}`;
                //         thumbnail.className = 'thumbnail';
                //         thumbnail.addEventListener('click', () => {
                //         mainImage.src = image;
                //     });
                //     thumbnailContainer.appendChild(thumbnail);

                // });


                reviewRequest =
                {
                    "type": "GetReviews",
                    "product_id": productId
                };

                apiRequest(reviewRequest).then(reviewData => {

                    console.log(reviewData);

                // Load rating
                    const starsElement = document.querySelector('.stars');
                    const reviewCountElement = document.querySelector('.review-count');
                    starsElement.textContent = '★★★★★'.slice(0, Math.floor(productData.rating)) + '☆☆☆☆☆'.slice(Math.floor(productData.rating));
                    reviewCountElement.textContent = `(${productData.reviewCount} reviews)`;
                });
                

            });
        }
    });

    
    
   
    
    
    
    
    // Add wishlist button 
    const wishlistButton = document.createElement('button');
    wishlistButton.className = 'add-to-wishlist';
    wishlistButton.setAttribute('data-id', productId);
    wishlistButton.innerHTML = '<i class="fas fa-heart"></i>';
    
    // Find a suitable place to append the wishlist button in your product page
    // For example, if you have a product-actions container:
    const productActions = document.querySelector('.product-actions');
    if (productActions) {
        productActions.appendChild(wishlistButton);
    }
}

document.querySelectorAll(".add-to-wishlist").forEach(button => {
    button.addEventListener("click", function() {
        if (isLoggedIn) {
            this.innerHTML = '<i class="fas fa-heart" style="color: #ff6b6b;"></i>';
            alert("Added to your wishlist!");
        } else {
            alert("Please log in to add items to your wishlist.");
            // Redirect to login page
            window.location.href = "../php/login.php";
        };
    });
});

// Load offers
function loadOffers() {

    offerRequest = {
                "type": "GetOffer",
                "product_id": productId
            };

            apiRequest(offerRequest).then(offersData =>{

                console.log(offersData);
                
                if (offersData.status === 'success')
                {
                    offersData.data.forEach(offer => {

                        console.log(offer);

                        const offerElement = document.createElement('div');
                        offerElement.className = 'offer';
        
                        let offerHTML = `
                        <div class="offer-retailer">${offer.name}</div>
                        <div class="offer-price">$${offer.price.toFixed(2)}</div>
                        <div class="offer-actions">
                        <a href="${offer.link}">View Deal</a>
                        `;

                        offerHTML += `</div>`;
                        offerElement.innerHTML = offerHTML;
                        offersContainer.appendChild(offerElement);

                    });
                }
            });
}

// Load reviews
function loadReviews() {
    if (reviewsData.length === 0) {
        reviewsContainer.innerHTML = '<p>No reviews yet. Be the first to review!</p>';
        return;
    }
    
    reviewsData.forEach(review => {
        const reviewElement = document.createElement('div');
        reviewElement.className = 'review';
        
        reviewElement.innerHTML = `
            <div class="review-author">${review.author}</div>
            <div class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
            <div class="review-text">${review.text}</div>
        `;
        
        reviewsContainer.appendChild(reviewElement);
    });
}

// Handle review button click
leaveReviewBtn.addEventListener('click', () => {
    if (!isLoggedIn) {
        alert('Please log in to leave a review. You will be redirected to the login page.');
        // In a real application, you would redirect to the login page
        // window.location.href = '/login';
        return;
    }
    
    reviewFormContainer.style.display = 'block';
    leaveReviewBtn.style.display = 'none';
});

// Handle review form submission
reviewForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const rating = document.getElementById('review-rating').value;
    const text = document.getElementById('review-text').value;
    
    // Create new review
    const newReview = {
        author: "You", // In a real app, this would be the user's name
        rating: parseInt(rating),
        text: text
    };
    
    // Add to reviews array
    reviewsData.unshift(newReview);
    
    // Reload reviews
    reviewsContainer.innerHTML = '';
    loadReviews();
    
    // Reset form
    reviewForm.reset();
    reviewFormContainer.style.display = 'none';
    leaveReviewBtn.style.display = 'block';
    
    // Update review count
    productData.reviewCount++;
    document.querySelector('.review-count').textContent = `(${productData.reviewCount} reviews)`;
    
    // Show success message
    alert('Thank you for your review!');
});

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    //const productId = getCookie("productId");
    loadProductData();
    loadOffers();
    loadReviews();
});

function getCookie(name) {
    
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
    const [cookieName, cookieValue] = cookie.trim().split('=');
    if (cookieName === name) {
      return decodeURIComponent(cookieValue);
    }
  }
  return null; // Cookie not found
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