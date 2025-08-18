<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';
session_start();

// Delete Job Post
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Get the company logo path before deleting
    $query = "SELECT company_logo FROM job_posts WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $logo_path = $row['company_logo'];
        
        // Delete the logo file if it exists
        if ($logo_path && file_exists($logo_path)) {
            unlink($logo_path);
        }
    }
    
    // Delete the job post
    $query = "DELETE FROM job_posts WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Job post deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting job post: " . mysqli_error($conn);
    }
    
    header("Location: manage-posts.php");
    exit();
}

// Archive Job Post
if (isset($_GET['archive'])) {
    $id = mysqli_real_escape_string($conn, $_GET['archive']);
    $query = "UPDATE job_posts SET status = 'archived' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Job post archived successfully!";
    } else {
        $_SESSION['error'] = "Error archiving job post: " . mysqli_error($conn);
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

        $jobTitle = mysqli_real_escape_string($conn, $_POST['jobTitle']);
        $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
        $jobType = mysqli_real_escape_string($conn, $_POST['jobType']);
        $jobCategory = mysqli_real_escape_string($conn, $_POST['jobCategory']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $salaryMin = !empty($_POST['salaryMin']) ? mysqli_real_escape_string($conn, $_POST['salaryMin']) : 'NULL';
        $salaryMax = !empty($_POST['salaryMax']) ? mysqli_real_escape_string($conn, $_POST['salaryMax']) : 'NULL';
        $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
        $contactEmail = mysqli_real_escape_string($conn, $_POST['contactEmail']);
        $contactPhone = mysqli_real_escape_string($conn, $_POST['contactPhone']);
        $jobDescription = mysqli_real_escape_string($conn, $_POST['jobDescription']);
        $qualifications = mysqli_real_escape_string($conn, $_POST['qualifications']);
        $howToApply = mysqli_real_escape_string($conn, $_POST['howToApply']);
        $jobLink = isset($_POST['jobLink']) ? mysqli_real_escape_string($conn, $_POST['jobLink']) : '';
        $postStatus = $_POST['create_job_post']; // Will be either 'draft' or 'published'
        $currency = mysqli_real_escape_string($conn, $_POST['currency']);

        // Handle file upload
        $companyLogo = 'NULL';
        if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/company_logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['companyLogo']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['companyLogo']['tmp_name'], $uploadFile)) {
                $companyLogo = "'$uploadFile'";
            }
        }

        // Insert into database
        $query = "INSERT INTO job_posts (
                    job_title, company_name, company_logo, job_type, job_category, 
                    location, salary_min, salary_max, currency, deadline, contact_email, 
                    contact_phone, job_description, qualifications, how_to_apply, 
                    job_link,
                    status, posted_date
                  ) VALUES (
                    '$jobTitle', '$companyName', $companyLogo, '$jobType', '$jobCategory',
                    '$location', $salaryMin, $salaryMax, '$currency', '$deadline', '$contactEmail',
                    '$contactPhone', '$jobDescription', '$qualifications', '$howToApply',
                    '$jobLink',
                    '$postStatus', NOW()
                  )";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Job post created successfully!";
            header("Location: manage-posts.php");
            exit();
        } else {
            throw new Exception("Error creating job post: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: create-posts.php");
        exit();
    }
}

// Toggle Status
if (isset($_GET['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['toggle_status']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $query = "UPDATE job_posts SET status = '$status' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Job post status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating job post status: " . mysqli_error($conn);
    }

    header("Location: manage-posts.php");
    exit();
}

// Update Job Post
if (isset($_POST['update_job_post'])) {
    try {
        $id = mysqli_real_escape_string($conn, $_POST['job_id']);
        
        // Validate required fields
        $required_fields = ['jobTitle', 'companyName', 'jobType', 'jobCategory', 'location', 'deadline', 'contactEmail', 'jobDescription', 'qualifications', 'howToApply'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        $jobTitle = mysqli_real_escape_string($conn, $_POST['jobTitle']);
        $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
        $jobType = mysqli_real_escape_string($conn, $_POST['jobType']);
        $jobCategory = mysqli_real_escape_string($conn, $_POST['jobCategory']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $salaryMin = !empty($_POST['salaryMin']) ? mysqli_real_escape_string($conn, $_POST['salaryMin']) : 'NULL';
        $salaryMax = !empty($_POST['salaryMax']) ? mysqli_real_escape_string($conn, $_POST['salaryMax']) : 'NULL';
        $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
        $contactEmail = mysqli_real_escape_string($conn, $_POST['contactEmail']);
        $contactPhone = mysqli_real_escape_string($conn, $_POST['contactPhone']);
        $jobDescription = mysqli_real_escape_string($conn, $_POST['jobDescription']);
        $qualifications = mysqli_real_escape_string($conn, $_POST['qualifications']);
        $howToApply = mysqli_real_escape_string($conn, $_POST['howToApply']);
        $jobLink = isset($_POST['jobLink']) ? mysqli_real_escape_string($conn, $_POST['jobLink']) : '';
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $currency = mysqli_real_escape_string($conn, $_POST['currency']);

        // Handle file upload
        $companyLogo = '';
        if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/company_logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['companyLogo']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['companyLogo']['tmp_name'], $uploadFile)) {
                // Delete old logo if exists
                $query = "SELECT company_logo FROM job_posts WHERE id = '$id'";
                $result = mysqli_query($conn, $query);
                if ($row = mysqli_fetch_assoc($result)) {
                    $oldLogo = $row['company_logo'];
                    if ($oldLogo && file_exists($oldLogo)) {
                        unlink($oldLogo);
                    }
                }
                $companyLogo = ", company_logo = '$uploadFile'";
            }
        }

        // Update database
        $query = "UPDATE job_posts SET 
                    job_title = '$jobTitle',
                    company_name = '$companyName',
                    job_type = '$jobType',
                    job_category = '$jobCategory',
                    location = '$location',
                    salary_min = $salaryMin,
                    salary_max = $salaryMax,
                    currency = '$currency',
                    deadline = '$deadline',
                    contact_email = '$contactEmail',
                    contact_phone = '$contactPhone',
                    job_description = '$jobDescription',
                    qualifications = '$qualifications',
                    how_to_apply = '$howToApply',
                    job_link = '$jobLink',
                    status = '$status'
                    $companyLogo
                  WHERE id = '$id'";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Job post updated successfully!";
            header("Location: view-post.php?id=$id");
            exit();
        } else {
            throw new Exception("Error updating job post: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit-post.php?id=$id");
        exit();
    }
}
?> 