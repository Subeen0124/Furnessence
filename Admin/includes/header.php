<div class="admin-header">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?php echo ucfirst(str_replace(['_', '.php'], [' ', ''], basename($_SERVER['PHP_SELF']))); ?></h1>
    </div>
    
    <div class="header-right">
        <div class="admin-profile">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($admin['full_name'] ?? 'Admin'); ?></span>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.admin-sidebar').classList.toggle('collapsed');
    document.querySelector('.admin-main').classList.toggle('expanded');
}
</script>
