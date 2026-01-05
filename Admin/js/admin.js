// Admin sidebar toggle
function toggleSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('collapsed');
    document.querySelector('.admin-main').classList.toggle('expanded');
}

// Image preview for product forms
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Category modal functions
function showAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addModal').style.display = 'none';
}

// Handle confirmation for forms with data-confirm attribute
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
