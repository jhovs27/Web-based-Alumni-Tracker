<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU Alumni Tracer - Login</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Google Sign-In API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        :root {
            --primary: #0056b3;
            --secondary: #e9a90f;
            --dark: #1a2a3a;
            --light: #f8f9fa;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/hc.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            /* Performance optimizations */
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            will-change: scroll-position;
        }

        .login-outer {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
        }

        .login-container {
            background: #F3FCFA;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            padding: 2rem;
            /* Hardware acceleration */
            transform: translateZ(0);
            will-change: transform;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        .form-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', sans-serif;
        }

        .form-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .form-header .encouragement {
            color: var(--success);
            font-size: 1.15rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .role-selector {
            margin-bottom: 2rem;
            text-align: center;
        }

        .role-selector h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
        }

        .role-options {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .role-option {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            /* Simplified transition for better performance */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 250px;
            text-align: center;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* Hardware acceleration */
            transform: translateZ(0);
            will-change: transform;
            contain: layout style paint;
        }

        .role-option:hover {
            transform: translateY(-3px) translateZ(0);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .role-option.selected {
            border-color: var(--primary);
            background: rgba(0, 86, 179, 0.05);
        }

        .role-option i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .role-option h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', sans-serif;
        }

        .role-option p {
            color: #666;
            font-size: 0.9rem;
        }

        .login-form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            /* Performance optimizations */
            transform: translateZ(0);
            will-change: transform;
            contain: layout style paint;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            /* Simplified transition */
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            /* Optimize input rendering */
            -webkit-appearance: none;
            appearance: none;
            /* Hardware acceleration */
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
            outline: none;
        }

        .login-btn {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            /* Simplified transition */
            transition: background-color 0.2s ease;
            font-weight: 500;
            /* Hardware acceleration */
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .login-btn:hover {
            background: #004494;
        }

        .forgot-password {
            text-align: center;
            margin: 1rem 0;
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .google-signin {
            text-align: center;
        }

        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid #ddd;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            /* Simplified transition */
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            /* Hardware acceleration */
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .google-btn:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .hidden {
            display: none !important;
        }

        /* Optimize for mobile */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .role-options {
                flex-direction: column;
                align-items: center;
            }
            
            .role-option {
                width: 100%;
                max-width: 300px;
            }
            
            .login-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-outer">
        <div class="login-container">
            <div class="form-header">
                <h1>Welcome Back!</h1>
                <p>Sign in to your SLSU-HC Alumni Tracer account</p>
                <div class="encouragement">Stay connected with your alma mater!</div>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['login_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['login_success']); ?>
                </div>
                <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                </div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

            <div class="role-selector">
                <h2>Select Your Role</h2>
                <div class="role-options">
                    <div class="role-option" data-role="alumni">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Alumni</h3>
                        <p>Sign in as a graduate</p>
                    </div>
                    <div class="role-option" data-role="admin">
                        <i class="fas fa-user-shield"></i>
                        <h3>Admin</h3>
                        <p>Sign in as administrator</p>
                    </div>
                    <div class="role-option" data-role="programchair">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>Program Chair</h3>
                        <p>Sign in as program chair</p>
                    </div>
                </div>
            </div>

            <form id="loginForm" action="process_login.php" method="POST">
                <input type="hidden" id="userType" name="userType" value="">
                
                <!-- Alumni Login Fields -->
                <div id="alumniFields" class="login-form-section hidden">
                    <div class="form-group">
                        <label for="alumniId"><i class="fas fa-id-card"></i> Alumni ID</label>
                        <input type="text" id="alumniId" name="alumniId" class="form-control" placeholder="Enter your Alumni ID" required>
                    </div>
                    <div class="form-group">
                        <label for="alumniPassword"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="alumniPassword" name="alumniPassword" class="form-control" placeholder="Enter your password" required minlength="8" autocomplete="current-password">
                        <small style="color:#888;">Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.</small>
                    </div>
                    <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Sign In</button>
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>
                    <div class="divider">
                        <span>Or Sign In with</span>
                    </div>
                    <div class="google-signin">
                        <button type="button" class="google-btn">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width:24px;height:24px;">
                            Sign in with Google
                        </button>
                    </div>
                </div>

                <!-- Admin Login Fields -->
                <div id="adminFields" class="login-form-section hidden">
                    <div class="form-group">
                        <label for="adminUsername"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="adminUsername" name="adminUsername" class="form-control" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="adminPassword"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="adminPassword" name="adminPassword" class="form-control" placeholder="Enter your password" required minlength="8" autocomplete="current-password">
                        <small style="color:#888;">Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.</small>
                    </div>
                    <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>
                    <div class="divider">
                        <span>Or Sign In with</span>
                    </div>
                    <div class="google-signin">
                        <button type="button" class="google-btn">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width:24px;height:24px;">
                            Sign in with Google
                        </button>
                    </div>
                </div>

                <!-- Program Chair Login Fields -->
                <div id="programChairFields" class="login-form-section hidden">
                    <div class="form-group">
                        <label for="pcUsername"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="pcUsername" name="pcUsername" class="form-control" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="pcPassword"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="pcPassword" name="pcPassword" class="form-control" placeholder="Enter your password" required minlength="8" autocomplete="current-password">
                        <small style="color:#888;">Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.</small>
                    </div>
                    <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>
                    <div class="divider">
                        <span>Or Sign In with</span>
                    </div>
                    <div class="google-signin">
                        <button type="button" class="google-btn">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width:24px;height:24px;">
                            Sign in with Google
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cache DOM elements for better performance
        const elements = {
            roleOptions: document.querySelectorAll('.role-option'),
            userTypeInput: document.getElementById('userType'),
            alumniFields: document.getElementById('alumniFields'),
            adminFields: document.getElementById('adminFields'),
            programChairFields: document.getElementById('programChairFields'),
            loginForm: document.getElementById('loginForm')
        };

        // Debounce function for performance
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showFields(role) {
            // Hide all fields
            elements.alumniFields.classList.add('hidden');
            elements.adminFields.classList.add('hidden');
            elements.programChairFields.classList.add('hidden');
            
            // Show relevant fields
            if (role === 'alumni') {
                elements.alumniFields.classList.remove('hidden');
            } else if (role === 'admin') {
                elements.adminFields.classList.remove('hidden');
            } else if (role === 'programchair') {
                elements.programChairFields.classList.remove('hidden');
            }
            
            // Set required attributes
            document.getElementById('alumniId').required = (role === 'alumni');
            document.getElementById('alumniPassword').required = (role === 'alumni');
            document.getElementById('adminUsername').required = (role === 'admin');
            document.getElementById('adminPassword').required = (role === 'admin');
            document.getElementById('pcUsername').required = (role === 'programchair');
            document.getElementById('pcPassword').required = (role === 'programchair');
        }

        // Optimized role selection with event delegation
        elements.roleOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                elements.roleOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                const role = this.getAttribute('data-role');
                elements.userTypeInput.value = role;
                showFields(role);
            });
            
            // Keyboard accessibility
            option.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Password validation with debouncing
        function validatePassword(password) {
            const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
            return pattern.test(password);
        }

        // Debounced password validation
        const debouncedPasswordValidation = debounce(validatePassword, 100);

        // Form submission with optimized validation
        elements.loginForm.addEventListener('submit', function(e) {
            const role = elements.userTypeInput.value;
            
            if (!role) {
                alert('Please select your role first.');
                e.preventDefault();
                return false;
            }
            
            let password = '';
            if (role === 'alumni') password = document.getElementById('alumniPassword').value;
            if (role === 'admin') password = document.getElementById('adminPassword').value;
            if (role === 'programchair') password = document.getElementById('pcPassword').value;
            
            if (!validatePassword(password)) {
                alert('Password does not meet requirements! It must be at least 8 characters and include uppercase, lowercase, number, and special character.');
                e.preventDefault();
                return false;
            }
            
            // Allow form submission
            return true;
        });

        // Google Sign-In handling with error handling
        function handleGoogleSignIn(response) {
            const credential = response.credential;
            const userRole = elements.userTypeInput.value;
            
            if (!userRole) {
                alert('Please select your role before signing in with Google.');
                return;
            }
            
            // Send the credential and role to your backend for verification
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
                    alert(data.message || 'Google Sign-In failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during Google Sign-In. Please try again.');
            });
        }

        // Initialize Google Sign-In when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Google Sign-In buttons
            if (typeof google !== 'undefined' && google.accounts) {
                google.accounts.id.initialize({
                    client_id: 'YOUR_GOOGLE_CLIENT_ID', // Replace with your actual Google Client ID
                    callback: handleGoogleSignIn
                });

                // Add click handlers to Google buttons
                document.querySelectorAll('.google-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        if (typeof google !== 'undefined' && google.accounts) {
                            google.accounts.id.prompt();
                        } else {
                            alert('Google Sign-In is not available. Please try again later.');
                        }
                    });
                });
            }
        });

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to form when role is selected
            elements.roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    setTimeout(() => {
                        const activeForm = document.querySelector('.login-form-section:not(.hidden)');
                        if (activeForm) {
                            activeForm.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'start' 
                            });
                        }
                    }, 100);
                });
            });
        });
    </script>
</body>
</html> 