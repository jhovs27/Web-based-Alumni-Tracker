<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU Alumni Tracer - Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        body { 
            font-family: 'Inter', 'Poppins', sans-serif;
            background: var(--dark-gradient);
            overflow-x: hidden;
        }
        
        /* Animated Background Particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .particle:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 40px; height: 40px; top: 80%; left: 20%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 100px; height: 100px; top: 40%; left: 70%; animation-delay: 1s; }
        .particle:nth-child(5) { width: 50px; height: 50px; top: 10%; left: 60%; animation-delay: 3s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        /* Enhanced Card Animations */
        .login-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        /* Role Selection Cards */
        .role-option {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .role-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .role-option:hover::before {
            left: 100%;
        }
        
        .role-option:hover {
            transform: translateY(-8px) scale(1.05);
            border-color: #667eea;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
        
        .role-option.selected {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        }
        
        /* Enhanced Form Controls */
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        
        /* Animated Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: all 0.5s ease;
            transform: translate(-50%, -50%);
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        /* Google Button */
        .google-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        /* Loading Animation */
        .loading-spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Enhanced Logo Animation */
        .logo-container {
            position: relative;
            display: inline-block;
        }
        
        .logo-container::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: linear-gradient(45deg, #667eea, #764ba2, #667eea);
            border-radius: 50%;
            z-index: -1;
            animation: rotate 3s linear infinite;
            opacity: 0.3;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Pulse Animation for Icons */
        .icon-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Enhanced Input Labels */
        .input-label {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .input-label::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .input-label:hover::after {
            width: 100%;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 640px) {
            .particle { display: none; }
            .login-card { margin: 10px; }
        }
        
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }
        
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 relative">
    <!-- Animated Background Particles -->
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <!-- Enhanced Background with Animated Gradient -->
    <div class="fixed inset-0 z-0" style="background: linear-gradient(135deg, rgba(15,23,42,0.95) 0%, rgba(30,64,175,0.8) 50%, rgba(102,126,234,0.6) 100%), url('images/hc.jpg'); background-size: cover; background-position: center; background-attachment: fixed; animation: gradientShift 10s ease-in-out infinite alternate;"></div>
    
    <!-- Enhanced Back to Home Button -->
    <a href="index.php" class="fixed top-4 left-4 z-50 group" data-aos="fade-down">
        <div class="bg-white bg-opacity-20 backdrop-blur-lg text-white hover:text-blue-200 hover:bg-opacity-30 px-6 py-3 rounded-2xl shadow-xl border border-white border-opacity-20 transition-all duration-300 hover:scale-105 flex items-center gap-3 font-medium">
            <i class="fas fa-home text-lg group-hover:animate-bounce"></i>
        <span class="hidden sm:inline">Back to Home</span>
        </div>
    </a>
    
    <!-- Main Content Container -->
    <div class="w-full max-w-2xl mx-auto z-10 relative px-4">
        <div class="login-card rounded-3xl shadow-2xl p-8 sm:p-12 relative" data-aos="zoom-in" data-aos-duration="800">
            
            <!-- Enhanced Logo and Header -->
            <div class="flex flex-col items-center mb-10" data-aos="fade-up" data-aos-delay="200">
                <div class="logo-container mb-4">
                    <img src="images/logo.png" alt="SLSU Logo" class="h-20 w-20 rounded-full shadow-lg">
                </div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2 tracking-tight">
                    WELCOME BACK
                </h1>
                <p class="text-gray-600 text-lg font-medium">Sign in to SLSU Alumni Tracer</p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mt-3"></div>
            </div>

            <!-- Enhanced Role Selector -->
            <div class="mb-10" data-aos="fade-up" data-aos-delay="400">
                <h2 class="text-xl font-bold text-gray-800 text-center mb-6 flex items-center justify-center gap-2">
                    <i class="fas fa-users text-blue-600 icon-pulse"></i>
                    Choose Your Role
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="role-option group rounded-2xl p-6 flex flex-col items-center cursor-pointer" data-role="alumni" tabindex="0" data-aos="fade-up" data-aos-delay="500">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-graduate text-2xl text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Alumni</h3>
                        <p class="text-gray-600 text-sm text-center">Graduate Access</p>
                    </div>
                    <div class="role-option group rounded-2xl p-6 flex flex-col items-center cursor-pointer" data-role="admin" tabindex="0" data-aos="fade-up" data-aos-delay="600">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-shield text-2xl text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Admin</h3>
                        <p class="text-gray-600 text-sm text-center">Administrator Access</p>
                    </div>
                    <div class="role-option group rounded-2xl p-6 flex flex-col items-center cursor-pointer" data-role="programchair" tabindex="0" data-aos="fade-up" data-aos-delay="700">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chalkboard-teacher text-2xl text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Program Chair</h3>
                        <p class="text-gray-600 text-sm text-center">Program Access</p>
                    </div>
                </div>
            </div>

            <!-- Error Message Display -->
            <?php if (isset($_SESSION['login_error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: <?php echo json_encode($_SESSION['login_error']); ?>,
                        confirmButtonColor: '#667eea',
                        background: '#fff',
                        customClass: {
                            popup: 'rounded-3xl',
                            title: 'font-bold',
                            confirmButton: 'rounded-xl px-6 py-3'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                });
            </script>
            <?php unset($_SESSION['login_error']); endif; ?>

            <!-- Enhanced Login Form -->
            <form id="loginForm" autocomplete="off" method="POST" action="process_login.php">
                <input type="hidden" id="userType" name="userType" value="">
                
                <!-- Alumni Fields -->
                <div id="alumniFields" class="login-form-section" style="display:none;" data-aos="fade-up">
                    <div class="mb-6">
                        <label for="alumniId" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-id-card text-blue-600"></i> 
                            Alumni ID
                        </label>
                        <input type="text" id="alumniId" name="alumniId" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300" placeholder="Enter your Alumni ID" required>
                    </div>
                    <div class="mb-4 relative">
                        <label for="alumniPassword" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-lock text-blue-600"></i> 
                            Password
                        </label>
                        <input type="password" id="alumniPassword" name="alumniPassword" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300 pr-14" placeholder="Enter your password" required minlength="8" autocomplete="new-password">
                        <button type="button" tabindex="-1" class="absolute right-4 top-14 text-gray-500 hover:text-blue-600 focus:outline-none password-toggle transition-colors duration-200" data-target="alumniPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye text-xl"></i>
                        </button>
                        <small class="text-gray-500 text-sm mt-2 block">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    <div class="mt-8 mb-4">
                        <button type="submit" class="btn-primary w-full text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-3 text-lg relative z-10">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Sign In</span>
                        </button>
                    </div>
                    <div class="text-center mb-6">
                        <a href="forgot-password.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition-colors duration-200">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="flex items-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500 text-sm font-semibold uppercase tracking-wide">Or Continue with</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    <div class="flex justify-center">
                        <button type="button" class="google-btn flex items-center gap-3 bg-white border-2 border-gray-200 px-6 py-3 rounded-xl shadow-sm font-semibold text-gray-700">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="w-6 h-6">
                            <span>Sign in with Google</span>
                        </button>
                    </div>
                </div>

                <!-- Admin Fields -->
                <div id="adminFields" class="login-form-section" style="display:none;" data-aos="fade-up">
                    <div class="mb-6">
                        <label for="adminUsername" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-user text-blue-600"></i> 
                            Username
                        </label>
                        <input type="text" id="adminUsername" name="adminUsername" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-4 relative">
                        <label for="adminPassword" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-lock text-blue-600"></i> 
                            Password
                        </label>
                        <input type="password" id="adminPassword" name="adminPassword" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300 pr-14" placeholder="Enter your password" required minlength="8" autocomplete="new-password">
                        <button type="button" tabindex="-1" class="absolute right-4 top-14 text-gray-500 hover:text-blue-600 focus:outline-none password-toggle transition-colors duration-200" data-target="adminPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye text-xl"></i>
                        </button>
                        <small class="text-gray-500 text-sm mt-2 block">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    <div class="mt-8 mb-4">
                        <button type="submit" class="btn-primary w-full text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-3 text-lg relative z-10">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Login</span>
                        </button>
                    </div>
                    <div class="text-center mb-6">
                        <a href="forgot-password.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition-colors duration-200">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="flex items-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500 text-sm font-semibold uppercase tracking-wide">Or Continue with</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    <div class="flex justify-center">
                        <button type="button" class="google-btn flex items-center gap-3 bg-white border-2 border-gray-200 px-6 py-3 rounded-xl shadow-sm font-semibold text-gray-700">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="w-6 h-6">
                            <span>Sign in with Google</span>
                        </button>
                    </div>
                </div>

                <!-- Program Chair Fields -->
                <div id="programChairFields" class="login-form-section" style="display:none;" data-aos="fade-up">
                    <div class="mb-6">
                        <label for="pcUsername" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-user text-blue-600"></i> 
                            Username
                        </label>
                        <input type="text" id="pcUsername" name="pcUsername" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-4 relative">
                        <label for="pcPassword" class="input-label block font-semibold text-gray-700 mb-3 flex items-center gap-3">
                            <i class="fas fa-lock text-blue-600"></i> 
                            Password
                        </label>
                        <input type="password" id="pcPassword" name="pcPassword" class="form-control w-full px-5 py-4 rounded-xl text-lg focus:outline-none transition-all duration-300 pr-14" placeholder="Enter your password" required minlength="8" autocomplete="new-password">
                        <button type="button" tabindex="-1" class="absolute right-4 top-14 text-gray-500 hover:text-blue-600 focus:outline-none password-toggle transition-colors duration-200" data-target="pcPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye text-xl"></i>
                        </button>
                        <small class="text-gray-500 text-sm mt-2 block">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    <div class="mt-8 mb-4">
                        <button type="submit" class="btn-primary w-full text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-3 text-lg relative z-10">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Login</span>
                        </button>
                    </div>
                    <div class="text-center mb-6">
                        <a href="forgot-password.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition-colors duration-200">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="flex items-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500 text-sm font-semibold uppercase tracking-wide">Or Continue with</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    <div class="flex justify-center">
                        <button type="button" class="google-btn flex items-center gap-3 bg-white border-2 border-gray-200 px-6 py-3 rounded-xl shadow-sm font-semibold text-gray-700">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="w-6 h-6">
                            <span>Sign in with Google</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Enhanced Card-based user type selection
        const roleOptions = document.querySelectorAll('.role-option');
        const userTypeInput = document.getElementById('userType');
        const alumniFields = document.getElementById('alumniFields');
        const adminFields = document.getElementById('adminFields');
        const programChairFields = document.getElementById('programChairFields');
        const loginBtn = document.querySelector('.btn-primary');

        function showFields(role) {
            // Hide all fields first
            alumniFields.style.display = 'none';
            adminFields.style.display = 'none';
            programChairFields.style.display = 'none';
            
            // Show the selected role's fields with animation
            setTimeout(() => {
                if (role === 'alumni') {
                    alumniFields.style.display = 'block';
                    AOS.refresh();
                } else if (role === 'admin') {
                    adminFields.style.display = 'block';
                    AOS.refresh();
                } else if (role === 'programchair') {
                    programChairFields.style.display = 'block';
                    AOS.refresh();
                }
            }, 150);
            
            // Set required attributes
            document.getElementById('alumniId').required = (role === 'alumni');
            document.getElementById('alumniPassword').required = (role === 'alumni');
            document.getElementById('adminUsername').required = (role === 'admin');
            document.getElementById('adminPassword').required = (role === 'admin');
            document.getElementById('pcUsername').required = (role === 'programchair');
            document.getElementById('pcPassword').required = (role === 'programchair');
        }

        roleOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                roleOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                const role = this.getAttribute('data-role');
                userTypeInput.value = role;
                showFields(role);
                
                // Add a nice shake animation
                this.style.animation = 'none';
                this.offsetHeight; // Trigger reflow
                this.style.animation = 'pulse 0.5s ease-in-out';
            });
            
            option.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Enhanced password validation
        function validatePassword(password) {
            const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
            return pattern.test(password);
        }

        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const role = document.getElementById('userType').value;
            let password = '';
            
            if (role === 'alumni') password = document.getElementById('alumniPassword').value;
            if (role === 'admin') password = document.getElementById('adminPassword').value;
            if (role === 'programchair') password = document.getElementById('pcPassword').value;
            
            if (!validatePassword(password)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.',
                    confirmButtonColor: '#667eea',
                    customClass: {
                        popup: 'rounded-3xl',
                        title: 'font-bold',
                        confirmButton: 'rounded-xl px-6 py-3'
                    }
                });
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('.btn-primary');
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading-spinner"></div> <span>Signing in...</span>';
            submitBtn.disabled = true;
            
            // Reset after 3 seconds (in case of slow response)
            setTimeout(() => {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Enhanced password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.style.color = '#667eea';
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.style.color = '#6b7280';
                }
                
                // Add a nice animation
                icon.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    icon.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Enhanced Google Sign-In
        function handleGoogleSignIn(response) {
            const credential = response.credential;
            const userRole = document.getElementById('userType').value;
            
            if (!userRole) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please Select Role',
                    text: 'Please select your user type before signing in with Google.',
                    confirmButtonColor: '#667eea',
                    customClass: {
                        popup: 'rounded-3xl',
                        title: 'font-bold',
                        confirmButton: 'rounded-xl px-6 py-3'
                    }
                });
                return;
            }
            
            // Show loading state
            const googleBtns = document.querySelectorAll('.google-btn');
            googleBtns.forEach(btn => {
                btn.innerHTML = '<div class="loading-spinner"></div> <span>Signing in...</span>';
                btn.disabled = true;
            });
            
            fetch('auth/google-signin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credential: credential,
                    role: userRole
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sign-in Failed',
                        text: data.message || 'Google Sign-In failed. Please try again.',
                        confirmButtonColor: '#667eea',
                        customClass: {
                            popup: 'rounded-3xl',
                            title: 'font-bold',
                            confirmButton: 'rounded-xl px-6 py-3'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'An error occurred during Google Sign-In. Please try again.',
                    confirmButtonColor: '#667eea',
                    customClass: {
                        popup: 'rounded-3xl',
                        title: 'font-bold',
                        confirmButton: 'rounded-xl px-6 py-3'
                    }
                });
            })
            .finally(() => {
                // Reset button state
                googleBtns.forEach(btn => {
                    btn.innerHTML = '<img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="w-6 h-6"> <span>Sign in with Google</span>';
                    btn.disabled = false;
                });
            });
        }

        // Initialize Google Sign-In
        window.onload = function() {
            google.accounts.id.initialize({
                client_id: 'YOUR_GOOGLE_CLIENT_ID', // Replace with your actual Google Client ID
                callback: handleGoogleSignIn
            });
            
            document.querySelectorAll('.google-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userRole = document.getElementById('userType').value;
                    if (!userRole) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Please Select Role',
                            text: 'Please select your user type before signing in with Google.',
                            confirmButtonColor: '#667eea',
                            customClass: {
                                popup: 'rounded-3xl',
                                title: 'font-bold',
                                confirmButton: 'rounded-xl px-6 py-3'
                            }
                        });
                        return;
                    }
                    google.accounts.id.prompt();
                });
            });
        };

        // Add some dynamic background effects
        document.addEventListener('mousemove', function(e) {
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.01;
                const x = e.clientX * speed;
                const y = e.clientY * speed;
                particle.style.transform = `translate(${x}px, ${y}px)`;
            });
        });

        // Add keyboard navigation for role selection
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                const currentSelected = document.querySelector('.role-option.selected');
                const roles = Array.from(document.querySelectorAll('.role-option'));
                
                if (currentSelected) {
                    const currentIndex = roles.indexOf(currentSelected);
                    let nextIndex;
                    
                    if (e.key === 'ArrowLeft') {
                        nextIndex = currentIndex > 0 ? currentIndex - 1 : roles.length - 1;
                    } else {
                        nextIndex = currentIndex < roles.length - 1 ? currentIndex + 1 : 0;
                    }
                    
                    roles[nextIndex].click();
                    roles[nextIndex].focus();
                }
            }
        });
    </script>

    <!-- Google Sign-In Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</body>
</html>

