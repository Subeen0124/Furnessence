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

let lastClickedFilterBtn = filterBtns[0];

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

// Get all product cards
const productCards = document.querySelectorAll('.product-card');

productCards.forEach(card => {
  const cardTitle = card.querySelector('.card-title');
  const cardPrice = card.querySelector('.price');
  const cardImage = card.querySelector('.card-banner img');
  const actionButtons = card.querySelectorAll('.card-action-btn');
  
  if (!cardTitle || !cardPrice || !cardImage) return;
  
  const productData = {
    id: Math.random().toString(36).substr(2, 9), // Generate unique ID
    name: cardTitle.textContent.trim(),
    price: parseFloat(cardPrice.getAttribute('value') || cardPrice.textContent.replace('$', '')),
    image: cardImage.src
  };
  
  actionButtons.forEach((btn, index) => {
    const icon = btn.querySelector('ion-icon');
    const iconName = icon ? icon.getAttribute('name') : '';
    
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
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
