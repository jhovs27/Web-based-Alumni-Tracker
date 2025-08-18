<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../admin/config/database.php';

// Check if user is logged in as program chair
if (!isset($_SESSION['is_chair']) || !$_SESSION['is_chair']) {
    header('Location: ../login.php');
    exit();
}

// Delete Job Post
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Get the company logo path before deleting
        $stmt = $conn->prepare("SELECT company_logo FROM job_posts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if ($row && $row['company_logo']) {
            $logo_path = '../admin/' . $row['company_logo'];
            // Delete the logo file if it exists
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        
        // Delete the job post
        $stmt = $conn->prepare("DELETE FROM job_posts WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Job post deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting job post: " . $e->getMessage();
    }
    
    header("Location: manage-posts.php");
    exit();
}

// Archive Job Post
if (isset($_GET['archive'])) {
    $id = (int)$_GET['archive'];
    
    try {
        $stmt = $conn->prepare("UPDATE job_posts SET status = 'archived' WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Job post archived successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error archiving job post: " . $e->getMessage();
    }
    
    header("Location: manage-posts.php");
    exit();
}

// Create Job Post
if (isset($_POST['create_job_post'])) {
    try {
        // Validate required fields
        $required_fields = ['jobTitle', 'companyName', 'jobType', 'jobCategory', 'location', 'deadline', 'contactEmail', 'jobDescription', 'qualifications', 'howToApply'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        $jobTitle = $_POST['jobTitle'];
        $companyName = $_POST['companyName'];
        $jobType = $_POST['jobType'];
        $jobCategory = $_POST['jobCategory'];
        $location = $_POST['location'];
        $salaryMin = !empty($_POST['salaryMin']) ? $_POST['salaryMin'] : null;
        $salaryMax = !empty($_POST['salaryMax']) ? $_POST['salaryMax'] : null;
        $deadline = $_POST['deadline'];
        $contactEmail = $_POST['contactEmail'];
        $contactPhone = $_POST['contactPhone'];
        $jobDescription = $_POST['jobDescription'];
        $qualifications = $_POST['qualifications'];
        $howToApply = $_POST['howToApply'];
        $jobLink = isset($_POST['jobLink']) ? $_POST['jobLink'] : '';
        $postStatus = $_POST['create_job_post']; // Will be either 'draft' or 'published'
        $currency = $_POST['currency'];

        // Handle file upload
        $companyLogo = null;
        if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../admin/uploads/company_logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['companyLogo']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['companyLogo']['tmp_name'], $uploadFile)) {
                $companyLogo = 'uploads/company_logos/' . $newFileName;
            }
        }

        // Insert into database
        $stmt = $conn->prepare("
            INSERT INTO job_posts (
                job_title, company_name, company_logo, job_type, job_category, 
                location, salary_min, salary_max, currency, deadline, contact_email, 
                contact_phone, job_description, qualifications, how_to_apply, 
                job_link, status, posted_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $jobTitle, $companyName, $companyLogo, $jobType, $jobCategory,
            $location, $salaryMin, $salaryMax, $currency, $deadline, $contactEmail,
            $contactPhone, $jobDescription, $qualifications, $howToApply,
            $jobLink, $postStatus
        ]);

        $_SESSION['success'] = "Job post created successfully!";
        header("Location: manage-posts.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: create-posts.php");
        exit();
    }
}

// Toggle Status
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $status = $_GET['status'];

    try {
        $stmt = $conn->prepare("UPDATE job_posts SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        $_SESSION['success'] = "Job post status updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating job post status: " . $e->getMessage();
    }

    header("Location: manage-posts.php");
    exit();
}

// Update Job Post
if (isset($_POST['update_job_post'])) {
    try {
        $id = (int)$_POST['job_id'];
        
        // Validate required fields
        $required_fields = ['jobTitle', 'companyName', 'jobType', 'jobCategory', 'location', 'deadline', 'contactEmail', 'jobDescription', 'qualifications', 'howToApply'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        $jobTitle = $_POST['jobTitle'];
        $companyName = $_POST['companyName'];
        $jobType = $_POST['jobType'];
        $jobCategory = $_POST['jobCategory'];
        $location = $_POST['location'];
        $salaryMin = !empty($_POST['salaryMin']) ? $_POST['salaryMin'] : null;
        $salaryMax = !empty($_POST['salaryMax']) ? $_POST['salaryMax'] : null;
        $deadline = $_POST['deadline'];
        $contactEmail = $_POST['contactEmail'];
        $contactPhone = $_POST['contactPhone'];
        $jobDescription = $_POST['jobDescription'];
        $qualifications = $_POST['qualifications'];
        $howToApply = $_POST['howToApply'];
        $jobLink = isset($_POST['jobLink']) ? $_POST['jobLink'] : '';
        $status = $_POST['status'];
        $currency = $_POST['currency'];

        // Handle file upload
        $companyLogoUpdate = '';
        $companyLogo = null;
        if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../admin/uploads/company_logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['companyLogo']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['companyLogo']['tmp_name'], $uploadFile)) {
                // Delete old logo if exists
                $stmt = $conn->prepare("SELECT company_logo FROM job_posts WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                
                if ($row && $row['company_logo']) {
                    $oldLogo = '../admin/' . $row['company_logo'];
                    if (file_exists($oldLogo)) {
                        unlink($oldLogo);
                    }
                }
                
                $companyLogo = 'uploads/company_logos/' . $newFileName;
                $companyLogoUpdate = ', company_logo = ?';
            }
        }

        // Update database
        $sql = "UPDATE job_posts SET 
                    job_title = ?, company_name = ?, job_type = ?, job_category = ?, 
                    location = ?, salary_min = ?, salary_max = ?, currency = ?, 
                    deadline = ?, contact_email = ?, contact_phone = ?, 
                    job_description = ?, qualifications = ?, how_to_apply = ?, 
                    job_link = ?, status = ?" . $companyLogoUpdate . "
                  WHERE id = ?";
        
        $params = [
            $jobTitle, $companyName, $jobType, $jobCategory, $location,
            $salaryMin, $salaryMax, $currency, $deadline, $contactEmail, $contactPhone,
            $jobDescription, $qualifications, $howToApply, $jobLink, $status
        ];
        
        if ($companyLogo) {
            $params[] = $companyLogo;
        }
        
        $params[] = $id;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Job post updated successfully!";
        header("Location: view-post.php?id=$id");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit-post.php?id=$id");
        exit();
    }
}

// If no action specified, redirect to manage posts
header('Location: manage-posts.php');
exit();
?> 