<?php
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$profile_photo = $_SESSION['profile_photo_path'] ?? null;
if (empty($profile_photo) || !file_exists($profile_photo)) {
    $profile_photo = 'https://ui-avatars.com/api/?name=' . urlencode($admin_name) . '&background=0D8ABC&color=fff&size=128';
}
?>
<style>
/* Custom Scrollbar Styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 3px;
    transition: background 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}

.custom-scrollbar::-webkit-scrollbar-corner {
    background: transparent;
}

/* Firefox scrollbar styling */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

/* Hide horizontal scrollbar completely */
.custom-scrollbar {
    overflow-x: hidden;
}

/* Ensure smooth scrolling */
.custom-scrollbar {
    scroll-behavior: smooth;
}
</style>
<!-- Professional Admin Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-all duration-300 ease-in-out transform -translate-x-full sm:translate-x-0 bg-white border-r border-gray-200 shadow-lg">
    <div class="h-full flex flex-col">
        <!-- Header Section -->
        <div class="flex-shrink-0 px-6 py-6 border-b border-gray-100">
            <div class="flex items-center justify-center">
                <img src="includes/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
                <div class="ml-3">
                    <h2 class="text-lg font-bold text-gray-900">Admin Panel</h2>
                    <p class="text-xs text-gray-500">Management System</p>
                </div>
            </div>
        </div>

        <!-- Navigation Section -->
        <div class="flex-1 px-4 py-6 overflow-y-auto overflow-x-hidden custom-scrollbar">
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'index.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Dashboard</span>
                </a>

                <!-- Graduate Lists -->
                <a href="graduate-lists.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'graduate-lists.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M22 9.24V7a2 2 0 0 0-1.22-1.82l-7-3.11a2 2 0 0 0-1.56 0l-7 3.11A2 2 0 0 0 2 7v2.24a2 2 0 0 0 1.11 1.79l7 3.11a2 2 0 0 0 1.56 0l7-3.11A2 2 0 0 0 22 9.24z"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Graduate Lists</span>
                </a>

                <!-- Departments -->
                <a href="departments.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'departments.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Departments</span>
                </a>

                <!-- Job Posts Section -->
                <div class="space-y-1">
                    <button type="button" 
                            onclick="toggleJobPostsSubmenu()"
                            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo in_array($current_page, ['create-posts.php', 'manage-posts.php']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Job Posts</span>
                        </div>
                        <i id="jobPostsArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="jobPostsSubmenu" class="ml-4 space-y-1 <?php echo in_array($current_page, ['create-posts.php', 'manage-posts.php']) ? '' : 'hidden'; ?>">
                        <a href="create-posts.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'create-posts.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Create Posts</span>
                        </a>
                        <a href="manage-posts.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-posts.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Manage Posts</span>
                        </a>
                    </div>
                </div>

                <!-- Alumni Events Section -->
                <div class="space-y-1">
                    <button type="button" 
                            onclick="toggleSubmenu('eventsSubmenu')"
                            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo in_array($current_page, ['create-event.php', 'manage-events.php']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Alumni Events</span>
                        </div>
                        <i id="eventsArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="eventsSubmenu" class="ml-4 space-y-1 <?php echo in_array($current_page, ['create-event.php', 'manage-events.php']) ? '' : 'hidden'; ?>">
                        <a href="create-event.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'create-event.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Create Event</span>
                        </a>
                        <a href="manage-events.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-events.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Manage Events</span>
                        </a>
                    </div>
                </div>

                <!-- Alumni ID -->
                <a href="alumni-id.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'alumni-id.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6H8"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Alumni ID</span>
                </a>

                <!-- Payments -->
                <a href="payment.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'payment.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Payments</span>
                </a>

                <!-- Reports -->
                <a href="reports.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo $current_page === 'reports.php' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6-4l2 2 4-4"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Reports</span>
                </a>

                <!-- Manage Users Section -->
                <div class="space-y-1">
                    <button type="button" 
                            onclick="toggleSubmenu('usersSubmenu')"
                            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo in_array($current_page, ['manage-users-alumni.php', 'manage-users-chair.php', 'manage-users-admin.php']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Manage Users</span>
                        </div>
                        <i id="usersArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="usersSubmenu" class="ml-4 space-y-1 <?php echo in_array($current_page, ['manage-users-alumni.php', 'manage-users-chair.php', 'manage-users-admin.php']) ? '' : 'hidden'; ?>">
                        <a href="manage-users-alumni.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-users-alumni.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Alumni</span>
                        </a>
                        <a href="manage-users-chair.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-users-chair.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Program Chair</span>
                        </a>
                        <a href="manage-users-admin.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-users-admin.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Admin</span>
                        </a>
                    </div>
                </div>

                <!-- Surveys Section -->
                <div class="space-y-1">
                    <button type="button"
                            onclick="toggleSubmenu('surveysSubmenu')"
                            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo in_array($current_page, ['create-survey.php', 'manage-surveys.php']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0-3 3V6a3 3 0 1 0 3-3h12a3 3 0 1 0-3 3"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Surveys</span>
                        </div>
                        <i id="surveysArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="surveysSubmenu" class="ml-4 space-y-1 <?php echo in_array($current_page, ['create-survey.php', 'manage-surveys.php']) ? '' : 'hidden'; ?>">
                        <a href="create-survey.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'create-survey.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Create Survey</span>
                        </a>
                        <a href="manage-surveys.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'manage-surveys.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Manage Surveys</span>
                        </a>
                    </div>
                </div>

                <!-- Settings Section -->
                <div class="space-y-1">
                    <button type="button" 
                            onclick="toggleSubmenu('settingsSubmenu')"
                            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group <?php echo in_array($current_page, ['profile.php', 'admin-access-code.php']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : ''; ?>">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.128 2.004.128 3 0"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Settings</span>
                        </div>
                        <i id="settingsArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="settingsSubmenu" class="ml-4 space-y-1 <?php echo in_array($current_page, ['profile.php', 'admin-access-code.php']) ? '' : 'hidden'; ?>">
                        <a href="profile.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'profile.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Profile</span>
                        </a>
                        <a href="admin-access-code.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 <?php echo $current_page === 'admin-access-code.php' ? 'bg-blue-50 text-blue-700' : ''; ?>">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Access Code</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Profile Section -->
        <div class="flex-shrink-0 p-4 border-t border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50">
            <div class="flex items-center space-x-3 p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                         alt="Admin Profile" 
                         class="h-12 w-12 rounded-full object-cover border-2 border-blue-200 shadow-sm">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($admin_name); ?></p>
                    <p class="text-xs text-blue-600 font-medium">Administrator</p>
                </div>
                <div class="flex space-x-1">
                    <a href="profile.php" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200" title="Profile">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a0 0 0 0-7-7"/>
                        </svg>
                    </a>
                    <a href="logout.php" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 1 1-6 0v-1h6z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden transition-opacity duration-300"></div>

<!-- Toggle Button -->
<button id="toggleSidebar" class="fixed top-4 left-4 z-40 lg:hidden bg-white text-blue-600 p-2 rounded-lg shadow-lg border border-gray-200 transition-all duration-200 hover:bg-blue-50">
    <i class="fas fa-bars"></i>
</button>

<!-- Close Button -->
<button id="closeSidebar" class="fixed top-4 left-4 z-40 lg:hidden bg-white text-blue-600 p-2 rounded-lg shadow-lg border border-gray-200 hidden transition-all duration-200 hover:bg-blue-50">
    <i class="fas fa-times"></i>
</button>

<script>
// Job Posts Submenu Toggle
function toggleJobPostsSubmenu() {
    const submenu = document.getElementById('jobPostsSubmenu');
    const arrow = document.getElementById('jobPostsArrow');
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        submenu.style.maxHeight = '0';
        submenu.style.overflow = 'hidden';
        submenu.style.transition = 'max-height 0.3s ease-out';
        submenu.offsetHeight;
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
        setTimeout(() => {
            submenu.classList.add('hidden');
        }, 300);
    }
}

// Toggle submenu function
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const arrow = document.getElementById(submenuId.replace('Submenu', 'Arrow'));
    
    if (submenu) {
        submenu.classList.toggle('hidden');
        if (arrow) {
            arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

// Set active submenu based on current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = '<?php echo $current_page; ?>';
    
    const submenus = {
        'jobPostsSubmenu': ['create-posts.php', 'manage-posts.php'],
        'eventsSubmenu': ['create-event.php', 'manage-events.php'],
        'surveysSubmenu': ['create-survey.php', 'manage-surveys.php'],
        'usersSubmenu': ['manage-users-alumni.php', 'manage-users-chair.php', 'manage-users-admin.php'],
        'settingsSubmenu': ['profile.php', 'admin-access-code.php']
    };

    Object.entries(submenus).forEach(([submenuId, pages]) => {
        if (pages.includes(currentPage)) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(submenuId.replace('Submenu', 'Arrow'));
            
            if (submenu) {
                submenu.classList.remove('hidden');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
});

// Sidebar Toggle with Enhanced Animation
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const mainContent = document.querySelector('.main-content');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        
        if (!overlay.classList.contains('hidden')) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.opacity = '1';
            }, 10);
        }
        
        toggleBtn.classList.toggle('hidden');
        closeBtn.classList.toggle('hidden');
        
        if (mainContent) {
            if (sidebar.classList.contains('-translate-x-full')) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            } else {
                mainContent.style.marginLeft = '16rem';
                mainContent.style.width = 'calc(100% - 16rem)';
            }
        }
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    closeBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            toggleBtn.classList.remove('hidden');
            closeBtn.classList.add('hidden');
            if (mainContent) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            }
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            toggleBtn.classList.add('hidden');
            closeBtn.classList.add('hidden');
            if (mainContent) {
                mainContent.style.marginLeft = '16rem';
                mainContent.style.width = 'calc(100% - 16rem)';
            }
        }
    });
});
</script>
