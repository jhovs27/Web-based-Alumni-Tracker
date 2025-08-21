<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== EDIT EVENT FORM SUBMITTED ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
}

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    $_SESSION['error'] = "Invalid event ID.";
    header("Location: manage-events.php");
    exit();
}

// Fetch event details
try {
    $stmt = $conn->prepare("SELECT * FROM alumni_events WHERE id = :id");
    $stmt->bindValue(':id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        $_SESSION['error'] = "Event not found.";
        header("Location: manage-events.php");
        exit();
    }
    
    // Debug: Log the fetched event data
    error_log("Fetched event data: " . print_r($event, true));
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching event details: " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log the POST data
        error_log("Edit event form submitted. POST data: " . print_r($_POST, true));
        error_log("Event ID: $event_id");
        
        // Check if status is present in POST data
        if (!isset($_POST['status'])) {
            throw new Exception("Status field is missing from form submission");
        }
        
        $new_status = $_POST['status'];
        error_log("New status from form: $new_status");
        
        // Handle file uploads - keep existing files if no new ones uploaded
        $poster_image = $event['poster_image']; // Keep existing poster
        $event_document = $event['event_document']; // Keep existing document

        // Handle poster image upload
        if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === 0) {
            error_log("New poster image uploaded: " . print_r($_FILES['poster_image'], true));
            
            $upload_dir = 'uploads/events/posters/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old poster if it exists
            if (!empty($event['poster_image']) && file_exists($event['poster_image'])) {
                unlink($event['poster_image']);
                error_log("Deleted old poster: " . $event['poster_image']);
            }
            
            $poster_image = $upload_dir . time() . '_' . basename($_FILES['poster_image']['name']);
            if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $poster_image)) {
                error_log("New poster uploaded successfully to: " . $poster_image);
            } else {
                error_log("Failed to upload new poster");
                $poster_image = $event['poster_image']; // Keep old one if upload fails
            }
        } else {
            error_log("No new poster uploaded, keeping existing: " . $event['poster_image']);
        }

        // Handle event document upload
        if (isset($_FILES['event_document']) && $_FILES['event_document']['error'] === 0) {
            error_log("New event document uploaded: " . print_r($_FILES['event_document'], true));
            
            $upload_dir = 'uploads/events/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old document if it exists
            if (!empty($event['event_document']) && file_exists($event['event_document'])) {
                unlink($event['event_document']);
                error_log("Deleted old document: " . $event['event_document']);
            }
            
            $event_document = $upload_dir . time() . '_' . basename($_FILES['event_document']['name']);
            if (move_uploaded_file($_FILES['event_document']['tmp_name'], $event_document)) {
                error_log("New document uploaded successfully to: " . $event_document);
            } else {
                error_log("Failed to upload new document");
                $event_document = $event['event_document']; // Keep old one if upload fails
            }
        } else {
            error_log("No new document uploaded, keeping existing: " . $event['event_document']);
        }

        // Validate status
        $valid_statuses = ['Draft', 'Published', 'Cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception("Invalid status value: " . $new_status);
        }

        // First, check current status in database
        $stmt = $conn->prepare("SELECT status FROM alumni_events WHERE id = ?");
        $stmt->execute([$event_id]);
        $current_status = $stmt->fetchColumn();
        error_log("Current status in database: $current_status");

        $sql = "UPDATE alumni_events SET
            event_title = :event_title,
            event_description = :event_description,
            event_type = :event_type,
            event_category = :event_category,
            start_datetime = :start_datetime,
            end_datetime = :end_datetime,
            physical_address = :physical_address,
            online_link = :online_link,
            timezone = :timezone,
            poster_image = :poster_image,
            event_document = :event_document,
            registration_required = :registration_required,
            max_attendees = :max_attendees,
            allow_guests = :allow_guests,
            auto_confirm = :auto_confirm,
            contact_person = :contact_person,
            contact_email = :contact_email,
            contact_phone = :contact_phone,
            visibility = :visibility,
            status = :status
            WHERE id = :id
        ";

        $params = [
            ':event_title' => $_POST['event_title'],
            ':event_description' => $_POST['event_description'],
            ':event_type' => $_POST['event_type'],
            ':event_category' => $_POST['event_category'],
            ':start_datetime' => $_POST['start_datetime'],
            ':end_datetime' => $_POST['end_datetime'],
            ':physical_address' => $_POST['physical_address'],
            ':online_link' => $_POST['online_link'],
            ':timezone' => $_POST['timezone'],
            ':poster_image' => $poster_image,
            ':event_document' => $event_document,
            ':registration_required' => isset($_POST['registration_required']) ? 1 : 0,
            ':max_attendees' => $_POST['max_attendees'] ?: null,
            ':allow_guests' => isset($_POST['allow_guests']) ? 1 : 0,
            ':auto_confirm' => isset($_POST['auto_confirm']) ? 1 : 0,
            ':contact_person' => $_POST['contact_person'],
            ':contact_email' => $_POST['contact_email'],
            ':contact_phone' => $_POST['contact_phone'],
            ':visibility' => $_POST['visibility'],
            ':status' => $new_status,
            ':id' => $event_id
        ];

        error_log("SQL Parameters: " . print_r($params, true));

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            $rows_affected = $stmt->rowCount();
            error_log("Event updated successfully. Event ID: $event_id, New Status: $new_status, Rows affected: $rows_affected");
            
            // Verify the update
            $stmt = $conn->prepare("SELECT status, poster_image, event_document FROM alumni_events WHERE id = ?");
            $stmt->execute([$event_id]);
            $updated_data = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Data after update: " . print_r($updated_data, true));
            
            $_SESSION['success'] = "Event updated successfully! Status changed from '$current_status' to '$new_status'";
        } else {
            error_log("No rows were affected when updating event");
            $_SESSION['error'] = "No changes were made to the event.";
        }
        
        header("Location: manage-events.php");
        exit();
    } catch(PDOException $e) {
        error_log("Database error updating event: " . $e->getMessage());
        $_SESSION['error'] = "Error updating event: " . $e->getMessage();
    } catch(Exception $e) {
        error_log("General error updating event: " . $e->getMessage());
        $_SESSION['error'] = "Error updating event: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Alumni Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles for animations and file uploads */
        .file-upload-container {
            border: 2px dashed #e2e8f0;
            transition: all 0.3s ease;
        }
        .file-upload-container:hover {
            border-color: #3b82f6;
        }
        .file-upload-icon {
            color: #9ca3af;
            transition: color 0.3s ease;
        }
        .file-upload-container:hover .file-upload-icon {
            color: #3b82f6;
        }
        .file-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .current-file {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-helper {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .form-checkbox {
            width: 1rem;
            height: 1rem;
            color: #3b82f6;
            border-color: #d1d5db;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col h-0 flex-1">
                    <div class="flex items-center h-16 flex-shrink-0 px-4 bg-gray-900">
                        <h1 class="text-xl font-semibold text-white">Admin Panel</h1>
                    </div>
                    <div class="flex-1 flex flex-col overflow-y-auto">
                        <nav class="flex-1 px-2 py-4 bg-gray-800 space-y-1">
                            <a href="index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                Dashboard
                            </a>
                            <a href="manage-events.php" class="bg-gray-900 text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-calendar mr-3"></i>
                                Manage Events
                            </a>
                            <a href="manage-posts.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                                <i class="fas fa-newspaper mr-3"></i>
                                Manage Posts
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <!-- Header -->
                        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">Edit Event</h1>
                                    <p class="text-gray-600">Update event details and settings</p>
                                </div>
                                <a href="manage-events.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>Back to Events
                                </a>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Edit Form -->
                        <form id="eventForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="event_title" class="form-label">Event Title</label>
                                        <input type="text" name="event_title" id="event_title" required
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['event_title']); ?>">
                                    </div>
                                    <div>
                                        <label for="event_type" class="form-label">Event Type</label>
                                        <select name="event_type" id="event_type" required class="form-select">
                                            <option value="Reunion" <?php if($event['event_type']=='Reunion') echo 'selected'; ?>>Reunion</option>
                                            <option value="Seminar" <?php if($event['event_type']=='Seminar') echo 'selected'; ?>>Seminar</option>
                                            <option value="Webinar" <?php if($event['event_type']=='Webinar') echo 'selected'; ?>>Webinar</option>
                                            <option value="Career Fair" <?php if($event['event_type']=='Career Fair') echo 'selected'; ?>>Career Fair</option>
                                            <option value="Outreach" <?php if($event['event_type']=='Outreach') echo 'selected'; ?>>Outreach</option>
                                            <option value="Other" <?php if($event['event_type']=='Other') echo 'selected'; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="event_category" class="form-label">Event Category</label>
                                        <select name="event_category" id="event_category" class="form-select">
                                            <option value="">Select Category</option>
                                            <option value="Academic" <?php if($event['event_category']=='Academic') echo 'selected'; ?>>Academic</option>
                                            <option value="Social" <?php if($event['event_category']=='Social') echo 'selected'; ?>>Social</option>
                                            <option value="Career" <?php if($event['event_category']=='Career') echo 'selected'; ?>>Career</option>
                                            <option value="Other" <?php if($event['event_category']=='Other') echo 'selected'; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="event_description" class="form-label">Event Description</label>
                                        <textarea name="event_description" id="event_description" required
                                                  class="form-textarea" rows="4"><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Date and Time -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Date and Time</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="start_datetime" class="form-label">Start Date and Time</label>
                                        <input type="datetime-local" name="start_datetime" id="start_datetime" required
                                               class="form-input"
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_datetime'])); ?>">
                                    </div>
                                    <div>
                                        <label for="end_datetime" class="form-label">End Date and Time</label>
                                        <input type="datetime-local" name="end_datetime" id="end_datetime" required
                                               class="form-input"
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_datetime'])); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="physical_address" class="form-label">Physical Address</label>
                                        <input type="text" name="physical_address" id="physical_address"
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['physical_address']); ?>"
                                               placeholder="Enter venue address">
                                    </div>
                                    <div>
                                        <label for="online_link" class="form-label">Online Link</label>
                                        <input type="url" name="online_link" id="online_link"
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['online_link']); ?>"
                                               placeholder="Enter online meeting link">
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Settings -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="registration_required" id="registration_required"
                                               class="form-checkbox"
                                               <?php if($event['registration_required']) echo 'checked'; ?>>
                                        <label for="registration_required" class="ml-2 text-sm text-gray-700">Require Registration</label>
                                    </div>
                                    <div>
                                        <label for="max_attendees" class="form-label">Maximum Attendees</label>
                                        <input type="number" name="max_attendees" id="max_attendees"
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['max_attendees']); ?>"
                                               placeholder="Leave empty for unlimited">
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="allow_guests" id="allow_guests"
                                               class="form-checkbox"
                                               <?php if($event['allow_guests']) echo 'checked'; ?>>
                                        <label for="allow_guests" class="ml-2 text-sm text-gray-700">Allow Guests</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="auto_confirm" id="auto_confirm"
                                               class="form-checkbox"
                                               <?php if($event['auto_confirm']) echo 'checked'; ?>>
                                        <label for="auto_confirm" class="ml-2 text-sm text-gray-700">Auto-confirm Registrations</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="contact_person" class="form-label">Contact Person</label>
                                        <input type="text" name="contact_person" id="contact_person" required
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['contact_person']); ?>">
                                    </div>
                                    <div>
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email" name="contact_email" id="contact_email" required
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['contact_email']); ?>">
                                    </div>
                                    <div>
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="tel" name="contact_phone" id="contact_phone"
                                               class="form-input"
                                               value="<?php echo htmlspecialchars($event['contact_phone']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Media and Documents -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Media and Documents</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Event Poster Upload -->
                                    <div>
                                        <label for="poster_image" class="form-label">Event Poster</label>
                                        <div class="file-upload-container p-6 text-center">
                                            <i class="fas fa-cloud-upload-alt file-upload-icon text-3xl mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Upload a new poster image</p>
                                            <input type="file" name="poster_image" id="poster_image" 
                                                   class="form-input" accept="image/*">
                                            
                                            <?php if (!empty($event['poster_image']) && file_exists($event['poster_image'])): ?>
                                                <div class="current-file">
                                                    <p class="text-sm font-medium text-blue-600 mb-2">Current Poster:</p>
                                                    <img src="<?php echo htmlspecialchars($event['poster_image']); ?>" 
                                                         alt="Current Event Poster" 
                                                         class="file-preview w-full max-w-xs mx-auto">
                                                    <p class="text-xs text-gray-500 mt-2"><?php echo basename($event['poster_image']); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <div class="current-file">
                                                    <p class="text-sm text-gray-500">No poster currently uploaded</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Event Document Upload -->
                                    <div>
                                        <label for="event_document" class="form-label">Event Document</label>
                                        <div class="file-upload-container p-6 text-center">
                                            <i class="fas fa-file-upload file-upload-icon text-3xl mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Upload a new document</p>
                                            <input type="file" name="event_document" id="event_document" 
                                                   class="form-input" accept=".pdf,.doc,.docx">
                                            
                                            <?php if (!empty($event['event_document']) && file_exists($event['event_document'])): ?>
                                                <div class="current-file">
                                                    <p class="text-sm font-medium text-blue-600 mb-2">Current Document:</p>
                                                    <a href="<?php echo htmlspecialchars($event['event_document']); ?>" 
                                                       target="_blank" 
                                                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                                                        <i class="fas fa-file-pdf mr-2"></i>
                                                        View Document
                                                    </a>
                                                    <p class="text-xs text-gray-500 mt-2"><?php echo basename($event['event_document']); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <div class="current-file">
                                                    <p class="text-sm text-gray-500">No document currently uploaded</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Settings -->
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select name="timezone" id="timezone" class="form-select">
                                            <option value="Asia/Manila" <?php if($event['timezone']=='Asia/Manila') echo 'selected'; ?>>Philippines (Asia/Manila)</option>
                                            <option value="UTC" <?php if($event['timezone']=='UTC') echo 'selected'; ?>>UTC (Coordinated Universal Time)</option>
                                            <option value="America/New_York" <?php if($event['timezone']=='America/New_York') echo 'selected'; ?>>Eastern Time (ET)</option>
                                            <option value="America/Chicago" <?php if($event['timezone']=='America/Chicago') echo 'selected'; ?>>Central Time (CT)</option>
                                            <option value="America/Denver" <?php if($event['timezone']=='America/Denver') echo 'selected'; ?>>Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles" <?php if($event['timezone']=='America/Los_Angeles') echo 'selected'; ?>>Pacific Time (PT)</option>
                                            <option value="Europe/London" <?php if($event['timezone']=='Europe/London') echo 'selected'; ?>>London (GMT)</option>
                                            <option value="Europe/Paris" <?php if($event['timezone']=='Europe/Paris') echo 'selected'; ?>>Paris (CET)</option>
                                            <option value="Asia/Tokyo" <?php if($event['timezone']=='Asia/Tokyo') echo 'selected'; ?>>Tokyo (JST)</option>
                                            <option value="Asia/Shanghai" <?php if($event['timezone']=='Asia/Shanghai') echo 'selected'; ?>>Shanghai (CST)</option>
                                            <option value="Asia/Dubai" <?php if($event['timezone']=='Asia/Dubai') echo 'selected'; ?>>Dubai (GST)</option>
                                            <option value="Australia/Sydney" <?php if($event['timezone']=='Australia/Sydney') echo 'selected'; ?>>Sydney (AEST)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="visibility" class="form-label">Visibility</label>
                                        <select name="visibility" id="visibility" class="form-select">
                                            <option value="public" <?php if($event['visibility']=='public') echo 'selected'; ?>>Public</option>
                                            <option value="private" <?php if($event['visibility']=='private') echo 'selected'; ?>>Private</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="status" class="form-label">Status</label>
                                        <div class="flex items-center space-x-3">
                                            <select name="status" id="status" class="form-select flex-1">
                                                <option value="Draft" <?php if($event['status']=='Draft') echo 'selected'; ?>>Draft</option>
                                                <option value="Published" <?php if($event['status']=='Published') echo 'selected'; ?>>Published</option>
                                                <option value="Cancelled" <?php if($event['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                            <span id="status-display" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch($event['status']) {
                                                    case 'Published':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'Draft':
                                                        echo 'bg-gray-100 text-gray-800';
                                                        break;
                                                    case 'Cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                        break;
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($event['status']); ?>
                                            </span>
                                        </div>
                                        <p class="form-helper">Current status: <strong><?php echo htmlspecialchars($event['status']); ?></strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status change feedback
            const statusSelect = document.getElementById('status');
            const originalStatus = statusSelect.value;
            
            statusSelect.addEventListener('change', function() {
                const newStatus = this.value;
                const statusDisplay = document.getElementById('status-display');
                
                // Update the status display if it exists
                if (statusDisplay) {
                    statusDisplay.textContent = newStatus;
                    statusDisplay.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                        newStatus === 'Published' ? 'bg-green-100 text-green-800' :
                        newStatus === 'Draft' ? 'bg-gray-100 text-gray-800' :
                        'bg-red-100 text-red-800'
                    }`;
                }
                
                // Show immediate feedback
                showStatusChangeNotification(newStatus, originalStatus);
            });
            
            // Function to show status change notification
            function showStatusChangeNotification(newStatus, oldStatus) {
                if (newStatus === oldStatus) return;
                
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Status will be changed from "${oldStatus}" to "${newStatus}" when you save.</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }
            
            // Form submission enhancement
            const form = document.getElementById('eventForm');
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                submitBtn.disabled = true;
                
                // Re-enable after a delay (in case of errors)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            });
        });
    </script>
</body>
</html> 