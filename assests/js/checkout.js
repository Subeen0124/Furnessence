// Checkout page functions
function selectPayment(element, paymentId) {
    // Remove selected class from all options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
        option.classList.remove('active');
    });
    
    // Add selected class to clicked option
    element.classList.add('selected');
    element.classList.add('active');
    
    // Check the radio button
    document.getElementById(paymentId).checked = true;
    
    // Show/hide Khalti info
    const khaltiInfo = document.getElementById('khalti-info');
    if (khaltiInfo) {
        khaltiInfo.style.display = (paymentId === 'khalti') ? 'flex' : 'none';
    }
    
    // Update button text
    const placeBtn = document.querySelector('.place-order-btn');
    if (placeBtn) {
        if (paymentId === 'khalti') {
            placeBtn.innerHTML = '<ion-icon name="wallet-outline"></ion-icon> Pay with Khalti';
        } else if (paymentId === 'esewa') {
            placeBtn.innerHTML = '<ion-icon name="phone-portrait-outline"></ion-icon> Pay with eSewa';
        } else {
            placeBtn.innerHTML = '<ion-icon name="checkmark-circle"></ion-icon> Place Order';
        }
    }
}

// Set initial selected payment option
document.addEventListener('DOMContentLoaded', function() {
    const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (checkedRadio) {
        const parentOption = checkedRadio.closest('.payment-option');
        if (parentOption) {
            parentOption.classList.add('selected');
            parentOption.classList.add('active');
        }
    }
    
    // Show URL error messages
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        let msg = '';
        switch (error) {
            case 'payment_cancelled': msg = 'Payment was cancelled. Please try again.'; break;
            case 'verification_failed': msg = 'Payment verification failed. Please contact support.'; break;
            case 'payment_incomplete': msg = 'Payment was not completed. Please try again.'; break;
            case 'invalid_payment': msg = 'Invalid payment response. Please try again.'; break;
            default: msg = 'An error occurred. Please try again.';
        }
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.innerHTML = '<ion-icon name="alert-circle"></ion-icon><span>' + msg + '</span>';
        const container = document.querySelector('.page-header');
        if (container) container.after(alertDiv);
    }
    
    // Form validation
    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        const phone = document.getElementById('phone').value;
        const zip = document.getElementById('zip').value;
        
        // Basic phone validation
        if (phone.length < 10) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            return false;
        }
        
        // Basic zip validation
        if (zip.length < 4) {
            e.preventDefault();
            alert('Please enter a valid ZIP/Postal code');
            return false;
        }
    });
});
