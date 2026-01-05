// Checkout page functions
function selectPayment(element, paymentId) {
    // Remove selected class from all options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    element.classList.add('selected');
    
    // Check the radio button
    document.getElementById(paymentId).checked = true;
}

// Set initial selected payment option
document.addEventListener('DOMContentLoaded', function() {
    const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (checkedRadio) {
        const parentOption = checkedRadio.closest('.payment-option');
        if (parentOption) {
            parentOption.classList.add('selected');
        }
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
