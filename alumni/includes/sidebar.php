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