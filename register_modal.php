<?php
session_start();
require_once 'admin/config/database.php'; // Adjust path if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU Alumni Tracer - Registration</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            min-height: 100vh;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.7);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .modal-overlay.show .modal-container {
            transform: scale(1);
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }
        
        .modal-close:hover {
            background: #e2e8f0;
            transform: scale(1.1);
        }
        
        .floating-label {
            position: relative;
        }
        .floating-label input, .floating-label select {
            padding-top: 1.5rem;
        }
        .floating-label label {
            position: absolute;
            top: 0.5rem;
            left: 1rem;
            font-size: 0.95rem;
            color: #64748b;
            pointer-events: none;
            transition: all 0.2s;
        }
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label,
        .floating-label select:focus + label,
        .floating-label select:not([value=""]) + label {
            top: -0.7rem;
            left: 0.75rem;
            font-size: 0.8rem;
            color: #2563eb;
            background: #fff;
            padding: 0 0.25rem;
            border-radius: 0.25rem;
        }
        .tab-btn.active {
            background: #2563eb;
            color: #fff;
        }
        .tab-btn {
            transition: background 0.2s, color 0.2s;
        }
        .form-card {
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
            border-radius: 1.5rem;
            background: #fff;
        }
        .fade-in {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
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
        
        /* Responsive adjustments for modal */
        @media (max-width: 768px) {
            .modal-container {
                max-width: 95vw;
                max-height: 95vh;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Modal Overlay -->
    <div class="modal-overlay show" id="registrationModal">
        <div class="modal-container p-6 md:p-10">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times text-gray-600"></i>
            </button>
            
            <div class="text-center mb-8">
                <img src="images/slsu_logo.png" alt="SLSU Logo" class="mx-auto mb-2 w-16 h-16">
                <h1 class="text-3xl font-bold text-blue-700 mb-1">SLSU-HC Alumni Registration</h1>
                <p class="text-gray-500">Join our alumni community and stay connected!</p>
            </div>
            
            <div class="flex justify-center gap-4 mb-8">
                <button class="tab-btn px-6 py-2 rounded-full font-semibold shadow-sm border border-blue-200 active" id="alumniTab" onclick="showTab('alumni')">
                    <i class="fas fa-user-graduate mr-2"></i> Alumni
                </button>
                <button class="tab-btn px-6 py-2 rounded-full font-semibold shadow-sm border border-blue-200" id="adminTab" onclick="showTab('admin')">
                    <i class="fas fa-user-shield mr-2"></i> Admin
                </button>
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
            
            <!-- Alumni Form -->
            <form id="alumniForm" class="fade-in" action="process_registration.php" method="POST" onsubmit="return validateAlumniForm(this)">
                <input type="hidden" name="role" value="alumni">
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="text" name="alumni_id" id="alumniIdInput" class="form-control" placeholder=" " required autocomplete="off">
                        <label for="alumniIdInput"><i class="fas fa-id-card"></i> Alumni ID</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="text" name="fullname" id="alumniFullName" class="form-control" placeholder=" " required readonly>
                        <label for="alumniFullName"><i class="fas fa-user"></i> Full Name</label>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="email" name="email" id="alumniEmail" class="form-control" placeholder=" " required readonly>
                        <label for="alumniEmail"><i class="fas fa-envelope"></i> Email</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="tel" name="phone" id="alumniPhone" class="form-control" placeholder=" " required readonly>
                        <label for="alumniPhone"><i class="fas fa-phone"></i> Phone</label>
                    </div>
                </div>
                <div class="mb-3 floating-label">
                    <input type="text" name="address" id="alumniAddress" class="form-control" placeholder=" " required readonly>
                    <label for="alumniAddress"><i class="fas fa-map-marker-alt"></i> Address</label>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="password" name="password" id="alumniPassword" class="form-control" placeholder=" " required autocomplete="new-password">
                        <label for="alumniPassword"><i class="fas fa-lock"></i> Password</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="password" name="confirm_password" id="alumniConfirmPassword" class="form-control" placeholder=" " required>
                        <label for="alumniConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-shield-alt text-blue-500"></i>
                        <span class="font-semibold text-gray-700">Password Strength</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div id="alumniStrengthMeter" class="progress-bar bg-success" style="width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <ul class="list-unstyled mt-2 text-sm text-gray-500">
                        <li id="alumniLength"><i class="fas fa-circle"></i> At least 8 characters</li>
                        <li id="alumniUppercase"><i class="fas fa-circle"></i> Uppercase letter</li>
                        <li id="alumniLowercase"><i class="fas fa-circle"></i> Lowercase letter</li>
                        <li id="alumniNumber"><i class="fas fa-circle"></i> Numbers</li>
                        <li id="alumniSpecial"><i class="fas fa-circle"></i> Special characters</li>
                    </ul>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="text" name="program" id="alumniProgram" class="form-control" placeholder=" " required readonly>
                        <label for="alumniProgram"><i class="fas fa-book"></i> Program</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="number" name="year_graduated" class="form-control" placeholder=" " required>
                        <label><i class="fas fa-calendar"></i> Year Graduated</label>
                    </div>
                </div>
                <div class="mb-3 floating-label">
                    <select name="employment_status" class="form-select" onchange="showEmploymentDetails(this.value)" required>
                        <option value="" selected disabled></option>
                        <option value="unemployed">Unemployed</option>
                        <option value="employed">Employed</option>
                        <option value="self-employed">Self-Employed</option>
                        <option value="studying">Currently Studying</option>
                    </select>
                    <label><i class="fas fa-user-tie"></i> Employment Status</label>
                </div>
                <div id="employedDetails" class="row g-3 mb-3 hidden">
                    <div class="col-md-6 floating-label">
                        <input type="text" name="company_name" class="form-control" placeholder=" ">
                        <label><i class="fas fa-building"></i> Company Name</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="text" name="position" class="form-control" placeholder=" ">
                        <label><i class="fas fa-user-tie"></i> Position</label>
                    </div>
                </div>
                <div id="selfEmployedDetails" class="row g-3 mb-3 hidden">
                    <div class="col-md-6 floating-label">
                        <input type="text" name="business_name" class="form-control" placeholder=" ">
                        <label><i class="fas fa-store"></i> Business Name</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="text" name="business_location" class="form-control" placeholder=" ">
                        <label><i class="fas fa-map-marker-alt"></i> Business Location</label>
                    </div>
                </div>
                <div id="studyingDetails" class="row g-3 mb-3 hidden">
                    <div class="col-md-6 floating-label">
                        <select name="study_level" class="form-select">
                            <option value="" selected disabled></option>
                            <option value="masteral">Master's Degree</option>
                            <option value="doctorate">Doctorate</option>
                        </select>
                        <label><i class="fas fa-graduation-cap"></i> Level of Study</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="text" name="study_type" class="form-control" placeholder=" ">
                        <label><i class="fas fa-book"></i> Type of Study</label>
                    </div>
                </div>
                <!-- Payment Section (visible, not required) -->
                <div class="mb-4 p-4 rounded-lg border border-blue-100 bg-blue-50">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-credit-card text-blue-500 text-xl"></i>
                        <span class="font-semibold text-blue-700">Payment Method (Optional)</span>
                    </div>
                    <div class="flex gap-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                            <label class="form-check-label" for="gcash">
                                <i class="fab fa-cc-visa"></i> GCash
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                            <label class="form-check-label" for="cash">
                                <i class="fas fa-money-bill-wave"></i> Cash on Hand
                            </label>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">You may pay later at the SLSU-HC office or via GCash. This is not required to complete registration.</div>
                </div>
                <button type="submit" class="btn btn-primary w-full py-2 text-lg mt-2 shadow-sm">
                    <i class="fas fa-user-plus mr-2"></i> Register as Alumni
                </button>
            </form>
            
            <!-- Admin Form (hidden by default) -->
            <form id="adminForm" class="fade-in hidden" action="process_registration.php" method="POST" onsubmit="return validateAdminForm(this)">
                <input type="hidden" name="role" value="admin">
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="text" name="username" class="form-control" placeholder=" " required>
                        <label><i class="fas fa-user"></i> Name</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="email" name="email" class="form-control" placeholder=" " required>
                        <label><i class="fas fa-envelope"></i> Email</label>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6 floating-label">
                        <input type="password" name="password" id="adminPassword" class="form-control" placeholder=" " required autocomplete="new-password">
                        <label for="adminPassword"><i class="fas fa-lock"></i> Password</label>
                    </div>
                    <div class="col-md-6 floating-label">
                        <input type="password" name="confirm_password" id="adminConfirmPassword" class="form-control" placeholder=" " required>
                        <label for="adminConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-shield-alt text-blue-500"></i>
                        <span class="font-semibold text-gray-700">Password Strength</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div id="adminStrengthMeter" class="progress-bar bg-success" style="width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <ul class="list-unstyled mt-2 text-sm text-gray-500">
                        <li id="length"><i class="fas fa-circle"></i> At least 8 characters</li>
                        <li id="uppercase"><i class="fas fa-circle"></i> Uppercase letter</li>
                        <li id="lowercase"><i class="fas fa-circle"></i> Lowercase letter</li>
                        <li id="number"><i class="fas fa-circle"></i> Numbers</li>
                        <li id="special"><i class="fas fa-circle"></i> Special characters</li>
                    </ul>
                </div>
                <button type="submit" class="btn btn-primary w-full py-2 text-lg mt-2 shadow-sm">
                    <i class="fas fa-user-shield mr-2"></i> Register as Admin
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS (for some components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal functions
        function openModal() {
            document.getElementById('registrationModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('registrationModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        document.getElementById('registrationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Tab switching
        function showTab(role) {
            document.getElementById('alumniTab').classList.remove('active');
            document.getElementById('adminTab').classList.remove('active');
            document.getElementById('alumniForm').classList.add('hidden');
            document.getElementById('adminForm').classList.add('hidden');
            if (role === 'alumni') {
                document.getElementById('alumniTab').classList.add('active');
                document.getElementById('alumniForm').classList.remove('hidden');
            } else {
                document.getElementById('adminTab').classList.add('active');
                document.getElementById('adminForm').classList.remove('hidden');
            }
        }
        
        // Employment details toggle
        function showEmploymentDetails(status) {
            document.getElementById('employedDetails').classList.add('hidden');
            document.getElementById('selfEmployedDetails').classList.add('hidden');
            document.getElementById('studyingDetails').classList.add('hidden');
            if (status === 'employed') {
                document.getElementById('employedDetails').classList.remove('hidden');
            } else if (status === 'self-employed') {
                document.getElementById('selfEmployedDetails').classList.remove('hidden');
            } else if (status === 'studying') {
                document.getElementById('studyingDetails').classList.remove('hidden');
            }
        }
        
        // Password strength checkers
        function checkPasswordStrength(password, strengthMeter, requirements) {
            let strength = 0;
            if (password.length >= 8) {
                requirements.length.classList.add('text-success');
                requirements.length.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.length.classList.remove('text-success');
                requirements.length.querySelector('i').className = 'fas fa-circle';
            }
            if (/[A-Z]/.test(password)) {
                requirements.uppercase.classList.add('text-success');
                requirements.uppercase.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.uppercase.classList.remove('text-success');
                requirements.uppercase.querySelector('i').className = 'fas fa-circle';
            }
            if (/[a-z]/.test(password)) {
                requirements.lowercase.classList.add('text-success');
                requirements.lowercase.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.lowercase.classList.remove('text-success');
                requirements.lowercase.querySelector('i').className = 'fas fa-circle';
            }
            if (/[0-9]/.test(password)) {
                requirements.number.classList.add('text-success');
                requirements.number.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.number.classList.remove('text-success');
                requirements.number.querySelector('i').className = 'fas fa-circle';
            }
            if (/[^A-Za-z0-9]/.test(password)) {
                requirements.special.classList.add('text-success');
                requirements.special.querySelector('i').className = 'fas fa-check-circle';
                strength += 20;
            } else {
                requirements.special.classList.remove('text-success');
                requirements.special.querySelector('i').className = 'fas fa-circle';
            }
            strengthMeter.style.width = strength + '%';
        }
        
        // Alumni password
        const alumniPasswordInput = document.getElementById('alumniPassword');
        const alumniStrengthMeter = document.getElementById('alumniStrengthMeter');
        const alumniRequirements = {
            length: document.getElementById('alumniLength'),
            uppercase: document.getElementById('alumniUppercase'),
            lowercase: document.getElementById('alumniLowercase'),
            number: document.getElementById('alumniNumber'),
            special: document.getElementById('alumniSpecial')
        };
        if (alumniPasswordInput) {
            alumniPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value, alumniStrengthMeter, alumniRequirements);
            });
        }
        
        // Admin password
        const adminPasswordInput = document.getElementById('adminPassword');
        const adminStrengthMeter = document.getElementById('adminStrengthMeter');
        const adminRequirements = {
            length: document.getElementById('length'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            number: document.getElementById('number'),
            special: document.getElementById('special')
        };
        if (adminPasswordInput) {
            adminPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value, adminStrengthMeter, adminRequirements);
            });
        }
        
        // Alumni info autofill
        document.addEventListener('DOMContentLoaded', function() {
            const alumniIdInput = document.getElementById('alumniIdInput');
            const fullNameInput = document.getElementById('alumniFullName');
            const emailInput = document.getElementById('alumniEmail');
            const phoneInput = document.getElementById('alumniPhone');
            const addressInput = document.getElementById('alumniAddress');
            const programInput = document.getElementById('alumniProgram');
            
            if (alumniIdInput) {
                alumniIdInput.addEventListener('blur', function() {
                    const alumniId = alumniIdInput.value.trim();
                    if (alumniId.length > 0) {
                        fetch('fetch_alumni_info.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'alumni_id=' + encodeURIComponent(alumniId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fullNameInput.value = data.fullname;
                                emailInput.value = data.email;
                                phoneInput.value = data.phone;
                                addressInput.value = data.address;
                                programInput.value = data.program;
                            } else {
                                fullNameInput.value = '';
                                emailInput.value = '';
                                phoneInput.value = '';
                                addressInput.value = '';
                                programInput.value = '';
                                alert(data.message);
                            }
                        })
                        .catch(() => {
                            fullNameInput.value = '';
                            emailInput.value = '';
                            phoneInput.value = '';
                            addressInput.value = '';
                            programInput.value = '';
                            alert('Error fetching alumni info.');
                        });
                    }
                });
            }
        });
        
        // Validation functions
        function validateAlumniForm(form) {
            const password = form.querySelector('input[name="password"]').value;
            const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            // Password requirements
            const requirements = alumniRequirements;
            const allRequirementsMet = Object.values(requirements).every(req => req.classList.contains('text-success'));
            if (!allRequirementsMet) {
                alert('Please meet all password requirements!');
                return false;
            }
            return true;
        }
        
        function validateAdminForm(form) {
            const password = form.querySelector('input[name="password"]').value;
            const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            // Password requirements
            const requirements = adminRequirements;
            const allRequirementsMet = Object.values(requirements).every(req => req.classList.contains('text-success'));
            if (!allRequirementsMet) {
                alert('Please meet all password requirements!');
                return false;
            }
            return true;
        }
        
        // Make functions globally available
        window.openRegistrationModal = openModal;
        window.closeRegistrationModal = closeModal;
    </script>
</body>
</html> 