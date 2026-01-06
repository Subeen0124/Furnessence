'use strict';

/**
 * add event on element
 */

const addEventOnElem = function (elem, type, callback) {
  if (elem.length > 1) {
    for (let i = 0; i < elem.length; i++) {
      elem[i].addEventListener(type, callback);
    }
  } else {
    elem.addEventListener(type, callback);
  }
}



/**
 * navbar toggle
 */

const navbar = document.querySelector("[data-navbar]");
const navTogglers = document.querySelectorAll("[data-nav-toggler]");
const overlay = document.querySelector("[data-overlay]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  overlay.classList.toggle("active");
}

addEventOnElem(navTogglers, "click", toggleNavbar);



/**
 * close navbar when click on any navbar link
 */

const navLinks = document.querySelectorAll("[data-nav-link]");

const closeNavbar = function () {
  navbar.classList.remove("active");
  overlay.classList.remove("active");
}

addEventOnElem(navLinks, "click", closeNavbar);



/**
 * header active when scroll down
 */

const header = document.querySelector("[data-header]");

const activeElemOnScroll = function () {
  if (window.scrollY > 100) {
    header.classList.add("active");
  } else {
    header.classList.remove("active");
  }
}

addEventOnElem(window, "scroll", activeElemOnScroll);



/**
 * filter functionality
 */

const filterBtns = document.querySelectorAll("[data-filter-btn]");
const filterItems = document.querySelectorAll("[data-filter]");
const productList = document.querySelector(".product-list");

let lastClickedFilterBtn = filterBtns[0];

// Add initial-load class on page load
if (productList) {
  productList.classList.add("initial-load");
  
  // Remove initial-load class after animations complete
  setTimeout(() => {
    productList.classList.remove("initial-load");
  }, 1500);
}

const filter = function () {
  lastClickedFilterBtn.classList.remove("active");
  this.classList.add("active");
  lastClickedFilterBtn = this;

  for (let i = 0; i < filterItems.length; i++) {
    if (this.dataset.filterBtn === filterItems[i].dataset.filter ||
        this.dataset.filterBtn === "all") {

      filterItems[i].style.display = "block";
      filterItems[i].classList.add("active");

    } else {

      filterItems[i].style.display = "none";
      filterItems[i].classList.remove("active");

    }
  }
}

addEventOnElem(filterBtns, "click", filter);



/**
 * back to top button
 */

const backTopBtn = document.querySelector("[data-back-top-btn]");

const showBackTopBtn = function () {
  if (window.scrollY > 100) {
    backTopBtn.classList.add("active");
  } else {
    backTopBtn.classList.remove("active");
  }
}

addEventOnElem(window, "scroll", showBackTopBtn);



/**
 * Add to Cart and Wishlist functionality
 */

function initializeProductCards() {
  // Get all product cards
  const productCards = document.querySelectorAll('.product-card');

  productCards.forEach(card => {
    const actionButtons = card.querySelectorAll('.card-action-btn');
    
    actionButtons.forEach((btn, index) => {
      const icon = btn.querySelector('ion-icon');
      const iconName = icon ? icon.getAttribute('name') : '';
      
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Get product data from button data attributes
        const productData = {
          id: btn.getAttribute('data-product-id'),
          name: btn.getAttribute('data-product-name'),
          price: btn.getAttribute('data-product-price'),
          image: btn.getAttribute('data-product-image')
        };
        
        // Validate product data
        if (!productData.id || !productData.name || !productData.price) {
          console.error('Invalid product data:', productData);
          showNotification('Invalid product data', 'error');
          return;
        }
        
        // Determine action based on button position or icon
        let action = '';
        if (iconName.includes('bag') || iconName.includes('cart')) {
          action = 'add_to_cart';
        } else if (iconName.includes('heart')) {
          action = 'add_to_wishlist';
        } else if (index === 1) {
          action = 'add_to_cart';
        } else if (index === 2) {
          action = 'add_to_wishlist';
        }
        
        if (!action) return;
        
        // Send AJAX request
        const formData = new FormData();
        formData.append('action', action);
        formData.append('product_id', productData.id);
        formData.append('product_name', productData.name);
        formData.append('product_price', productData.price);
        formData.append('product_image', productData.image);
        
        fetch('cart_wishlist_handler.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            
            // Update badge counts
            if (data.wishlist_count !== undefined) {
              updateBadge('wishlist', data.wishlist_count);
            }
            if (data.cart_count !== undefined) {
              updateBadge('cart', data.cart_count);
            }
          } else {
            if (data.message.includes('login')) {
              // Redirect to login
              showNotification('Please login to continue', 'error');
              setTimeout(() => {
                window.location.href = 'login.php';
              }, 1500);
            } else {
              showNotification(data.message, 'error');
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('An error occurred. Please try again.', 'error');
        });
      });
    });
  });
}

// Initialize on page load
initializeProductCards();

