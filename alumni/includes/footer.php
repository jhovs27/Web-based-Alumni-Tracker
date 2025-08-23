<footer class="bg-gradient-to-b from-sky-50 via-white to-indigo-50 text-gray-700 mt-auto lg:ml-72 border-t border-indigo-100">
    <!-- Decorative animated gradient bar -->
    <div class="px-4 sm:px-6 lg:px-8 pt-6">
        <div class="animated-gradient-bar h-1 w-full rounded-full"></div>
    </div>

        <!-- Main Footer Content -->
    <div class="px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
            <!-- About Alumni -->
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-10 w-10 rounded-full shadow" />
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">SLSU-HC Alumni</h3>
                        <p class="text-indigo-700/80 text-sm">Together beyond graduation</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed">
                    A community for lifelong connections. Discover opportunities, give back, and grow with fellow alumni.
                </p>
                <div class="flex space-x-2">
                    <a href="#" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 transition-colors duration-200" title="Facebook">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="#" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-rose-500 text-white hover:bg-rose-600 transition-colors duration-200" title="Email">
                        <i class="fas fa-envelope text-sm"></i>
                    </a>
                    <a href="#" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-sky-400 text-white hover:bg-sky-500 transition-colors duration-200" title="Twitter">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Explore -->
            <div class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-compass mr-2 text-indigo-600"></i>
                    Explore
                </h4>
                <ul class="space-y-2">
                    <li><a href="index.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-home w-5 text-indigo-500 mr-2"></i>Dashboard</a></li>
                    <li><a href="events.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-calendar-check w-5 text-indigo-500 mr-2"></i>Events</a></li>
                    <li><a href="jobs.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-briefcase w-5 text-indigo-500 mr-2"></i>Job Board</a></li>
                    <li><a href="network.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-users w-5 text-indigo-500 mr-2"></i>Alumni Network</a></li>
                    <li><a href="surveys.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-poll w-5 text-indigo-500 mr-2"></i>Surveys</a></li>
                    <li><a href="profile.php" class="flex items-center text-sm text-gray-600 hover:text-indigo-700 transition-colors duration-150"><i class="fas fa-user w-5 text-indigo-500 mr-2"></i>My Profile</a></li>
                    </ul>
                </div>

            <!-- Community at a Glance -->
            <div class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-star mr-2 text-indigo-600"></i>
                    Community at a Glance
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white border border-indigo-100 p-4 text-center shadow-sm">
                        <div class="text-xl font-bold text-indigo-600">
                            <?php
                            try {
                                if (isset($conn)) {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM alumni");
                                    echo number_format($stmt->fetchColumn());
                                } else { echo "500+"; }
                            } catch (Exception $e) { echo "500+"; }
                            ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Registered Alumni</p>
                    </div>
                    <div class="rounded-xl bg-white border border-indigo-100 p-4 text-center shadow-sm">
                        <div class="text-xl font-bold text-emerald-600">
                            <?php
                            try {
                                if (isset($conn)) {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM alumni_events WHERE status IN ('Published','Active')");
                                    echo number_format($stmt->fetchColumn());
                                } else { echo "20+"; }
                            } catch (Exception $e) { echo "20+"; }
                            ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Upcoming Events</p>
                    </div>
                    <div class="rounded-xl bg-white border border-indigo-100 p-4 text-center shadow-sm">
                        <div class="text-xl font-bold text-purple-600">
                            <?php
                            try {
                                if (isset($conn)) {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM job_posts WHERE status IN ('Published','Active','published')");
                                    echo number_format($stmt->fetchColumn());
                                } else { echo "50+"; }
                            } catch (Exception $e) { echo "50+"; }
                            ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Open Jobs</p>
                    </div>
                    <div class="rounded-xl bg-white border border-indigo-100 p-4 text-center shadow-sm">
                        <div class="text-xl font-bold text-amber-600"><?php echo date('Y') - 1999; ?>+</div>
                        <p class="text-xs text-gray-500 mt-1">Years Strong</p>
                        </div>
                    </div>
                </div>

            <!-- Contact -->
            <div class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-envelope-open-text mr-2 text-indigo-600"></i>
                    Contact
                </h4>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt text-indigo-600 mt-0.5"></i>
                        <div>
                            <p class="text-gray-700 font-medium">Hinunangan, Southern Leyte</p>
                            <p class="text-gray-500">Philippines 6524</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-phone text-indigo-600"></i>
                        <a href="tel:+63XXXXXXXXXX" class="text-gray-700 hover:text-indigo-700 transition-colors duration-150">+63 XXX XXX XXXX</a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-envelope text-indigo-600"></i>
                        <a href="mailto:alumni@slsu-hc.edu.ph" class="text-gray-700 hover:text-indigo-700 transition-colors duration-150">alumni@slsu-hc.edu.ph</a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-globe text-indigo-600"></i>
                        <a href="https://www.slsu.edu.ph" target="_blank" class="text-gray-700 hover:text-indigo-700 transition-colors duration-150">www.slsu.edu.ph</a>
                    </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Bottom Bar -->
    <div class="border-t border-indigo-100 bg-white/60">
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row justify-between items-center space-y-3 lg:space-y-0">
                <p class="text-sm text-gray-600 text-center lg:text-left">&copy; <?php echo date('Y'); ?> SLSU-HC Alumni Community. All rights reserved.</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span class="flex items-center"><i class="fas fa-code mr-2"></i>Alumni Portal v2.0</span>
                    <span class="hidden sm:inline">|</span>
                    <span class="flex items-center"><i class="fas fa-clock mr-2"></i>Updated <?php echo date('M Y'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="fixed bottom-6 right-6 w-11 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg transition-all duration-300 opacity-0 invisible z-50 hover:scale-110">
        <i class="fas fa-chevron-up"></i>
    </button>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.remove('opacity-0', 'invisible');
                scrollToTopBtn.classList.add('opacity-100', 'visible');
            } else {
                scrollToTopBtn.classList.add('opacity-0', 'invisible');
                scrollToTopBtn.classList.remove('opacity-100', 'visible');
            }
        });
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<style>
.animated-gradient-bar {
	background: linear-gradient(90deg, #6366f1, #06b6d4, #a855f7, #6366f1);
	background-size: 300% 100%;
	animation: moveGradient 8s linear infinite;
}

@keyframes moveGradient {
	0% { background-position: 0% 50%; }
	100% { background-position: 300% 50%; }
    }
</style>

<?php include __DIR__ . '/scripts.php'; ?> 
<!-- na changed -->