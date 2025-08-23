<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- Modern Responsive Sidebar - Admin-styled -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-all duration-300 ease-in-out transform -translate-x-full sm:translate-x-0 bg-white border-r border-gray-200 shadow-lg">
    <div class="h-full flex flex-col">
        <!-- Sidebar Header -->
        <div class="flex-shrink-0 px-6 py-6 border-b border-gray-100 bg-white/95">
            <div class="flex items-center justify-center">
                <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-10 w-auto">
                <div class="ml-3">
                    <h2 class="text-lg font-bold text-gray-900">Chair Panel</h2>
                    <p class="text-xs text-gray-500">Alumni Management</p>
                </div>
            </div>
        </div>
        <!-- Navigation Menu -->
        <div class="flex-1 px-4 py-6 overflow-y-auto overflow-x-hidden custom-scrollbar">
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium">Dashboard</span>
                </a>

                <!-- Manage Alumni (Admin style) -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleSubmenu('alumniSubmenu')" class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Manage Alumni</span>
                        </div>
                        <i id="alumniArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="alumniSubmenu" class="ml-4 space-y-1 hidden">
                        <a href="alumni_list.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Alumni Lists</span>
                        </a>
                        <a href="employment_status.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Employment Status</span>
                        </a>
                    </div>
                                </div>

                <a href="chair-reports.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V5"/>
                                    </svg>
                                </div>
                    <span class="ml-3 font-medium">Reports</span>
                </a>

                <!-- Job Posts (Admin style) -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleJobPostsSubmenu()" class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <span class="ml-3 font-medium">Job Posts</span>
                        </div>
                        <i id="jobPostingArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="jobPostingSubmenu" class="ml-4 space-y-1 hidden">
                        <a href="create-posts.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Create Post</span>
                        </a>
                        <a href="manage-posts.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Manage Posts</span>
                        </a>
                                </div>
                                </div>

                <!-- Notifications (Admin style) -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleSubmenu('notificationsSubmenu')" class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 17h5l-5 5V17z"/>
                                    </svg>
                            </div>
                            <span class="ml-3 font-medium">Notifications</span>
                        </div>
                        <i id="notificationsArrow" class="fas fa-chevron-down text-sm transition-transform duration-300"></i>
                    </button>
                    <div id="notificationsSubmenu" class="ml-4 space-y-1 hidden">
                        <a href="sms.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">SMS</span>
                        </a>
                        <a href="email.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Email</span>
                        </a>
                                </div>
                                </div>

                <!-- Surveys (Admin style) -->
                <div class="space-y-1">
                    <button type="button" onclick="toggleSubmenu('surveysSubmenu')" class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
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
                    <div id="surveysSubmenu" class="ml-4 space-y-1 hidden">
                        <a href="create-survey.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Create Survey</span>
                        </a>
                        <a href="manage-surveys.php" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-sm">Manage Surveys</span>
                        </a>
                                </div>
                                </div>

                <a href="chair_profile.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a0 0 0 0-7-7"/>
                                </svg>
                            </div>
                    <span class="ml-3 font-medium">Profile</span>
                    </a>
            </ul>

            <!-- Logout Section (Admin style) -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <a href="../logout.php" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1"/>
                            </svg>
                    </div>
                    <span class="ml-3 font-medium">Logout</span>
                </a>
            </div>

            <!-- Sidebar Mini Profile (Admin style, sticky) -->
            <?php
            $chair_name = $_SESSION['chair_name'] ?? 'Program Chair';
            $profile_photo = $_SESSION['profile_photo_path'] ?? '';
            if (!empty($profile_photo) && strpos($profile_photo, 'ui-avatars.com') === false) {
                $profile_photo_url = '../admin/chair-uploads/' . htmlspecialchars($profile_photo);
            } else {
                $profile_photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($chair_name) . '&background=0D8ABC&color=fff';
            }
            ?>
            <div class="sticky bottom-0 left-0 z-10 px-2 pb-4 pt-2 bg-gradient-to-t from-white via-white/80 to-transparent border-t border-gray-100">
                <div class="flex items-center space-x-3 p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="relative">
                        <img src="<?php echo $profile_photo_url; ?>" alt="Profile" class="h-12 w-12 rounded-full object-cover border-2 border-blue-200 shadow-sm">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($chair_name); ?></p>
                        <p class="text-xs text-blue-600 font-medium">Program Chair</p>
                    </div>
                    <div class="flex space-x-1">
                        <a href="chair_profile.php" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200" title="Profile">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a0 0 0 0-7-7" />
                            </svg>
                        </a>
                        <a href="../logout.php" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 1 1-6 0v-1h6z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity duration-300"></div>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    const arrow = document.getElementById(id.replace('Submenu', 'Arrow'));
    
    if (submenu) {
        submenu.classList.toggle('hidden');
        if (arrow) {
            arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

// Job Posts submenu toggle (admin-style with smooth animation)
function toggleJobPostsSubmenu() {
    const submenu = document.getElementById('jobPostingSubmenu');
    const arrow = document.getElementById('jobPostingArrow');
    if (!submenu) return;

    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        submenu.style.maxHeight = '0';
        submenu.style.overflow = 'hidden';
        submenu.style.transition = 'max-height 0.3s ease-out';
        submenu.offsetHeight; // force reflow
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.maxHeight = '0';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
        setTimeout(() => submenu.classList.add('hidden'), 300);
    }
}

// Auto-open Job Posts when on its pages
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = '<?php echo $current_page; ?>';
    if (['create-posts.php', 'manage-posts.php'].includes(currentPage)) {
        const submenu = document.getElementById('jobPostingSubmenu');
        const arrow = document.getElementById('jobPostingArrow');
        if (submenu) submenu.classList.remove('hidden');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    }
});
</script>

<style>
/* Custom Scrollbar Styling (matches admin sidebar) */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(71, 85, 105, 0.3); /* slate-700 */
    border-radius: 3px;
    transition: background 0.2s ease;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(71, 85, 105, 0.5);
}
.custom-scrollbar::-webkit-scrollbar-corner {
    background: transparent;
}
/* Firefox scrollbar styling */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(71, 85, 105, 0.3) transparent;
    overflow-x: hidden;
    scroll-behavior: smooth;
}
</style>
