<!-- Job Posts -->
<div class="space-y-1">
    <button onclick="toggleSubmenu('jobPostsSubmenu')" class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-briefcase w-5 h-5 mr-3"></i>
            Job Posts
        </div>
        <i class="fas fa-chevron-down text-sm"></i>
    </button>
    <div id="jobPostsSubmenu" class="pl-12 space-y-1 <?php echo in_array($current_page, ['create-post', 'manage-posts']) ? '' : 'hidden'; ?>">
        <a href="create-post.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg <?php echo $current_page === 'create-post' ? 'bg-gray-100' : ''; ?>">
            Create Post
        </a>
        <a href="manage-posts.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg <?php echo $current_page === 'manage-posts' ? 'bg-gray-100' : ''; ?>">
            Manage Posts
        </a>
    </div>
</div>

<!-- Alumni Events -->
<div class="space-y-1">
    <button onclick="toggleSubmenu('eventsSubmenu')" class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i>
            Alumni Events
        </div>
        <i class="fas fa-chevron-down text-sm"></i>
    </button>
    <div id="eventsSubmenu" class="pl-12 space-y-1 <?php echo in_array($current_page, ['create-event', 'manage-events']) ? '' : 'hidden'; ?>">
        <a href="create-event.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg <?php echo $current_page === 'create-event' ? 'bg-gray-100' : ''; ?>">
            Create Event
        </a>
        <a href="manage-events.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg <?php echo $current_page === 'manage-events' ? 'bg-gray-100' : ''; ?>">
            Manage Events
        </a>
    </div>
</div>

<!-- Alumni ID -->

<script>
// Toggle submenu function
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    if (submenu) {
        submenu.classList.toggle('hidden');
    }
}

// Set active submenu based on current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = '<?php echo $current_page; ?>';
    
    // Define all submenus and their related pages
    const submenus = {
        'jobPostsSubmenu': ['create-post', 'manage-posts'],
        'eventsSubmenu': ['create-event', 'manage-events']
    };

    // Show the appropriate submenu based on current page
    Object.entries(submenus).forEach(([submenuId, pages]) => {
        if (pages.includes(currentPage)) {
            const submenu = document.getElementById(submenuId);
            if (submenu) {
                submenu.classList.remove('hidden');
            }
        }
    });

    // Add click event listeners to all submenu toggles
    document.querySelectorAll('[onclick^="toggleSubmenu"]').forEach(button => {
        button.addEventListener('click', function() {
            const submenuId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            toggleSubmenu(submenuId);
        });
    });
});
</script> 