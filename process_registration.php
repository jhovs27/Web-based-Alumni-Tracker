<?php
require_once 'admin/config/database.php';
session_start();

// Debug: Log the request
error_log("Registration request received: " . print_r($_POST, true));
error_log("Files received: " . print_r($_FILES, true));

// Simple debug test - create a debug file
file_put_contents('debug_registration.txt', "Form submitted at: " . date('Y-m-d H:i:s') . "\n");
file_put_contents('debug_registration.txt', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug_registration.txt', "FILES data: " . print_r($_FILES, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_role = $_POST['user_role'] ?? '';
    
    error_log("User role: " . $user_role);
    
    if ($user_role === 'alumni') {
        // Alumni Registration Processing
        error_log("Processing alumni registration");
        processAlumniRegistration($conn);
    } elseif ($user_role === 'administrator') {
        // Administrator Registration Processing
        error_log("Processing admin registration");
        processAdminRegistration($conn);
    } else {
        error_log("Invalid user role: " . $user_role);
        $_SESSION['error'] = 'Invalid user role.';
        header('Location: register.php');
        exit;
    }
} else {
    error_log("Not a POST request");
    header('Location: register.php');
    exit;
}

function processAlumniRegistration($conn) {
    try {
        // Get all form data
        $alumni_id = $_POST['alumni_id'] ?? '';
        $student_no = $_POST['student_no'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $sex = $_POST['sex'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $birthplace = $_POST['birthplace'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact_number = $_POST['contact_number'] ?? '';
        $course = $_POST['course'] ?? '';
        $date_graduated = $_POST['date_graduated'] ?? '';
        $academic_year = $_POST['academic_year'] ?? '';
        $civil_status = $_POST['civil_status'] ?? '';
        $password = $_POST['alumni_password'] ?? '';
        $confirm_password = $_POST['alumni_confirm_password'] ?? '';
        
        // Employment information
        $employment_status = $_POST['employment_status'] ?? '';
        $current_employment_status = $_POST['current_employment_status'] ?? '';
        $job_title = $_POST['job_title'] ?? '';
        $company_name = $_POST['company_name'] ?? '';
        $company_address = $_POST['company_address'] ?? '';
        $job_location = $_POST['job_location'] ?? '';
        $industry_type = $_POST['industry_type'] ?? '';
        $employment_from = $_POST['employment_from'] ?? '';
        $employment_to = $_POST['employment_to'] ?? '';
        $job_related_to_degree = $_POST['job_related_to_degree'] ?? '';
        
        // Unemployed fields
        $current_status = $_POST['unemployment_status'] ?? '';
        $engaged_in_applications = $_POST['job_applications'] ?? '';
        
        // Self-employed fields
        $business_name = $_POST['business_name'] ?? '';
        $business_type = $_POST['business_type'] ?? '';
        $business_start_date = $_POST['business_start_date'] ?? '';
        $business_address = $_POST['business_address'] ?? '';
        $business_related_to_degree = $_POST['business_related_to_degree'] ?? '';
        
        // Further studying fields
        $educational_level = $_POST['educational_level'] ?? '';
        $school_institution = $_POST['school_institution'] ?? '';
        $program_degree = $_POST['program_degree'] ?? '';
        
        // Payment information
        $payment_method = $_POST['payment_method'] ?? '';
        $gcash_name = $_POST['gcash_name'] ?? '';
        $gcash_number = $_POST['gcash_number'] ?? '';
        $reference_number = $_POST['reference_number'] ?? '';
        
        // Validation
        if (empty($alumni_id) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Required fields are missing.';
            header('Location: register.php');
            exit;
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: register.php');
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long.';
            header('Location: register.php');
            exit;
        }
        
        // Check if alumni already exists
        $stmt = $conn->prepare("SELECT id FROM alumni WHERE alumni_id = ?");
        $stmt->execute([$alumni_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Alumni ID already registered.';
            header('Location: register.php');
            exit;
        }
        
        // Check if email already exists
        if (!empty($email)) {
            $stmt = $conn->prepare("SELECT id FROM alumni WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already registered.';
                header('Location: register.php');
                exit;
            }
        }
        
        // Handle file uploads
        $employment_proof_document = '';
        $business_permit_document = '';
        $payment_proof = '';
        
        // Create upload directories
        $upload_dirs = [
            'uploads/documents/employment/',
            'uploads/documents/business/',
            'uploads/documents/payment/'
        ];
        
        foreach ($upload_dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        // Handle employment proof document
        if (isset($_FILES['employment_proof']) && $_FILES['employment_proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['employment_proof'];
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
                $filename = 'employment_proof_' . time() . '_' . $alumni_id . '.' . $file_extension;
                $filepath = 'uploads/documents/employment/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $employment_proof_document = $filepath;
                }
            }
        }
        
        // Handle business permit document
        if (isset($_FILES['business_proof']) && $_FILES['business_proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['business_proof'];
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
                $filename = 'business_permit_' . time() . '_' . $alumni_id . '.' . $file_extension;
                $filepath = 'uploads/documents/business/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $business_permit_document = $filepath;
                }
            }
        }
        
        // Handle payment proof
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['payment_proof'];
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
                $filename = 'payment_proof_' . time() . '_' . $alumni_id . '.' . $file_extension;
                $filepath = 'uploads/documents/payment/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $payment_proof = $filepath;
                }
            }
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Create fullname
        $fullname = trim($last_name . ', ' . $first_name . ' ' . $middle_name);
        
        // Prepare SQL statement for alumni table (all columns except id)
        $sql = "INSERT INTO alumni (
            alumni_id, fullname, last_name, first_name, middle_name, sex, birthdate, birthplace, email, phone, address, password_hash, course, date_graduated, academic_year, civil_status,
            employment_status, current_employment_status, job_title, company_name, company_address, job_location, industry_type, employment_from, employment_to, job_related_to_degree, employment_proof_document,
            current_status, engaged_in_applications, business_name, business_type, business_start_date, business_address, business_related_to_degree, business_permit_document,
            educational_level, school_institution, program_degree, payment_method, gcash_name, gcash_number, payment_status, payment_date, payment_reference, student_no, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $alumni_id,
            $fullname,
            $last_name,
            $first_name,
            $middle_name,
            $sex,
            $birthdate,
            $birthplace,
            $email,
            $contact_number,
            $birthplace, // or use a separate address field if you have one
            $password_hash,
            $course,
            $date_graduated,
            $academic_year,
            $civil_status,
            $employment_status,
            $current_employment_status,
            $job_title,
            $company_name,
            $company_address,
            $job_location,
            $industry_type,
            $employment_from,
            $employment_to,
            $job_related_to_degree,
            $employment_proof_document,
            $current_status,
            $engaged_in_applications,
            $business_name,
            $business_type,
            $business_start_date,
            $business_address,
            $business_related_to_degree,
            $business_permit_document,
            $educational_level,
            $school_institution,
            $program_degree,
            $payment_method,
            $gcash_name,
            $gcash_number,
            'pending', // payment_status
            null,      // payment_date
            $reference_number, // payment_reference
            $student_no
        ]);
        
        $_SESSION['success'] = 'Alumni registration successful! Your account is pending verification. You will be notified once approved.';
        header('Location: login.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
        header('Location: register.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
        header('Location: register.php');
        exit;
    }
}

function processAdminRegistration($conn) {
    // Admin registration logic (if needed)
    $_SESSION['error'] = 'Admin registration not implemented yet.';
    header('Location: register.php');
    exit;
}
?>