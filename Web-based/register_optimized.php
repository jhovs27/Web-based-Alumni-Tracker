<?php
session_start();
require_once 'admin/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU Alumni Tracer - Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
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

        .container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
        }

        .register-container {
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

        .form-section {
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

        .section-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .employment-details {
            display: none;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .employment-details.active {
            display: block;
        }

        .row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .col {
            flex: 1;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-weight: 500;
            /* Hardware acceleration */
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #004494;
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #d19a0e;
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

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }

        .strength-meter {
            height: 5px;
            background: #ddd;
            border-radius: 3px;
            margin: 0.5rem 0;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0;
        }

        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }

        .requirements {
            list-style: none;
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: #666;
        }

        .requirements li {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .requirements li i {
            font-size: 0.8rem;
        }

        .requirement-met {
            color: var(--success);
        }

        .requirement-met i {
            color: var(--success);
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
            
            .row {
                flex-direction: column;
            }
            
            .register-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="form-header">
                <h1>SLSU-HC Alumni Registration</h1>
                <p>Join our alumni community and stay connected!</p>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['alumni_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['alumni_success']); ?>
                </div>
                <?php unset($_SESSION['alumni_success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['alumni_error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($_SESSION['alumni_error']); ?>
                </div>
                <?php unset($_SESSION['alumni_error']); ?>
            <?php endif; ?>

            <div class="role-selector">
                <h2>Select Your Role</h2>
                <div class="role-options">
                    <div class="role-option" onclick="selectRole('alumni')">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Alumni</h3>
                        <p>Register as a graduate</p>
                    </div>
                    <div class="role-option" onclick="selectRole('admin')">
                        <i class="fas fa-user-shield"></i>
                        <h3>Admin</h3>
                        <p>Register as administrator</p>
                    </div>
                </div>
            </div>

            <!-- Alumni Form -->
            <form id="alumniForm" class="form-section hidden" action="process_registration.php" method="POST" onsubmit="return validateAlumniForm()">
                <input type="hidden" name="role" value="alumni">
                
                <div class="section-title">
                    <i class="fas fa-user-graduate"></i>
                    Alumni Registration
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniIdInput"><i class="fas fa-id-card"></i> Alumni ID</label>
                            <input type="text" name="alumni_id" id="alumniIdInput" class="form-control" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniFullName"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="fullname" id="alumniFullName" class="form-control" required readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniEmail"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" id="alumniEmail" class="form-control" required readonly>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniPhone"><i class="fas fa-phone"></i> Phone</label>
                            <input type="tel" name="phone" id="alumniPhone" class="form-control" required readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alumniAddress"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <input type="text" name="address" id="alumniAddress" class="form-control" required readonly>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniPassword"><i class="fas fa-lock"></i> Password</label>
                            <div class="password-field">
                                <input type="password" name="password" id="alumniPassword" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('alumniPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="strength-meter">
                                <div id="alumniStrengthMeter" class="strength-bar"></div>
                            </div>
                            <ul class="requirements">
                                <li id="alumniLength"><i class="fas fa-circle"></i> At least 8 characters</li>
                                <li id="alumniUppercase"><i class="fas fa-circle"></i> Uppercase letter</li>
                                <li id="alumniLowercase"><i class="fas fa-circle"></i> Lowercase letter</li>
                                <li id="alumniNumber"><i class="fas fa-circle"></i> Numbers</li>
                                <li id="alumniSpecial"><i class="fas fa-circle"></i> Special characters</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                            <div class="password-field">
                                <input type="password" name="confirm_password" id="alumniConfirmPassword" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('alumniConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="alumniProgram"><i class="fas fa-book"></i> Program</label>
                            <input type="text" name="program" id="alumniProgram" class="form-control" required readonly>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="yearGraduated"><i class="fas fa-calendar"></i> Year Graduated</label>
                            <input type="number" name="year_graduated" id="yearGraduated" class="form-control" required min="1990" max="2030">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="employmentStatus"><i class="fas fa-user-tie"></i> Employment Status</label>
                    <select name="employment_status" id="employmentStatus" class="form-control" onchange="showEmploymentDetails()" required>
                        <option value="">Select employment status</option>
                        <option value="unemployed">Unemployed</option>
                        <option value="employed">Employed</option>
                        <option value="self-employed">Self-Employed</option>
                        <option value="studying">Currently Studying</option>
                    </select>
                </div>

                <div id="employedDetails" class="employment-details">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="companyName"><i class="fas fa-building"></i> Company Name</label>
                                <input type="text" name="company_name" id="companyName" class="form-control">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="position"><i class="fas fa-user-tie"></i> Position</label>
                                <input type="text" name="position" id="position" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="selfEmployedDetails" class="employment-details">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="businessName"><i class="fas fa-store"></i> Business Name</label>
                                <input type="text" name="business_name" id="businessName" class="form-control">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="businessLocation"><i class="fas fa-map-marker-alt"></i> Business Location</label>
                                <input type="text" name="business_location" id="businessLocation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="studyingDetails" class="employment-details">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="studyLevel"><i class="fas fa-graduation-cap"></i> Level of Study</label>
                                <select name="study_level" id="studyLevel" class="form-control">
                                    <option value="">Select level</option>
                                    <option value="masteral">Master's Degree</option>
                                    <option value="doctorate">Doctorate</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="studyType"><i class="fas fa-book"></i> Type of Study</label>
                                <input type="text" name="study_type" id="studyType" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Payment Method (Optional)</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="payment_method" value="gcash">
                            <i class="fab fa-cc-visa"></i> GCash
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="payment_method" value="cash">
                            <i class="fas fa-money-bill-wave"></i> Cash on Hand
                        </label>
                    </div>
                    <small style="color: #666;">You may pay later at the SLSU-HC office or via GCash.</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Register as Alumni
                </button>
            </form>

            <!-- Admin Form -->
            <form id="adminForm" class="form-section hidden" action="process_registration.php" method="POST" onsubmit="return validateAdminForm()">
                <input type="hidden" name="role" value="admin">
                
                <div class="section-title">
                    <i class="fas fa-user-shield"></i>
                    Admin Registration
                </div>

                <div id="adminAccess">
                    <div class="form-group">
                        <label for="adminAccessCode"><i class="fas fa-key"></i> Admin Access Code</label>
                        <input type="password" id="adminAccessCode" class="form-control" placeholder="Enter admin access code">
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="verifyAdminAccess()">
                        <i class="fas fa-unlock"></i> Verify Access
                    </button>
                </div>

                <div id="adminFormFields" class="hidden">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="adminName"><i class="fas fa-user"></i> Name</label>
                                <input type="text" name="username" id="adminName" class="form-control" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="adminEmail"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" id="adminEmail" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="adminPassword"><i class="fas fa-lock"></i> Password</label>
                                <div class="password-field">
                                    <input type="password" name="password" id="adminPassword" class="form-control" required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('adminPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div id="strengthMeter" class="strength-bar"></div>
                                </div>
                                <ul class="requirements">
                                    <li id="length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                    <li id="uppercase"><i class="fas fa-circle"></i> Uppercase letter</li>
                                    <li id="lowercase"><i class="fas fa-circle"></i> Lowercase letter</li>
                                    <li id="number"><i class="fas fa-circle"></i> Numbers</li>
                                    <li id="special"><i class="fas fa-circle"></i> Special characters</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="adminConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                                <div class="password-field">
                                    <input type="password" name="confirm_password" id="adminConfirmPassword" class="form-control" required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('adminConfirmPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-user-shield"></i> Register as Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cache DOM elements for better performance
        const elements = {
            alumniForm: document.getElementById('alumniForm'),
            adminForm: document.getElementById('adminForm'),
            adminAccess: document.getElementById('adminAccess'),
            adminFormFields: document.getElementById('adminFormFields'),
            roleOptions: document.querySelectorAll('.role-option'),
            employmentDetails: {
                employed: document.getElementById('employedDetails'),
                selfEmployed: document.getElementById('selfEmployedDetails'),
                studying: document.getElementById('studyingDetails')
            }
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

        function selectRole(role) {
            // Remove selected class from all options
            elements.roleOptions.forEach(option => option.classList.remove('selected'));
            
            // Add selected class to clicked option
            event.target.closest('.role-option').classList.add('selected');
            
            // Hide all forms
            elements.alumniForm.classList.add('hidden');
            elements.adminForm.classList.add('hidden');
            
            // Show selected form
            if (role === 'alumni') {
                elements.alumniForm.classList.remove('hidden');
            } else if (role === 'admin') {
                elements.adminForm.classList.remove('hidden');
            }
        }

        function showEmploymentDetails() {
            const status = document.getElementById('employmentStatus').value;
            
            // Hide all employment details
            Object.values(elements.employmentDetails).forEach(detail => {
                if (detail) detail.classList.remove('active');
            });
            
            // Show relevant details
            if (status === 'employed' && elements.employmentDetails.employed) {
                elements.employmentDetails.employed.classList.add('active');
            } else if (status === 'self-employed' && elements.employmentDetails.selfEmployed) {
                elements.employmentDetails.selfEmployed.classList.add('active');
            } else if (status === 'studying' && elements.employmentDetails.studying) {
                elements.employmentDetails.studying.classList.add('active');
            }
        }

        function validateAlumniForm() {
            const password = document.getElementById('alumniPassword').value;
            const confirmPassword = document.getElementById('alumniConfirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            // Check password requirements
            const requirements = {
                length: document.getElementById('alumniLength'),
                uppercase: document.getElementById('alumniUppercase'),
                lowercase: document.getElementById('alumniLowercase'),
                number: document.getElementById('alumniNumber'),
                special: document.getElementById('alumniSpecial')
            };
            
            const allRequirementsMet = Object.values(requirements).every(req => 
                req.classList.contains('requirement-met')
            );
            
            if (!allRequirementsMet) {
                alert('Please meet all password requirements!');
                return false;
            }
            
            return true;
        }

        function validateAdminForm() {
            const password = document.getElementById('adminPassword').value;
            const confirmPassword = document.getElementById('adminConfirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            // Check password requirements
            const requirements = {
                length: document.getElementById('length'),
                uppercase: document.getElementById('uppercase'),
                lowercase: document.getElementById('lowercase'),
                number: document.getElementById('number'),
                special: document.getElementById('special')
            };
            
            const allRequirementsMet = Object.values(requirements).every(req => 
                req.classList.contains('requirement-met')
            );
            
            if (!allRequirementsMet) {
                alert('Please meet all password requirements!');
                return false;
            }
            
            return true;
        }

        // Optimized password strength checker with debouncing
        function checkPasswordStrength(password, strengthMeter, requirements) {
            let strength = 0;
            
            // Check length
            if (password.length >= 8) {
                requirements.length.classList.add('requirement-met');
                requirements.length.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.length.classList.remove('requirement-met');
                requirements.length.querySelector('i').className = 'fas fa-circle';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                requirements.uppercase.classList.add('requirement-met');
                requirements.uppercase.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.uppercase.classList.remove('requirement-met');
                requirements.uppercase.querySelector('i').className = 'fas fa-circle';
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                requirements.lowercase.classList.add('requirement-met');
                requirements.lowercase.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.lowercase.classList.remove('requirement-met');
                requirements.lowercase.querySelector('i').className = 'fas fa-circle';
            }
            
            // Check number
            if (/[0-9]/.test(password)) {
                requirements.number.classList.add('requirement-met');
                requirements.number.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.number.classList.remove('requirement-met');
                requirements.number.querySelector('i').className = 'fas fa-circle';
            }
            
            // Check special character
            if (/[^A-Za-z0-9]/.test(password)) {
                requirements.special.classList.add('requirement-met');
                requirements.special.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.special.classList.remove('requirement-met');
                requirements.special.querySelector('i').className = 'fas fa-circle';
            }
            
            // Update strength meter
            strengthMeter.style.width = strength + '%';
            strengthMeter.className = 'strength-bar ' + (strength <= 40 ? 'strength-weak' : strength <= 80 ? 'strength-medium' : 'strength-strong');
        }

        // Debounced password strength checker
        const debouncedPasswordCheck = debounce(checkPasswordStrength, 100);

        function verifyAdminAccess() {
            const accessCode = document.getElementById('adminAccessCode').value;
            const validCode = 'SLSU-HC_ADMIN_2025';
            
            if (accessCode === validCode) {
                elements.adminAccess.classList.add('hidden');
                elements.adminFormFields.classList.remove('hidden');
            } else {
                alert('Invalid admin access code. Please try again.');
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Alumni info autofill with debouncing
            const alumniIdInput = document.getElementById('alumniIdInput');
            if (alumniIdInput) {
                const debouncedFetch = debounce(function(alumniId) {
                    if (alumniId.length > 0) {
                        fetch('fetch_alumni_info.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'alumni_id=' + encodeURIComponent(alumniId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('alumniFullName').value = data.fullname;
                                document.getElementById('alumniEmail').value = data.email;
                                document.getElementById('alumniPhone').value = data.phone;
                                document.getElementById('alumniAddress').value = data.address;
                                document.getElementById('alumniProgram').value = data.program;
                            } else {
                                ['alumniFullName', 'alumniEmail', 'alumniPhone', 'alumniAddress', 'alumniProgram'].forEach(id => {
                                    document.getElementById(id).value = '';
                                });
                                alert(data.message);
                            }
                        })
                        .catch(() => {
                            ['alumniFullName', 'alumniEmail', 'alumniPhone', 'alumniAddress', 'alumniProgram'].forEach(id => {
                                document.getElementById(id).value = '';
                            });
                            alert('Error fetching alumni info.');
                        });
                    }
                }, 300);

                alumniIdInput.addEventListener('blur', function() {
                    debouncedFetch(this.value.trim());
                });
            }

            // Password strength checkers with debouncing
            const adminPasswordInput = document.getElementById('adminPassword');
            if (adminPasswordInput) {
                adminPasswordInput.addEventListener('input', function() {
                    debouncedPasswordCheck(this.value, document.getElementById('strengthMeter'), {
                        length: document.getElementById('length'),
                        uppercase: document.getElementById('uppercase'),
                        lowercase: document.getElementById('lowercase'),
                        number: document.getElementById('number'),
                        special: document.getElementById('special')
                    });
                });
            }

            const alumniPasswordInput = document.getElementById('alumniPassword');
            if (alumniPasswordInput) {
                alumniPasswordInput.addEventListener('input', function() {
                    debouncedPasswordCheck(this.value, document.getElementById('alumniStrengthMeter'), {
                        length: document.getElementById('alumniLength'),
                        uppercase: document.getElementById('alumniUppercase'),
                        lowercase: document.getElementById('alumniLowercase'),
                        number: document.getElementById('alumniNumber'),
                        special: document.getElementById('alumniSpecial')
                    });
                });
            }
        });
    </script>
</body>
</html> 