<?php
$chair_name = $_SESSION['chair_name'] ?? 'Program Chair';
$profile_photo = $_SESSION['profile_photo_path'] ?? '';
if (!empty($profile_photo) && strpos($profile_photo, 'ui-avatars.com') === false) {
    $profile_photo_url = '../admin/chair-uploads/' . htmlspecialchars($profile_photo);
} else {
    $profile_photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($chair_name) . '&background=0D8ABC&color=fff';
}
?>

<!-- Modern Responsive Navbar -->
<nav class="bg-white/95 backdrop-blur-md shadow-lg fixed w-full top-0 z-50 border-b border-gray-200/50 transition-all duration-300 lg:ml-64 lg:w-[calc(100%-16rem)]" id="navbar">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left Section: Mobile Menu + Logo -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Button -->
                <button id="toggleSidebar" class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Logo and Title (Hidden on mobile) -->
                <div class="hidden sm:flex items-center space-x-3">
                    <div class="relative">
                        <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-10 w-10 rounded-full object-cover shadow-md border-2 border-white">
                        <div class="absolute inset-0 bg-blue-500/20 rounded-full blur-sm"></div>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900 tracking-tight">SLSU-HC Chair Panel</h1>
                        <p class="text-sm text-gray-600 hidden md:block">Welcome, <span class="text-blue-600 font-medium"><?php echo htmlspecialchars($chair_name); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Right Section: Zoom + Notifications + DateTime + Profile -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Fit to Screen Button -->
                <button id="fitScreenBtn" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500" title="Fit to Screen">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6M4 4v6M4 20h6M4 20v-6M20 4h-6M20 4v6M20 20h-6M20 20v-6" />
                    </svg>
                </button>

                <!-- Notifications -->
                <button class="relative p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5V17z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center font-medium animate-pulse">3</span>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileDropdown" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="relative">
                            <img id="navbar-profile-photo" src="<?php echo $profile_photo_url; ?>" alt="Profile" class="h-8 w-8 rounded-full object-cover border-2 border-gray-200">
                            <div class="absolute bottom-0 right-0 h-3 w-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>
                        <div class="hidden sm:block text-left">
                            <span class="block text-sm font-medium text-gray-900"><?php echo htmlspecialchars($chair_name); ?></span>
                            <span class="block text-xs text-gray-500">Program Chair</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-200 backdrop-blur-sm">
                        <!-- User Info Header -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <img src="<?php echo $profile_photo_url; ?>" alt="Profile" class="h-10 w-10 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($chair_name); ?></p>
                                    <p class="text-xs text-gray-500">Program Chair</p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                <div class="p-2 bg-blue-50 rounded-lg mr-3 group-hover:bg-blue-100">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">My Profile</span>
                            </a>
                            
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 group">
                                <div class="p-2 bg-gray-50 rounded-lg mr-3 group-hover:bg-gray-100">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31 2.37 2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Settings</span>
                            </a>
                            
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                <div class="p-2 bg-purple-50 rounded-lg mr-3 group-hover:bg-purple-100">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Help & Support</span>
                            </a>
                        </div>

                        <!-- Logout Section -->
                        <div class="border-t border-gray-100 pt-2">
                            <a href="../logout.php" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200 group">
                                <div class="p-2 bg-red-50 rounded-lg mr-3 group-hover:bg-red-100">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
// Mobile sidebar toggle
const toggleSidebarBtn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');
let overlay = document.getElementById('sidebarOverlay');

// If overlay doesn't exist, create it
if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'sidebarOverlay';
    overlay.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300';
    document.body.appendChild(overlay);
}

function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
}

function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
}

if (toggleSidebarBtn && sidebar && overlay) {
    toggleSidebarBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    overlay.addEventListener('click', function() {
        closeSidebar();
    });
}

// Profile dropdown toggle
const profileDropdown = document.getElementById('profileDropdown');
const profileMenu = document.getElementById('profileMenu');

if (profileDropdown && profileMenu) {
    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!profileMenu.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileMenu.classList.add('hidden');
        }
    });
}

// Fit to Screen (Fullscreen) Feature
const fitScreenBtn = document.getElementById('fitScreenBtn');
if (fitScreenBtn) {
    fitScreenBtn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });
}

// Session refresh mechanism
function refreshSession() {
    fetch('session_refresh.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.profile_photo_path) {
                const navbarPhoto = document.getElementById('navbar-profile-photo');
                if (navbarPhoto && navbarPhoto.src !== data.profile_photo_path) {
                    navbarPhoto.src = data.profile_photo_path;
                }
            }
        } else if (data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {});
}

setInterval(refreshSession, 300000); // Refresh every 5 minutes
</script>
