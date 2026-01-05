// Authentication page functions
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.name = 'eye-off-outline';
    } else {
        passwordInput.type = 'password';
        toggleIcon.name = 'eye-outline';
    }
}
