<!-- Modern Responsive Sidebar - Redesigned -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 h-screen transition-all duration-300 ease-in-out transform -translate-x-full lg:translate-x-0 bg-slate-900 border-r border-slate-700/50 shadow-2xl">
    <div class="h-full flex flex-col bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900">
        <!-- Sidebar Header -->
        <div class="flex-shrink-0 px-6 py-6 border-b border-slate-700/40 bg-slate-900/95">
            <div class="flex items-center justify-center">
                <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-10 w-auto">
                <div class="ml-3">
                    <h2 class="text-lg font-bold text-slate-100">Alumni Panel</h2>
                    <p class="text-xs text-blue-300">Alumni Portal</p>
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

                <!-- Events -->
                <li>
                    <a href="events.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-emerald-600/20 hover:to-emerald-500/10 hover:text-white hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 group border border-transparent hover:border-emerald-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg group-hover:shadow-emerald-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-emerald-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Events</span>
                        <div class="ml-auto w-2 h-2 bg-emerald-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Job Board -->
                <li>
                    <a href="jobs.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-amber-600/20 hover:to-amber-500/10 hover:text-white hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 group border border-transparent hover:border-amber-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg group-hover:shadow-amber-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-amber-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Job Board</span>
                        <div class="ml-auto w-2 h-2 bg-amber-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Board of Officers -->
                <li>
                    <a href="officers.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-violet-600/20 hover:to-violet-500/10 hover:text-white hover:shadow-lg hover:shadow-violet-500/10 transition-all duration-300 group border border-transparent hover:border-violet-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl shadow-lg group-hover:shadow-violet-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-6a2 2 0 01-2-2v-2M7 10a4 4 0 118 0 4 4 0 01-8 0zm10 10v-3a2 2 0 012-2h1"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-violet-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Board of Officers</span>
                        <div class="ml-auto w-2 h-2 bg-violet-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Surveys -->
                <li>
                    <a href="surveys.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-cyan-600/20 hover:to-cyan-500/10 hover:text-white hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 group border border-transparent hover:border-cyan-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-lg group-hover:shadow-cyan-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-cyan-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Surveys</span>
                        <div class="ml-auto w-2 h-2 bg-cyan-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Payments -->
                <li>
                    <a href="payments.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-green-600/20 hover:to-green-500/10 hover:text-white hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 group border border-transparent hover:border-green-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg group-hover:shadow-green-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-green-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">Payments</span>
                        <div class="ml-auto w-2 h-2 bg-green-400 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                </li>

                <!-- Profile -->
                <li>
                    <a href="profile.php" class="flex items-center space-x-3 px-3 py-3.5 text-slate-300 rounded-2xl hover:bg-gradient-to-r hover:from-slate-600/20 hover:to-slate-500/10 hover:text-white hover:shadow-lg hover:shadow-slate-500/10 transition-all duration-300 group border border-transparent hover:border-slate-500/20">
                        <div class="relative">
                            <div class="p-2.5 bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl shadow-lg group-hover:shadow-slate-500/25 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-slate-400/20 rounded-xl blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <span class="font-semibold text-sm tracking-wide">My Profile</span>
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

            <!-- Sidebar Mini Profile (Alumni) -->
            <?php
                $alumni_first_name = isset($alumni['first_name']) ? trim($alumni['first_name']) : '';
                $alumni_last_name = isset($alumni['last_name']) ? trim($alumni['last_name']) : '';
                $alumni_full_name = trim($alumni_first_name . ' ' . $alumni_last_name);
                $alumni_year = isset($alumni['year_graduated']) && !empty($alumni['year_graduated']) ? preg_replace('/[^0-9]/', '', (string)$alumni['year_graduated']) : '';
                $profile_photo = $alumni['profile_photo_path'] ?? '';
                if (!empty($profile_photo) && file_exists('../admin/uploads/profile_photos/' . $profile_photo)) {
                    $alumni_photo_url = '../admin/uploads/profile_photos/' . htmlspecialchars($profile_photo);
                } else {
                    $alumni_photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($alumni_full_name ?: 'Alumni') . '&background=6366F1&color=fff';
                }
            ?>
            <div class="sticky bottom-0 left-0 z-10 px-2 pb-4 pt-2 bg-gradient-to-t from-slate-900/95 via-slate-900/70 to-transparent">
                <div class="flex items-center space-x-3 p-3 bg-slate-800/60 backdrop-blur rounded-2xl shadow-lg border border-slate-700/60">
                    <div class="relative">
                        <span class="inline-flex p-0.5 rounded-full bg-gradient-to-tr from-indigo-400 via-fuchsia-400 to-emerald-400">
                            <img src="<?php echo $alumni_photo_url; ?>" alt="Profile" class="h-12 w-12 rounded-full object-cover border-2 border-slate-900">
                        </span>
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-400 border-2 border-slate-900 rounded-full" title="Online"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-100 truncate"><?php echo htmlspecialchars($alumni_full_name ?: 'Alumni'); ?></p>
                        <div class="flex items-center space-x-2">
                            <p class="text-xs text-indigo-300 font-medium">Alumni</p>
                            <?php if (!empty($alumni_year)): ?>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-300 border border-indigo-400/20">Since <?php echo htmlspecialchars($alumni_year); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <a href="profile.php" class="p-2 text-slate-300 hover:text-indigo-400 hover:bg-indigo-50/10 rounded-lg transition-colors duration-200" title="Profile">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                        <a href="settings.php" class="p-2 text-slate-300 hover:text-indigo-400 hover:bg-indigo-50/10 rounded-lg transition-colors duration-200" title="Settings">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31 2.37 2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </a>
                        <a href="../logout.php" class="p-2 text-slate-300 hover:text-rose-400 hover:bg-rose-50/10 rounded-lg transition-colors duration-200" title="Logout">
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