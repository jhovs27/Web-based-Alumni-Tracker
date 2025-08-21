<?php
session_start();
require_once 'admin/config/database.php';

// Debug logging
error_log("=== LOGIN PROCESS START ===");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['userType'] ?? '';
    error_log("Role: " . $role);
    
    if ($role === 'alumni') {
        $alumni_id = trim($_POST['alumniId']);
        $password = $_POST['alumniPassword'];

        error_log("Alumni login attempt - ID: " . $alumni_id . ", Password length: " . strlen($password));

        // Validate input
        if (empty($alumni_id) || empty($password)) {
            error_log("Login failed: Empty fields");
            $_SESSION['login_error'] = "Please enter both Alumni ID and password.";
            header('Location: login.php');
            exit;
        }

        // Validate alumni ID pattern (xxx-xxxx)
        if (!preg_match('/^\d{3}-\d{4}$/', $alumni_id)) {
            error_log("Login failed: Invalid alumni ID format - " . $alumni_id);
            $_SESSION['login_error'] = "Invalid Alumni ID format. Please use the format: xxx-xxxx (e.g., 252-0006)";
            header('Location: login.php');
            exit;
        }

        try {
            // Step 1: Check if alumni_id exists in alumni_ids table
            error_log("Step 1: Checking alumni_ids table for ID: " . $alumni_id);
            $stmt = $conn->prepare("SELECT * FROM alumni_ids WHERE alumni_id = ?");
            $stmt->execute([$alumni_id]);
            $alumni_id_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$alumni_id_record) {
                error_log("Login failed: Alumni ID not found in alumni_ids table - " . $alumni_id);
                $_SESSION['login_error'] = "Alumni ID not found. Please check your Alumni ID or register first.";
                header('Location: login.php');
                exit;
            }

            error_log("Step 1: ✓ Alumni ID found in alumni_ids table: " . $alumni_id);
            error_log("Student No from alumni_ids: " . $alumni_id_record['student_no']);

            // Step 2: Check if alumni exists in alumni table using student_no
            error_log("Step 2: Checking alumni table for student_no: " . $alumni_id_record['student_no']);
            $stmt = $conn->prepare("SELECT * FROM alumni WHERE student_no = ?");
            $stmt->execute([$alumni_id_record['student_no']]);
            $alumni = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$alumni) {
                error_log("Login failed: Alumni not found in alumni table for student_no - " . $alumni_id_record['student_no']);
                $_SESSION['login_error'] = "Alumni not registered. Please complete your registration first.";
                header('Location: login.php');
                exit;
            }

            error_log("Step 2: ✓ Alumni found in alumni table: " . ($alumni['first_name'] . ' ' . $alumni['last_name']));

            // Step 3: Check if password exists in alumni table
            error_log("Step 3: Checking password in alumni table for student_no: " . $alumni_id_record['student_no']);
        $stored_password = $alumni['password_hash'] ?? $alumni['password'] ?? null;
        
        if (!$stored_password) {
                error_log("Login failed: No password found in alumni table for student_no - " . $alumni_id_record['student_no']);
                $_SESSION['login_error'] = "No password set for this alumni. Please contact support or reset your password.";
            header('Location: login.php');
            exit;
        }

            error_log("Step 3: ✓ Password found in alumni table");

            // Step 4: Verify password from alumni table
            error_log("Step 4: Verifying password from alumni table for alumni ID: " . $alumni_id);
        if (!password_verify($password, $stored_password)) {
                error_log("Login failed: Incorrect password for alumni ID - " . $alumni_id);
                $_SESSION['login_error'] = "Incorrect password. Please check your password and try again.";
            header('Location: login.php');
            exit;
        }

            error_log("Step 4: ✓ Password verification successful for alumni ID - " . $alumni_id);

            // Step 5: SUCCESS - Clear session and set new alumni session
            error_log("Step 5: Setting session variables");
            session_unset();
            session_regenerate_id(true);
            
            $_SESSION['is_alumni'] = true;
            $_SESSION['alumni_id'] = $alumni['alumni_id'];
            $_SESSION['alumni_alumni_id'] = $alumni_id; // Use the alumni_id from input
            $_SESSION['alumni_name'] = $alumni['first_name'] . ' ' . $alumni['last_name'];
            $_SESSION['alumni_email'] = $alumni['email'];
            $_SESSION['alumni_student_no'] = $alumni['student_no'];
            $_SESSION['alumni_course'] = $alumni['course'];
            $_SESSION['alumni_employment_status'] = $alumni['employment_status'];
            $_SESSION['login_time'] = time();
            
            error_log("Step 5: ✓ Session variables set successfully: " . print_r($_SESSION, true));
            error_log("Step 6: Redirecting to: alumni/index.php");
            
            // Step 6: Redirect to alumni dashboard
            error_log("=== LOGIN SUCCESS - REDIRECTING TO ALUMNI DASHBOARD ===");
            
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Redirect to alumni dashboard
            header('Location: alumni/index.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("Database error during alumni login: " . $e->getMessage());
            $_SESSION['login_error'] = "Database error occurred. Please try again.";
            header('Location: login.php');
            exit;
        }
    }
    
    if ($role === 'admin') {
        $username = trim($_POST['adminUsername']);
        $password = $_POST['adminPassword'];

        $stmt = $conn->prepare("SELECT * FROM admins WHERE name = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $_SESSION['login_error'] = "Invalid admin credentials.";
            header('Location: login.php');
            exit;
        }

        session_unset();
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['profile_photo_path'] = $admin['profile_photo_path'];
        
        header('Location: admin/index.php');
        exit();
    }
    
    if ($role === 'programchair') {
        $username = trim($_POST['pcUsername']);
        $password = $_POST['pcPassword'];

        $stmt = $conn->prepare("SELECT * FROM program_chairs WHERE username = ?");
        $stmt->execute([$username]);
        $chair = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$chair || !password_verify($password, $chair['password'])) {
            $_SESSION['login_error'] = "Invalid program chair credentials.";
            header('Location: login.php');
            exit;
        }

        session_unset();
        $_SESSION['is_chair'] = true;
        $_SESSION['chair_id'] = $chair['id'];
        $_SESSION['chair_name'] = $chair['full_name'];
        $_SESSION['chair_email'] = $chair['email'];
        $_SESSION['chair_program'] = $chair['program'];
        $_SESSION['chair_designation'] = $chair['Designation'];
        $_SESSION['profile_photo_path'] = $chair['profile_picture'] ?? null;

        header('Location: program_chair/index.php');
        exit();
    }
}

error_log("No valid role found or no POST data");
// If we get here, redirect back to login
header('Location: login.php');
exit();
?>