// Update badge count
function updateBadge(type, count) {
  const headerActions = document.querySelectorAll('.header-action-btn');
  
  headerActions.forEach(btn => {
    const icon = btn.querySelector('ion-icon');
    const iconName = icon ? icon.getAttribute('name') : '';
    
    let isTarget = false;
    if (type === 'wishlist' && iconName.includes('heart')) {
      isTarget = true;
    } else if (type === 'cart' && iconName.includes('bag')) {
      isTarget = true;
    }
    
    if (isTarget) {
      let badge = btn.querySelector('.btn-badge');
      if (count > 0) {
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'btn-badge';
          btn.appendChild(badge);
        }
        badge.textContent = count;
      } else if (badge) {
        badge.remove();
      }
    }
  });
}

// Show notification
function showNotification(message, type = 'success') {
  // Remove existing notifications
  const existing = document.querySelector('.notification-toast');
  if (existing) {
    existing.remove();
  }
  
  const notification = document.createElement('div');
  notification.className = `notification-toast ${type}`;
  notification.innerHTML = `
    <ion-icon name="${type === 'success' ? 'checkmark-circle' : 'alert-circle'}"></ion-icon>
    <span>${message}</span>
  `;
  
  document.body.appendChild(notification);
  
  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 100);
  
  // Remove after 3 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}



/**
 * USER DROPDOWN MENU
 */

const userDropdownBtn = document.querySelector('[data-user-dropdown-btn]');
const userDropdownMenu = document.querySelector('[data-user-dropdown]');

console.log('User Dropdown Button:', userDropdownBtn);
console.log('User Dropdown Menu:', userDropdownMenu);

if (userDropdownBtn && userDropdownMenu) {
  console.log('User dropdown initialized');
  
  // Toggle dropdown on button click
  userDropdownBtn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Button clicked!');
    const isActive = userDropdownMenu.classList.toggle('active');
    console.log('Dropdown active:', isActive);
    userDropdownBtn.setAttribute('aria-expanded', isActive.toString());
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.user-dropdown-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      userDropdownMenu.classList.remove('active');
      userDropdownBtn.setAttribute('aria-expanded', 'false');
    }
  });

  // Close dropdown on ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && userDropdownMenu.classList.contains('active')) {
      userDropdownMenu.classList.remove('active');
      userDropdownBtn.setAttribute('aria-expanded', 'false');
    }
  });

  // Prevent dropdown from closing when clicking inside it
  userDropdownMenu.addEventListener('click', function(e) {
    e.stopPropagation();
  });
} else {
  console.log('User dropdown elements not found!');
}


/**
 * Product Details Page - Add to Cart and Wishlist
 */

function initializeProductDetailsButtons() {
  // Handle add to cart buttons (both classes)
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn, .btn-add-cart');
  const addToWishlistButtons = document.querySelectorAll('.add-to-wishlist-btn, .btn-wishlist');
  
  addToCartButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (btn.disabled) {
        showNotification('This product is out of stock', 'error');
        return;
      }
      
      const productData = {
        id: btn.getAttribute('data-product-id'),
        name: btn.getAttribute('data-product-name'),
        price: btn.getAttribute('data-product-price'),
        image: btn.getAttribute('data-product-image')
      };
      
      if (!productData.id || !productData.name || !productData.price) {
        console.error('Invalid product data:', productData);
        showNotification('Invalid product data', 'error');
        return;
      }
      
      const formData = new FormData();
      formData.append('action', 'add_to_cart');
      formData.append('product_id', productData.id);
      formData.append('product_name', productData.name);
      formData.append('product_price', productData.price);
      formData.append('product_image', productData.image);
      
      fetch('cart_wishlist_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          if (data.cart_count !== undefined) {
            updateBadge('cart', data.cart_count);
          }
        } else {
          if (data.message.includes('login')) {
            showNotification('Please login to continue', 'error');
            setTimeout(() => {
              window.location.href = 'login.php';
            }, 1500);
          } else {
            showNotification(data.message, 'error');
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
      });
    });
  });
  
  addToWishlistButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      const productData = {
        id: btn.getAttribute('data-product-id'),
        name: btn.getAttribute('data-product-name'),
        price: btn.getAttribute('data-product-price'),
        image: btn.getAttribute('data-product-image')
      };
      
      if (!productData.id || !productData.name || !productData.price) {
        console.error('Invalid product data:', productData);
        showNotification('Invalid product data', 'error');
        return;
      }
      
      const formData = new FormData();
      formData.append('action', 'add_to_wishlist');
      formData.append('product_id', productData.id);
      formData.append('product_name', productData.name);
      formData.append('product_price', productData.price);
      formData.append('product_image', productData.image);
      
      fetch('cart_wishlist_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          if (data.wishlist_count !== undefined) {
            updateBadge('wishlist', data.wishlist_count);
          }
        } else {
          if (data.message.includes('login')) {
            showNotification('Please login to continue', 'error');
            setTimeout(() => {
              window.location.href = 'login.php';
            }, 1500);
          } else {
            showNotification(data.message, 'error');
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
      });
    });
  });
}

// Initialize product details buttons on page load
initializeProductDetailsButtons();
