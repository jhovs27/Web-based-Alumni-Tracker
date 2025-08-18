<!-- Navbar -->
<nav class="bg-white shadow-lg fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Title -->
            <div class="flex items-center">
                <button id="sidebarToggle" class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="flex items-center ml-4">
                    <img src="../images/slsu_logo.png" alt="SLSU Logo" class="logo-circle h-8 w-8 mr-3 rounded-full object-cover aspect-square flex-shrink-0">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-800">SLSU-HC Alumni Portal</h1>
                        <p class="text-xs text-gray-500">Welcome back, <?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?></p>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full">
                    <i class="fas fa-bell"></i>
                </button>
                
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileDropdown" class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100">
                        <?php if (!empty($alumni['profile_photo_path']) && file_exists('../admin/uploads/profile_photos/' . $alumni['profile_photo_path'])): ?>
                            <img src="../admin/uploads/profile_photos/<?php echo htmlspecialchars($alumni['profile_photo_path']); ?>" 
                                 alt="Profile" class="h-8 w-8 rounded-full object-cover">
                        <?php else: ?>
                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                        <?php endif; ?>
                        <span class="hidden md:block text-sm font-medium text-gray-700"><?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i>Settings
                        </a>
                        <hr class="my-1">
                        <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav> 