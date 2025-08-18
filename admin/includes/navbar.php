<?php
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$profile_photo = $_SESSION['profile_photo_path'] ?? null;

if (empty($profile_photo) || !file_exists($profile_photo)) {
    $profile_photo = 'https://ui-avatars.com/api/?name=' . urlencode($admin_name) . '&background=0D8ABC&color=fff&size=128';
}
?>

<!-- Modern Navbar -->
<nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 right-0 left-0 z-30 lg:left-64">
    <div class="px-4 mx-auto">
        <div class="flex justify-between h-16 pl-16 lg:pl-0">
            <!-- Left side - Page Title -->
            <div class="flex items-center">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1 bg-blue-600 rounded-full"></div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            <?php 
                            $current_page = basename($_SERVER['PHP_SELF']);
                            if ($current_page === 'index.php') {
                                echo 'Dashboard';
                            } else {
                                echo ucwords(str_replace(['.php', '-'], ['', ' '], $current_page));
                            }
                            ?>
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">
                            <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right side - Actions -->
            <div class="flex items-center gap-4">
                <!-- Fit to Screen Button -->
                <div class="relative">
                    <button id="fitScreenBtn" 
                            class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            title="Fit to Screen">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationBtn" 
                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1h6z"/>
                        </svg>
                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform bg-red-500 rounded-full">3</span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notificationDropdown" 
                         class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50 transform transition-all duration-200 ease-in-out">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                <span class="text-xs text-gray-500">3 new</span>
                            </div>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">New Graduate Registered</p>
                                        <p class="text-xs text-gray-500 mt-1">John Doe has completed registration</p>
                                        <p class="text-xs text-blue-600 mt-1">2 minutes ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">Event Published</p>
                                        <p class="text-xs text-gray-500 mt-1">Alumni Reunion 2024 is now live</p>
                                        <p class="text-xs text-blue-600 mt-1">1 hour ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">Job Post Approved</p>
                                        <p class="text-xs text-gray-500 mt-1">Software Engineer position approved</p>
                                        <p class="text-xs text-blue-600 mt-1">3 hours ago</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-100">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileBtn" 
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 group">
                        <img class="h-8 w-8 rounded-full object-cover ring-2 ring-gray-200 group-hover:ring-blue-300 transition-all duration-200"
                             src="<?php echo htmlspecialchars($profile_photo); ?>"
                             alt="Profile">
                        <div class="hidden md:block text-left">
                            <div class="text-sm font-medium text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                <?php echo htmlspecialchars($admin_name); ?>
                            </div>
                            <div class="text-xs text-gray-500">Administrator</div>
                        </div>
                        <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Profile Dropdown Menu -->
                    <div id="profileDropdown" 
                         class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50 transform transition-all duration-200 ease-in-out">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <img class="h-10 w-10 rounded-full object-cover" 
                                     src="<?php echo htmlspecialchars($profile_photo); ?>" 
                                     alt="Profile">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($admin_name); ?>
                                    </p>
                                    <p class="text-xs text-gray-500">Administrator</p>
                                </div>
                            </div>
                        </div>
                        <div class="py-1">
                            <a href="profile.php" 
                               class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a0 0 0 0-7-7"/>
                                </svg>
                                Profile Settings
                            </a>
                            <a href="admin-access-code.php" 
                               class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.128 2.004.128 3 0"/>
                                </svg>
                                System Settings
                            </a>
                            <a href="permission-control.php" 
                               class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Permissions
                            </a>
                        </div>
                        <div class="border-t border-gray-100 py-1">
                            <a href="logout.php" 
                               class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 1 1-6 0v-1h6z"/>
                                </svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    const fitScreenBtn = document.getElementById('fitScreenBtn');

    function closeAllDropdowns() {
        notificationDropdown.classList.add('hidden');
        profileDropdown.classList.add('hidden');
    }

    // Toggle notification dropdown
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = notificationDropdown.classList.contains('hidden');
        closeAllDropdowns();
        if (isHidden) {
            notificationDropdown.classList.remove('hidden');
        }
    });

    // Toggle profile dropdown
    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = profileDropdown.classList.contains('hidden');
        closeAllDropdowns();
        if (isHidden) {
            profileDropdown.classList.remove('hidden');
        }
    });

    // Toggle fit screen functionality
    fitScreenBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Toggle fullscreen mode
        if (!document.fullscreenElement) {
            // Enter fullscreen
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
            
            // Update button appearance
            fitScreenBtn.classList.add('bg-blue-50', 'text-blue-600');
            fitScreenBtn.classList.remove('hover:bg-blue-50');
            fitScreenBtn.classList.add('hover:bg-blue-100');
            
            // Update icon to exit fullscreen
            fitScreenBtn.innerHTML = `
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            `;
            fitScreenBtn.title = "Exit Fullscreen";
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            
            // Update button appearance
            fitScreenBtn.classList.remove('bg-blue-50', 'text-blue-600');
            fitScreenBtn.classList.add('hover:bg-blue-50');
            fitScreenBtn.classList.remove('hover:bg-blue-100');
            
            // Update icon back to fit screen
            fitScreenBtn.innerHTML = `
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            `;
            fitScreenBtn.title = "Fit to Screen";
        }
    });

    // Listen for fullscreen change events
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('msfullscreenchange', handleFullscreenChange);

    function handleFullscreenChange() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            // Exited fullscreen - reset button
            fitScreenBtn.classList.remove('bg-blue-50', 'text-blue-600');
            fitScreenBtn.classList.add('hover:bg-blue-50');
            fitScreenBtn.classList.remove('hover:bg-blue-100');
            
            fitScreenBtn.innerHTML = `
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            `;
            fitScreenBtn.title = "Fit to Screen";
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target) &&
            !profileBtn.contains(e.target) && !profileDropdown.contains(e.target) &&
            !fitScreenBtn.contains(e.target)) { // Added fitScreenBtn to exclusion
            closeAllDropdowns();
        }
    });

    // Close dropdowns when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
});
</script>
