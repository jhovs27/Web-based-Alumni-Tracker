<!-- Footer -->
<footer class="w-full bg-gradient-to-r from-blue-600 to-blue-800 text-white mt-8">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mb-6">
            <!-- Logo and Description -->
            <div class="lg:col-span-2">
                <div class="flex items-center mb-3">
                    <img src="../images/slsu_logo.png" alt="SLSU Logo" class="h-8 w-8 mr-3 rounded-full">
                    <div>
                        <h3 class="text-lg font-bold">SLSU-HC Alumni Portal</h3>
                        <p class="text-blue-100 text-xs">Connecting alumni, building futures</p>
                    </div>
                </div>
                <p class="text-blue-100 text-xs leading-relaxed">
                    Stay connected with your alma mater and fellow alumni. Access exclusive events, 
                    job opportunities, and resources to advance your career and personal growth.
                </p>
            </div>

            <!-- Quick Links, Contact Info, and Social Media Group -->
            <div class="grid grid-cols-3 gap-10">
                <!-- Quick Links -->
                <div>
                    <h4 class="text-base font-semibold mb-3 text-white">Quick Links</h4>
                    <ul class="space-y-1">
                        <li><a href="events.php" class="text-blue-100 hover:text-white transition-colors duration-200 text-xs">Events</a></li>
                        <li><a href="jobs.php" class="text-blue-100 hover:text-white transition-colors duration-200 text-xs">Job Board</a></li>
                        <li><a href="network.php" class="text-blue-100 hover:text-white transition-colors duration-200 text-xs">Alumni Network</a></li>
                        <li><a href="surveys.php" class="text-blue-100 hover:text-white transition-colors duration-200 text-xs">Surveys</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="pr-8">
                    <h4 class="text-base font-semibold mb-3 text-white">Contact Us</h4>
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center text-blue-100">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-300 w-3"></i>
                            <span>SLSU-HC, Hinunangan</span>
                        </div>
                        <div class="flex items-center text-blue-100">
                            <i class="fas fa-phone mr-2 text-blue-300 w-3"></i>
                            <span>+63 XXX XXX XXXX</span>
                        </div>
                        <div class="flex items-center text-blue-100">
                            <i class="fas fa-envelope mr-2 text-blue-300 w-3"></i>
                            <span>alumni@slsu-hc.edu.ph</span>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="pl-4">
                    <h4 class="text-base font-semibold mb-3 text-white">Follow Us</h4>
                    <div class="flex flex-row space-x-2">
                        <a href="#" class="w-8 h-8 bg-blue-500 hover:bg-blue-400 rounded-full flex items-center justify-center transition-colors duration-200 group">
                            <i class="fab fa-facebook-f text-white text-xs group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                        <a href="#" class="w-8 h-8 bg-red-500 hover:bg-red-400 rounded-full flex items-center justify-center transition-colors duration-200 group">
                            <i class="fas fa-envelope text-white text-xs group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                        <a href="#" class="w-8 h-8 bg-blue-600 hover:bg-blue-500 rounded-full flex items-center justify-center transition-colors duration-200 group">
                            <i class="fab fa-google text-white text-xs group-hover:scale-110 transition-transform duration-200"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-blue-500 pt-4 text-center">
            <div class="text-blue-100 text-xs">
                &copy; <?php echo date('Y'); ?> SLSU-HC Alumni Portal. All rights reserved.
            </div>
        </div>
    </div>
</footer>

<!-- Ensure footer doesn't overlap with sidebar by adding margin -->
<style>
    .main-content {
        min-height: calc(100vh - 4rem); /* Account for navbar height */
        display: flex;
        flex-direction: column;
    }
    
    .main-content > div:last-child {
        flex: 1;
    }
    
    footer {
        margin-left: 0;
        z-index: 10;
    }
    
    @media (min-width: 768px) {
        footer {
            margin-left: 16rem; /* 256px = 16rem, matches sidebar width */
            width: calc(100% - 16rem);
        }
    }
    
    /* Ensure footer content is always visible */
    footer .max-w-7xl {
        max-width: none;
        width: 100%;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        footer .grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        footer {
            margin-left: 0;
            width: 100%;
        }
        
        footer .grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        /* On mobile, stack Quick Links, Contact, and Social in a row */
        footer .grid .grid {
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
    }
    
    @media (max-width: 480px) {
        /* On very small screens, stack everything vertically */
        footer .grid .grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include __DIR__ . '/scripts.php'; ?> 