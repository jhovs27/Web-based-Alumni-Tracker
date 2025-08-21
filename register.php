<?php
// Enhanced error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'admin/config/database.php';

// Debug: Log POST data for troubleshooting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST DATA: " . print_r($_POST, true));
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_role = trim($_POST['user_role'] ?? '');
    
    if ($user_role === 'alumni') {
        try {
            // Personal Information
            $input_alumni_id = trim($_POST['alumni_id'] ?? '');
            
            // Debug logging for alumni_id
            error_log("Registration Debug - Raw Alumni ID: " . $input_alumni_id);
            
            // Accept any alumni_id format, just check if it exists in the database
            if (empty($input_alumni_id)) {
                $error_message = "Alumni ID is required.";
            } else {
                // Verify alumni_id exists in alumni_ids table and fetch the complete record
                $check_alumni_id = $conn->prepare("SELECT * FROM alumni_ids WHERE alumni_id = ?");
                $check_alumni_id->execute([$input_alumni_id]);
                if ($check_alumni_id->rowCount() === 0) {
                    $error_message = "We’re sorry, but the Alumni ID number you entered does not exist in our records. Please double-check the ID and try again.";
                } else {
                    // Fetch the complete alumni record from alumni_ids table
                    $alumni_record = $check_alumni_id->fetch(PDO::FETCH_ASSOC);
                    $alumni_id = $alumni_record['alumni_id']; // Use the complete alumni_id from database
                    $student_no = $alumni_record['student_no']; // Use student_no from alumni_ids table
                    
                    error_log("Registration Debug - Found Alumni ID in database: " . $alumni_id);
                    error_log("Registration Debug - Student No from database: " . $student_no);
                }
            }
            
            // Use hidden fields for backend processing (only if alumni_id validation passed)
            if (empty($error_message)) {
                $last_name = trim($_POST['hidden_last_name'] ?? $_POST['last_name'] ?? '');
                $first_name = trim($_POST['hidden_first_name'] ?? $_POST['first_name'] ?? '');
                $middle_name = trim($_POST['hidden_middle_name'] ?? $_POST['middle_name'] ?? '');
                $course = trim($_POST['hidden_course'] ?? '');
                $email = trim($_POST['hidden_email'] ?? $_POST['email'] ?? '');
                $address = trim($_POST['hidden_address'] ?? '');
                $civil_status = trim($_POST['civil_status'] ?? '');
                $password = $_POST['alumni_password'] ?? '';
                $confirm_password = $_POST['alumni_confirm_password'] ?? '';
                
                // Employment Information
                $employment_status = $_POST['employment_status'] ?? '';
                
                // Payment Information
                $payment_option = $_POST['payment_method'] ?? '';
                
                // Debug logging
                error_log("Registration Debug - Alumni ID: " . $alumni_id);
                error_log("Registration Debug - Payment Option: " . $payment_option);
                error_log("Registration Debug - POST Data: " . print_r($_POST, true));
                
                // Basic validation
                if (empty($alumni_id) || empty($student_no) || empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($civil_status) || empty($employment_status) || empty($payment_option)) {
                    $error_message = "All required fields must be filled.";
                } elseif ($password !== $confirm_password) {
                    $error_message = "Passwords do not match.";
                } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                    $error_message = "Password must be at least 8 characters long and contain at least one uppercase letter, one number, and one special character.";
                } else {
                    // Check if student number already exists
                    $check_student = $conn->prepare("SELECT alumni_id FROM alumni WHERE student_no = ?");
                    $check_student->execute([$student_no]);
                    
                    if ($check_student->rowCount() > 0) {
                        $error_message = "This student number is already registered.";
                    } else {
                        // Check if email already exists
                        $check_email = $conn->prepare("SELECT alumni_id FROM alumni WHERE email = ?");
                        $check_email->execute([$email]);
                        
                        if ($check_email->rowCount() > 0) {
                            $error_message = "This email is already registered.";
                        } else {
                            // Check which password field exists in the database
                            $check_password_hash = $conn->query("SHOW COLUMNS FROM alumni LIKE 'password_hash'");
                            $has_password_hash = $check_password_hash->rowCount() > 0;
                            
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Debug password hashing
                            error_log("Registration Debug - Password: " . substr($password, 0, 3) . "***");
                            error_log("Registration Debug - Password Hash: " . substr($password_hash, 0, 20) . "...");
                            error_log("Registration Debug - Hash Length: " . strlen($password_hash));
                            error_log("Registration Debug - Using field: " . ($has_password_hash ? "password_hash" : "password"));
                            
                            // Create fullname from first and last name for display
                            $fullname = ucwords(strtolower(trim($first_name . ' ' . $last_name)));
                            
                            // Debug the values before insertion
                            error_log("Registration Debug - About to insert:");
                            error_log("Alumni ID: " . $alumni_id);
                            error_log("Payment Option: " . $payment_option);
                            error_log("Student No: " . $student_no);
                            error_log("Email: " . $email);
                            
                            // Insert into the alumni table with all information
                            if ($has_password_hash) {
                                $sql = "INSERT INTO alumni (
                                    alumni_id, student_no, last_name, first_name, middle_name, course, email, address, 
                                    civil_status, employment_status, payment_option, password_hash
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                                )";
                                
                                $stmt = $conn->prepare($sql);
                                $result = $stmt->execute([
                                    $alumni_id, $student_no, $last_name, $first_name, $middle_name, $course, $email, $address,
                                    $civil_status, $employment_status, $payment_option, $password_hash
                                ]);
                            } else {
                                $sql = "INSERT INTO alumni (
                                    alumni_id, student_no, last_name, first_name, middle_name, course, email, address, 
                                    civil_status, employment_status, payment_option, password
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                                )";
                                
                                $stmt = $conn->prepare($sql);
                                $result = $stmt->execute([
                                    $alumni_id, $student_no, $last_name, $first_name, $middle_name, $course, $email, $address,
                                    $civil_status, $employment_status, $payment_option, $password_hash
                                ]);
                            }
                            
                            if ($result) {
                                // Get the inserted alumni ID
                                $inserted_id = $conn->lastInsertId();
                                $success_message = "{$fullname} has been registered successfully!";
                                error_log("Alumni Registration Success: Alumni ID {$alumni_id} for {$fullname}");
                                error_log("Alumni Registration Success: Payment Option {$payment_option}");
                                error_log("Alumni Registration Success: Inserted ID {$inserted_id}");

                                // Prepare employment data
                                $employment_data = [
                                    'alumni_id' => $alumni_id,
                                    'employment_status' => strtolower($employment_status), // match enum values
                                    'employee_id_number' => null,
                                    'company_name' => null,
                                    'company_address' => null,
                                    'business_name' => null,
                                    'business_location' => null,
                                    'school_name' => null,
                                    'level_of_study' => null,
                                    'type_of_study' => null,
                                    'proof_document_path' => null
                                ];

                                if ($employment_status === 'Employed') {
                                    $employment_data['employee_id_number'] = trim($_POST['employee_id'] ?? '');
                                    $employment_data['company_name'] = trim($_POST['company_name'] ?? '');
                                    $employment_data['company_address'] = trim($_POST['company_address'] ?? '');
                                } elseif ($employment_status === 'Self-employed') {
                                    $employment_data['business_name'] = trim($_POST['business_name'] ?? '');
                                    $employment_data['business_location'] = trim($_POST['business_location'] ?? '');
                                } elseif ($employment_status === 'Further Studying') {
                                    $employment_data['school_name'] = trim($_POST['school_name'] ?? '');
                                    $employment_data['level_of_study'] = trim($_POST['study_level'] ?? '');
                                    $employment_data['type_of_study'] = trim($_POST['study_type'] ?? '');
                                }

                                // Only insert if status is not Unemployed
                                if (in_array($employment_status, ['Employed', 'Self-employed', 'Further Studying'])) {
                                    $sql_emp = "INSERT INTO employment (
                                        alumni_id, employment_status, employee_id_number, company_name, company_address,
                                        business_name, business_location, school_name, level_of_study, type_of_study, proof_document_path
                                    ) VALUES (
                                        :alumni_id, :employment_status, :employee_id_number, :company_name, :company_address,
                                        :business_name, :business_location, :school_name, :level_of_study, :type_of_study, :proof_document_path
                                    )";
                                    $stmt_emp = $conn->prepare($sql_emp);
                                    $stmt_emp->execute($employment_data);
                                }

                                // Insert into payment table
                                $reference_number = $_POST['reference_number'] ?? null;
                                $proof_document = $_POST['proof_document'] ?? null;
                                
                                // Only insert payment record if payment_option is selected
                                if (!empty($payment_option)) {
                                    $payment_sql = "INSERT INTO payment (
                                        alumni_id, payment_option, reference_number, proof_document, status
                                    ) VALUES (
                                        ?, ?, ?, ?, 'Pending Payment'
                                    )";
                                    
                                    $payment_stmt = $conn->prepare($payment_sql);
                                    $payment_result = $payment_stmt->execute([
                                        $alumni_id, $payment_option, $reference_number, $proof_document
                                    ]);
                                    
                                    if ($payment_result) {
                                        error_log("Payment record created successfully for alumni ID: {$alumni_id} with payment option: {$payment_option}");
                                    } else {
                                        error_log("Failed to create payment record for alumni ID: {$alumni_id}");
                                    }
                                } else {
                                    error_log("No payment option selected for alumni ID: {$alumni_id}");
                                }

                                $_POST = array(); // Clear form data
                            } else {
                                // Detailed error reporting
                                $errorInfo = $stmt->errorInfo();
                                $error_message = "Registration failed. Please try again. Error: " . ($errorInfo[2] ?? 'Unknown error');
                                error_log("Alumni Registration Failed: " . ($errorInfo[2] ?? 'Unknown error'));
                                error_log("SQL Error Info: " . print_r($errorInfo, true));
                                error_log("POST Data: " . print_r($_POST, true));
                            }
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
            error_log("Registration error: " . $e->getMessage());
        }
    } elseif ($user_role === 'administrator') {
        // Handle admin registration (existing code)
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $admin_code = $_POST['role_identifier'] ?? '';
        
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($admin_code)) {
            $error_message = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $error_message = "Password must be at least 8 characters long and contain at least one uppercase letter, one number, and one special character.";
        } else {
            if ($admin_code === 'ADMIN2024') {
                try {
                    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $check_email->execute([$email]);
                    
                    if ($check_email->rowCount() > 0) {
                        $error_message = "Email already exists.";
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        $sql = "INSERT INTO users (first_name, last_name, email, password, user_role, role_identifier, created_at) 
                                VALUES (?, ?, ?, ?, 'administrator', ?, NOW())";
                        
                        $stmt = $conn->prepare($sql);
                        if ($stmt->execute([$first_name, $last_name, $email, $password_hash, $admin_code])) {
                            $success_message = "Administrator registration successful!";
                        } else {
                            $error_message = "Registration failed. Please try again.";
                        }
                    }
                } catch (PDOException $e) {
                    $error_message = "Database error: " . $e->getMessage();
                }
            } else {
                $error_message = "Invalid admin access code.";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message) && empty($success_message)) {
    $error_message = "Unknown error occurred. Please check your input and try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Alumni Tracer System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .home-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            z-index: 1000;
        }
        
        .home-button:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .content {
            padding: 32px 24px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .role-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .role-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-card:hover {
            border-color: #4f46e5;
            transform: translateY(-2px);
        }
        
        .role-card.selected {
            border-color: #4f46e5;
            background: #f0f9ff;
        }
        
        .role-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: white;
        }
        
        .registration-form {
            display: none;
        }
        
        .registration-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }
        
        .btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
        }
        
        .btn-verify {
            background: #f8fafc;
            color: #4f46e5;
            border: 2px solid #4f46e5;
            margin-top: 8px;
        }
        
        .btn-verify:hover {
            background: #4f46e5;
            color: white;
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
        }
        
        .role-fields {
            display: none;
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin-top: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .role-fields.active {
            display: block;
        }
        
        .verification-result {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            display: none;
        }
        
        .verification-result.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            display: block;
        }
        
        .verification-result.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            display: block;
        }
        
        .back-button {
            background: none;
            border: none;
            color: #4f46e5;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 20px;
        }
        
        .student-info-display {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            display: none;
        }
        
        .section-header {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
            border: 1px solid #e2e8f0;
        }
        
        .section-header h3 {
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .employment-fields,
        .payment-fields {
            display: none;
            background: white;
            border-radius: 6px;
            padding: 12px;
            margin-top: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 14px;
        }
        
        .login-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        
        .readonly-field {
            background: #f1f5f9 !important;
            border: 2px solid #cbd5e1 !important;
            color: #334155 !important;
            cursor: not-allowed;
        }
        .readonly-field:focus {
            box-shadow: none;
            border-color: #cbd5e1 !important;
        }
        
        @media (max-width: 768px) {
            .role-selection {
                grid-template-columns: 1fr;
            }
            .form-row, .form-row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-button">
        <i data-lucide="home"></i>
        Home
    </a>
    <div class="container">
        <div class="header">
            <h1>Create Account</h1>
            <p>Join the SLSU-HC Alumni Community</p>
        </div>
        
        <div class="content">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i data-lucide="alert-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <script>
                // Always run after page load if there's a password error
                <?php if ($error_message && preg_match('/Password/', $error_message)): ?>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('role-selection').style.display = 'none';
                        document.getElementById('registration-form').classList.add('active');
                        setTimeout(function() {
                            var alumniPass = document.getElementById('alumni_password');
                            var adminPass = document.getElementById('password');
                            if (alumniPass && alumniPass.offsetParent !== null) alumniPass.focus();
                            else if (adminPass && adminPass.offsetParent !== null) adminPass.focus();
                        }, 100);
                    });
                <?php endif; ?>
            </script>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Role Selection -->
            <div class="role-selection" id="role-selection">
                <div class="role-card" onclick="selectRole('alumni')">
                    <div class="role-icon">
                        <i data-lucide="graduation-cap"></i>
                    </div>
                    <h3>Alumni</h3>
                    <p>Graduate registration</p>
                </div>
                <div class="role-card" onclick="selectRole('administrator')">
                    <div class="role-icon">
                        <i data-lucide="shield-check"></i>
                    </div>
                    <h3>Administrator</h3>
                    <p>Admin registration</p>
                </div>
            </div>
            
            <!-- Registration Form -->
            <div class="registration-form" id="registration-form">
                <button type="button" class="back-button" onclick="showRoleSelection()">
                    <i data-lucide="arrow-left"></i>
                    Back to Role Selection
                </button>
                
                <form method="POST" enctype="multipart/form-data" id="registrationForm">
                    <input type="hidden" id="user_role" name="user_role" value="">
                    
                    <!-- Hidden fields for alumni data -->
                    <input type="hidden" id="hidden_student_no" name="hidden_student_no" value="">
                    <input type="hidden" id="hidden_last_name" name="hidden_last_name" value="">
                    <input type="hidden" id="hidden_first_name" name="hidden_first_name" value="">
                    <input type="hidden" id="hidden_middle_name" name="hidden_middle_name" value="">
                    <input type="hidden" id="hidden_course" name="hidden_course" value="">
                    <input type="hidden" id="hidden_email" name="hidden_email" value="">
                    <input type="hidden" id="hidden_address" name="hidden_address" value="">
                    
                    <!-- Alumni ID Verification -->
                    <div class="form-group role-fields" id="alumni-fields">
                        <label for="alumni_id">Alumni ID Number</label>
                        <input type="text" id="alumni_id" name="alumni_id" placeholder="Enter Alumni ID number">
                        <button type="button" class="btn btn-verify" onclick="verifyAlumniID()">
                            <i data-lucide="search"></i>
                            Verify Alumni ID
                        </button>
                        <div id="verification-result" class="verification-result"></div>
                    </div>
                    
                    <!-- Admin Code -->
                    <div class="form-group role-fields" id="admin-fields">
                        <label for="admin_code">Admin Access Code</label>
                        <div class="password-field">
                            <input type="password" id="admin_code" name="role_identifier" placeholder="Enter Admin Access Code">
                            <button type="button" class="password-toggle" onclick="togglePassword('admin_code')">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Alumni Registration Fields -->
                    <div id="alumni-registration-fields" style="display: none;">
                        <!-- Personal Information -->
                        <div class="section-header">
                            <h3><i data-lucide="user"></i> Personal Information</h3>
                            <p>This information is automatically filled from your records</p>
                        </div>
                        
                        <div class="form-row-3">
                            <div class="form-group">
                                <label for="student_no">Student Number</label>
                                <input type="text" id="student_no" name="student_no_display" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="course">Course</label>
                                <input type="text" id="course" name="course_display" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="civil_status">Civil Status</label>
                                <select id="civil_status" name="civil_status">
                                    <option value="">Select Civil Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Divorced">Divorced</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row-3">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" readonly class="readonly-field">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" readonly class="readonly-field">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="alumni_password">Password</label>
                                <div class="password-field">
                                    <input type="password" id="alumni_password" name="alumni_password" required minlength="8">
                                    <button type="button" class="password-toggle" onclick="togglePassword('alumni_password')">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="alumni_confirm_password">Confirm Password</label>
                                <div class="password-field">
                                    <input type="password" id="alumni_confirm_password" name="alumni_confirm_password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('alumni_confirm_password')">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employment Information -->
                        <div class="section-header">
                            <h3><i data-lucide="briefcase"></i> Employment Information</h3>
                        </div>
                        
                        <div class="form-group">
                            <label for="employment_status">Employment Status</label>
                            <select id="employment_status" name="employment_status" onchange="toggleEmploymentFields()">
                                <option value="">Select Employment Status</option>
                                <option value="Employed">Employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Self-employed">Self-Employed</option>
                                <option value="Further Studying">Further Studying</option>
                            </select>
                        </div>
                        
                        <!-- Employed Fields -->
                        <div id="employed-fields" class="employment-fields">
                            <div class="form-group">
                                <label for="company_name">Company Name</label>
                                <input type="text" id="company_name" name="company_name" placeholder="Enter company name">
                            </div>
                            <div class="form-group">
                                <label for="company_address">Company Address</label>
                                <input type="text" id="company_address" name="company_address" placeholder="Enter company address">
                            </div>
                            <div class="form-group">
                                <label for="employee_id">Employee ID (if applicable)</label>
                                <input type="text" id="employee_id" name="employee_id" placeholder="Enter employee ID">
                            </div>
                        </div>

                        <!-- Unemployed Fields -->
                        <div id="unemployed-fields" class="employment-fields">
                            <!-- No fields for unemployed status -->
                        </div>

                        <!-- Self-Employed Fields -->
                        <div id="self-employed-fields" class="employment-fields">
                            <div class="form-group">
                                <label for="business_name">Business Name</label>
                                <input type="text" id="business_name" name="business_name" placeholder="Enter business name">
                            </div>
                            <div class="form-group">
                                <label for="business_location">Business Location</label>
                                <input type="text" id="business_location" name="business_location" placeholder="Enter business location">
                            </div>
                        </div>

                        <!-- Further Studying Fields -->
                        <div id="further-studying-fields" class="employment-fields">
                            <div class="form-group">
                                <label for="school_name">Name of School</label>
                                <input type="text" id="school_name" name="school_name" placeholder="Enter name of school">
                            </div>
                            <div class="form-group">
                                <label for="study_level">Level of Study</label>
                                <input type="text" id="study_level" name="study_level" placeholder="e.g., Masteral, Doctorate, etc.">
                            </div>
                            <div class="form-group">
                                <label for="study_type">Type of Study</label>
                                <input type="text" id="study_type" name="study_type" placeholder="e.g., Medicine, Law, Engineering, etc.">
                            </div>
                        </div>
                        
                        <!-- Payment Information -->
                        <div class="section-header">
                            <h3><i data-lucide="credit-card"></i> Payment Information</h3>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" onchange="togglePaymentFields()">
                                <option value="">Select Payment Method</option>
                                <option value="GCASH">GCASH</option>
                                <option value="Cash on Hand">Cash on Hand</option>
                            </select>
                        </div>
                        
                        <!-- GCASH Payment Fields -->
                        <div id="gcash-fields" class="payment-fields">
                            <div style="text-align: center; margin-bottom: 16px;">
                                <img src="images/GCash-MyQR-05072025235950.PNG.jpg" alt="GCASH QR Code" style="width: 150px; height: 150px;">
                            </div>
                            <div class="form-group">
                                <label for="gcash_name">GCASH Name</label>
                                <input type="text" id="gcash_name" name="gcash_name" value="Madelyn Eway" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="gcash_number">GCASH Number</label>
                                <input type="text" id="gcash_number" name="gcash_number" value="09708371718" readonly class="readonly-field">
                            </div>
                            <div class="form-group">
                                <label for="reference_number">Reference Number</label>
                                <input type="text" id="reference_number" name="reference_number" placeholder="Enter payment reference number">
                            </div>
                        </div>

                        <!-- Cash on Hand Fields -->
                        <div id="cash-on-hand-fields" class="payment-fields">
                            <p>Please proceed to the cashier for payment.</p>
                        </div>
                    </div>
                    
                    <!-- Admin Registration Fields -->
                    <div id="admin-registration-fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="admin_first_name">First Name</label>
                                <input type="text" id="admin_first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="admin_last_name">Last Name</label>
                                <input type="text" id="admin_last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Email Address</label>
                            <input type="email" id="admin_email" name="email" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="password-field">
                                    <input type="password" id="password" name="password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="password-field">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div id="submit-section" style="display: none;">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="user-plus"></i>
                            Create Account
                        </button>
                    </div>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        
        function selectRole(role) {
            document.getElementById('user_role').value = role;
            document.getElementById('role-selection').style.display = 'none';
            document.getElementById('registration-form').classList.add('active');
            
            // Reset all fields
            document.querySelectorAll('.role-fields').forEach(field => {
                field.classList.remove('active');
            });
            
            document.getElementById('alumni-registration-fields').style.display = 'none';
            document.getElementById('admin-registration-fields').style.display = 'none';
            document.getElementById('submit-section').style.display = 'none';
            
            // Dynamically set required attributes
            if (role === 'alumni') {
                setAdminFieldsRequired(false);
                setAlumniFieldsRequired(true);
                document.getElementById('alumni-fields').classList.add('active');
            } else if (role === 'administrator') {
                setAdminFieldsRequired(true);
                setAlumniFieldsRequired(false);
                document.getElementById('admin-fields').classList.add('active');
                document.getElementById('admin-registration-fields').style.display = 'block';
                document.getElementById('submit-section').style.display = 'block';
            }
        }

        function setAdminFieldsRequired(isRequired) {
            document.getElementById('admin_first_name').required = isRequired;
            document.getElementById('admin_last_name').required = isRequired;
            document.getElementById('admin_email').required = isRequired;
            document.getElementById('password').required = isRequired;
            document.getElementById('confirm_password').required = isRequired;
        }

        function setAlumniFieldsRequired(isRequired) {
            document.getElementById('email').required = isRequired;
            document.getElementById('alumni_password').required = isRequired;
            document.getElementById('alumni_confirm_password').required = isRequired;
            // Add other alumni required fields as needed
        }
        
        function showRoleSelection() {
            document.getElementById('registration-form').classList.remove('active');
            document.getElementById('role-selection').style.display = 'grid';
            document.getElementById('user_role').value = '';
        }
        
        function verifyAlumniID() {
            const alumniID = document.getElementById('alumni_id').value.trim();
            const resultDiv = document.getElementById('verification-result');
            const verifyBtn = document.querySelector('.btn-verify');
            
            if (!alumniID) {
                showVerificationResult('Please enter an Alumni ID number', 'error');
                return;
            }
            
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i data-lucide="loader-2"></i> Verifying...';
            lucide.createIcons();
            
            const formData = new FormData();
            formData.append('alumni_id', alumniID);
            
            fetch('verify_alumni_id.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showVerificationResult('Alumni ID verified successfully!', 'success');
                    fillAlumniFields(data.data);
                    document.getElementById('alumni-registration-fields').style.display = 'block';
                    document.getElementById('submit-section').style.display = 'block';
                    verifyBtn.style.display = 'none'; // Hide the verify button
                } else {
                    // Always show the same message for not found
                    showVerificationResult(
                        "We’re sorry, the Alumni ID you entered could not be found. Kindly check and try again.",
                        'error'
                    );
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i data-lucide="search"></i> Verify Alumni ID';
                }
            })
            .catch(error => {
                console.error('Verification error:', error);
                showVerificationResult('Error verifying Alumni ID. Please try again. Error: ' + error.message, 'error');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i data-lucide="search"></i> Verify Alumni ID';
            })
            .finally(() => {
                lucide.createIcons();
            });
        }
        
        function fillAlumniFields(data) {
            document.getElementById('student_no').value = data.student_no || '';
            document.getElementById('course').value = data.course || '';
            document.getElementById('last_name').value = data.last_name || '';
            document.getElementById('first_name').value = data.first_name || '';
            document.getElementById('middle_name').value = data.middle_name || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('address').value = data.address || '';
            // Fill hidden fields for backend processing
            document.getElementById('hidden_student_no').value = data.student_no || '';
            document.getElementById('hidden_last_name').value = data.last_name || '';
            document.getElementById('hidden_first_name').value = data.first_name || '';
            document.getElementById('hidden_middle_name').value = data.middle_name || '';
            document.getElementById('hidden_course').value = data.course || '';
            document.getElementById('hidden_email').value = data.email || '';
            document.getElementById('hidden_address').value = data.address || '';
        }
        
        function showVerificationResult(message, type) {
            const resultDiv = document.getElementById('verification-result');
            resultDiv.textContent = message;
            resultDiv.className = `verification-result ${type}`;
            resultDiv.style.display = 'block';
        }
        
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            const icon = toggle.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                field.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
        
        function toggleEmploymentFields() {
            const employmentStatus = document.getElementById('employment_status').value;
            const allEmploymentFields = document.querySelectorAll('.employment-fields');
            
            allEmploymentFields.forEach(field => {
                field.style.display = 'none';
            });
            
            if (employmentStatus === 'Employed') {
                document.getElementById('employed-fields').style.display = 'block';
            } else if (employmentStatus === 'Unemployed') {
                document.getElementById('unemployed-fields').style.display = 'block';
            } else if (employmentStatus === 'Self-employed') {
                document.getElementById('self-employed-fields').style.display = 'block';
            } else if (employmentStatus === 'Further Studying') {
                document.getElementById('further-studying-fields').style.display = 'block';
            }
        }
        
        function togglePaymentFields() {
            const paymentMethod = document.getElementById('payment_method').value;
            const allPaymentFields = document.querySelectorAll('.payment-fields');
            
            allPaymentFields.forEach(field => {
                field.style.display = 'none';
            });
            
            if (paymentMethod === 'GCASH') {
                document.getElementById('gcash-fields').style.display = 'block';
            } else if (paymentMethod === 'Cash on Hand') {
                document.getElementById('cash-on-hand-fields').style.display = 'block';
            }
        }
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            document.getElementById('payment_method').disabled = false;
            const userRole = document.getElementById('user_role').value;
            if (userRole === 'alumni') {
                const password = document.getElementById('alumni_password').value;
                const confirmPassword = document.getElementById('alumni_confirm_password').value;
                let errorMsg = '';
                if (password !== confirmPassword) {
                    errorMsg = 'Passwords do not match!';
                } else {
                    if (password.length < 8) errorMsg += 'Password must be at least 8 characters long.\n';
                    if (!/[A-Z]/.test(password)) errorMsg += 'Please add at least one uppercase letter.\n';
                    if (!/\d/.test(password)) errorMsg += 'Please add at least one number.\n';
                    if (!/[\W_]/.test(password)) errorMsg += 'Please add at least one special character.\n';
                }
                if (errorMsg) {
                    e.preventDefault();
                    alert(errorMsg.trim());
                    // Stay on alumni registration, do not reset form or go back
                    document.getElementById('role-selection').style.display = 'none';
                    document.getElementById('registration-form').classList.add('active');
                    setTimeout(function() {
                        var alumniPass = document.getElementById('alumni_password');
                        if (alumniPass && alumniPass.offsetParent !== null) alumniPass.focus();
                    }, 100);
                    return false;
                }
            } else if (userRole === 'administrator') {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
            }
        });
    </script>
</body>
</html>
