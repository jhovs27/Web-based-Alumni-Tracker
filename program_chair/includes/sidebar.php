<!-- Modern Responsive Sidebar - Redesigned -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 h-screen transition-all duration-300 ease-in-out transform -translate-x-full lg:translate-x-0 bg-slate-900 border-r border-slate-700/50 shadow-2xl">
    <div class="h-full flex flex-col bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900">
        <!-- Sidebar Header -->
        <div class="flex-shrink-0 px-6 py-6 border-b border-slate-700/40 bg-slate-900/95">
            <div class="flex items-center justify-center">
                <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-10 w-auto">
                <div class="ml-3">
                    <h2 class="text-lg font-bold text-slate-100">Chair Panel</h2>
                    <p class="text-xs text-blue-300">Alumni Management</p>
                </div>
            </div>
        </div>
        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-6 overflow-y-auto custom-scrollbar">
            <ul class="space-y-1.5">
                <!-- Dashboard -->
                <li>
                    <a href="index.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-blue-500/10 hover:text-white hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 group border border-transparent hover:border-blue-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:shadow-blue-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-blue-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Dashboard</span>
                        <div class="ml-auto w-2 h-2 bg-blue-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Manage Alumni -->
                <li>
                    <button type="button" onclick="toggleSubmenu('alumniSubmenu')" class="flex items-center justify-between w-full px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-emerald-600/20 hover:to-emerald-500/10 hover:text-white hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 group border border-transparent hover:border-emerald-500/20">
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="p-2.5 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg group-hover:shadow-emerald-500/25 transition-all duration-300 group-hover:scale-110">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-emerald-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <span class="font-semibold text-sm tracking-wide">Manage Alumni</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg id="alumniArrow" class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <ul id="alumniSubmenu" class="hidden mt-2 ml-8 space-y-1 border-l-2 border-emerald-500/30 pl-4">
                        <li>
                            <a href="alumni_list.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-emerald-500/20 rounded-lg group-hover:bg-emerald-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Alumni Lists</span>
                            </a>
                        </li>
                        <li>
                            <a href="employment_status.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-emerald-500/20 rounded-lg group-hover:bg-emerald-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Employment Status</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li>
                    <a href="chair-reports.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-violet-600/20 hover:to-violet-500/10 hover:text-white hover:shadow-lg hover:shadow-violet-500/10 transition-all duration-300 group border border-transparent hover:border-violet-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl shadow-lg group-hover:shadow-violet-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-violet-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Reports</span>
                        <div class="ml-auto w-2 h-2 bg-violet-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Job Posting -->
                <li>
                    <button type="button" onclick="toggleSubmenu('jobPostingSubmenu')" class="flex items-center justify-between w-full px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-amber-600/20 hover:to-amber-500/10 hover:text-white hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 group border border-transparent hover:border-amber-500/20">
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="p-2.5 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg group-hover:shadow-amber-500/25 transition-all duration-300 group-hover:scale-110">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-amber-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <span class="font-semibold text-sm tracking-wide">Job Posting</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-amber-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg id="jobPostingArrow" class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <ul id="jobPostingSubmenu" class="hidden mt-2 ml-8 space-y-1 border-l-2 border-amber-500/30 pl-4">
                        <li>
                            <a href="create-posts.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-amber-500/10 hover:text-amber-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-amber-500/20 rounded-lg group-hover:bg-amber-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Create Post</span>
                            </a>
                        </li>
                        <li>
                            <a href="manage-posts.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-amber-500/10 hover:text-amber-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-amber-500/20 rounded-lg group-hover:bg-amber-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Manage Posts</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Notifications -->
                <li>
                    <button type="button" onclick="toggleSubmenu('notificationsSubmenu')" class="flex items-center justify-between w-full px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-rose-600/20 hover:to-rose-500/10 hover:text-white hover:shadow-lg hover:shadow-rose-500/10 transition-all duration-300 group border border-transparent hover:border-rose-500/20">
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="p-2.5 bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl shadow-lg group-hover:shadow-rose-500/25 transition-all duration-300 group-hover:scale-110">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5V17z"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-rose-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <span class="font-semibold text-sm tracking-wide">Notifications</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-rose-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg id="notificationsArrow" class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <ul id="notificationsSubmenu" class="hidden mt-2 ml-8 space-y-1 border-l-2 border-rose-500/30 pl-4">
                        <li>
                            <a href="sms.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-rose-500/10 hover:text-rose-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-rose-500/20 rounded-lg group-hover:bg-rose-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">SMS</span>
                            </a>
                        </li>
                        <li>
                            <a href="email.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-rose-500/10 hover:text-rose-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-rose-500/20 rounded-lg group-hover:bg-rose-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">EMAIL</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Surveys -->
                <li>
                    <button type="button" onclick="toggleSubmenu('surveysSubmenu')" class="flex items-center justify-between w-full px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-cyan-600/20 hover:to-cyan-500/10 hover:text-white hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 group border border-transparent hover:border-cyan-500/20">
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="p-2.5 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-lg group-hover:shadow-cyan-500/25 transition-all duration-300 group-hover:scale-110">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-cyan-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <span class="font-semibold text-sm tracking-wide">Surveys</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-cyan-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg id="surveysArrow" class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <ul id="surveysSubmenu" class="hidden mt-2 ml-8 space-y-1 border-l-2 border-cyan-500/30 pl-4">
                        <li>
                            <a href="create-survey.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-cyan-500/10 hover:text-cyan-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-cyan-500/20 rounded-lg group-hover:bg-cyan-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Create Survey</span>
                            </a>
                        </li>
                        <li>
                            <a href="manage-surveys.php" class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 rounded-xl hover:bg-cyan-500/10 hover:text-cyan-300 text-sm transition-all duration-200 group">
                                <div class="p-1.5 bg-cyan-500/20 rounded-lg group-hover:bg-cyan-500/30 transition-colors duration-200">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Manage Surveys</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Profile -->
                <li>
                    <a href="chair_profile.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-slate-600/20 hover:to-slate-500/10 hover:text-white hover:shadow-lg hover:shadow-slate-500/10 transition-all duration-300 group border border-transparent hover:border-slate-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl shadow-lg group-hover:shadow-slate-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-slate-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Profile</span>
                        <div class="ml-auto w-2 h-2 bg-slate-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>
            </ul>

            <!-- Logout Section -->
            <div class="mt-8 pt-6 border-t border-slate-700/50">
                <a href="../logout.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-red-600/20 hover:to-red-500/10 hover:text-white hover:shadow-lg hover:shadow-red-500/10 transition-all duration-300 group border border-transparent hover:border-red-500/20">
                    <div class="relative">
                        <div class="p-2.5 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg group-hover:shadow-red-500/25 transition-all duration-300 group-hover:scale-110">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-red-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    <span class="font-semibold text-sm tracking-wide">Logout</span>
                    <div class="ml-auto w-2 h-2 bg-red-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
            </div>

            <!-- Sidebar Mini Profile (Program Chair) -->
            <?php
            $chair_name = $_SESSION['chair_name'] ?? 'Program Chair';
            $profile_photo = $_SESSION['profile_photo_path'] ?? '';
            if (!empty($profile_photo) && strpos($profile_photo, 'ui-avatars.com') === false) {
                $profile_photo_url = '../admin/chair-uploads/' . htmlspecialchars($profile_photo);
            } else {
                $profile_photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($chair_name) . '&background=0D8ABC&color=fff';
            }
            ?>
            <div class="sticky bottom-0 left-0 z-10 px-2 pb-4 pt-2 bg-gradient-to-t from-slate-900/95 via-slate-900/80 to-transparent">
                <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-slate-800/80 to-blue-900/60 rounded-2xl shadow-lg border border-slate-700/60">
                    <div class="relative">
                        <img src="<?php echo $profile_photo_url; ?>" alt="Profile" class="h-12 w-12 rounded-full object-cover border-2 border-blue-400 shadow-md">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-100 truncate"><?php echo htmlspecialchars($chair_name); ?></p>
                        <p class="text-xs text-blue-300 font-medium">Program Chair</p>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <a href="chair_profile.php" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-50/10 rounded-lg transition-colors duration-200" title="Profile">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                        <a href="../logout.php" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50/10 rounded-lg transition-colors duration-200" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
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
