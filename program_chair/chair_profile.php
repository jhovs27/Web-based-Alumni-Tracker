<?php
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    header('Location: ../login.php');
    exit();
}

require_once '../admin/config/database.php';

// --- Handle Profile Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $program = $_POST['program'];
        $about_me = trim($_POST['about_me'] ?? '');
        $chair_id = $_SESSION['chair_id'];

        // Fetch designation from department
        $designation = '';
        $dept_desc = '';
        if ($program) {
            $stmt = $conn->prepare("SELECT Designation, Description FROM department WHERE id = ?");
            $stmt->execute([$program]);
            $dept_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $designation = $dept_row['Designation'] ?? '';
            $dept_desc = $dept_row['Description'] ?? '';
        }

        // Validation
        if (empty($full_name) || empty($email) || empty($username) || empty($program)) {
            $_SESSION['error'] = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
        } else {
            // Check if email or username already exists (excluding current chair)
            $stmt = $conn->prepare("SELECT id FROM program_chairs WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $chair_id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email or username already exists.";
            } else {
                // Handle profile picture upload
                $profile_picture = $_POST['current_profile_picture'] ?? '';
                $upload_dir = '../admin/chair-uploads/';
                
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['profile_picture'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($file['type'], $allowed_types)) {
                        $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                    } elseif ($file['size'] > $max_size) {
                        $_SESSION['error'] = "File size must be less than 5MB.";
                    } else {
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $file_extension;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            // Delete old profile picture if exists
                            if ($profile_picture && file_exists($upload_dir . $profile_picture)) {
                                unlink($upload_dir . $profile_picture);
                            }
                            $profile_picture = $filename;
                        } else {
                            $_SESSION['error'] = "Failed to upload profile picture.";
                        }
                    }
                }

                if (!isset($_SESSION['error'])) {
                    // Update database
                    $stmt = $conn->prepare("UPDATE program_chairs SET full_name = ?, email = ?, username = ?, program = ?, Designation = ?, profile_picture = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $username, $program, $designation, $profile_picture, $chair_id]);
                    
                    // Update session variables
                    $_SESSION['chair_name'] = $full_name;
                    $_SESSION['chair_email'] = $email;
                    $_SESSION['chair_program'] = $program;
                    $_SESSION['chair_designation'] = $designation;
                    $_SESSION['profile_photo_path'] = $profile_picture;
                    $_SESSION['chair_username'] = $username;
                    $_SESSION['about_me'] = $about_me;
                    $_SESSION['success'] = "Profile updated successfully!";
                }
            }
        }
    }

    // --- Handle Password Change ---
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $chair_id = $_SESSION['chair_id'];

        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM program_chairs WHERE id = ?");
        $stmt->execute([$chair_id]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current_password, $hash)) {
            $_SESSION['error'] = "Current password is incorrect.";
        } elseif (strlen($new_password) < 8) {
            $_SESSION['error'] = "New password must be at least 8 characters.";
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New passwords do not match.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE program_chairs SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $chair_id]);
            $_SESSION['success'] = "Password changed successfully!";
        }
    }
}

// Fetch departments for dropdown
$departments = [];
try {
    $stmt = $conn->query("SELECT id, DepartmentName, Description, Designation FROM department WHERE Active = 1 ORDER BY DepartmentName ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $departments[] = [
            'id' => $row['id'],
            'label' => $row['DepartmentName'] . ' - ' . $row['Description'],
            'designation' => $row['Designation']
        ];
    }
} catch (PDOException $e) {}

// Get the logged-in chair's username or email from session
$username = $_SESSION['chair_username'] ?? null;
$email = $_SESSION['chair_email'] ?? null;

// Fetch the full chair record
$chair = null;
if ($username || $email) {
    $stmt = $conn->prepare("SELECT pc.*, d.DepartmentName, d.Description FROM program_chairs pc LEFT JOIN department d ON pc.program = d.id WHERE pc.username = ? OR pc.email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    $chair = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fallback to session if DB fetch fails
if (!$chair) {
    $chair = [
        'full_name' => $_SESSION['chair_name'] ?? 'Program Chair',
        'Designation' => $_SESSION['chair_designation'] ?? '',
        'profile_picture' => $_SESSION['profile_photo_path'] ?? '',
        'DepartmentName' => $_SESSION['chair_program'] ?? '',
        'email' => $_SESSION['chair_email'] ?? '',
        'username' => $_SESSION['chair_username'] ?? '',
        'status' => 'Active',
        'created_at' => '',
        'program' => $_SESSION['chair_program'] ?? '',
        'about_me' => $_SESSION['about_me'] ?? 'No bio yet. Click edit to add your bio.',
        'Description' => '',
    ];
}

if (!isset($chair['about_me'])) {
    $chair['about_me'] = $_SESSION['about_me'] ?? 'No bio yet. Click edit to add your bio.';
}

$profile_picture = $chair['profile_picture'] ?? '';
$profile_picture_url = (!empty($profile_picture) && strpos($profile_picture, 'ui-avatars.com') === false)
    ? '../admin/chair-uploads/' . htmlspecialchars($profile_picture)
    : (empty($profile_picture) ? 'https://ui-avatars.com/api/?name=' . urlencode($chair['full_name']) . '&background=0D8ABC&color=fff' : $profile_picture);

$member_since = !empty($chair['created_at']) ? date('F d, Y', strtotime($chair['created_at'])) : '-';
$last_updated = $member_since; // For demo, use created_at
$dept_desc = $chair['Description'] ?? '';

// Quick stats
$alumni_count = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM alumni WHERE program = ?");
    $stmt->execute([$chair['program']]);
    $alumni_count = $stmt->fetchColumn();
} catch (PDOException $e) {}

$events_managed = 0; // Placeholder
$years_as_chair = $member_since !== '-' ? (date('Y') - date('Y', strtotime($chair['created_at']))) : 0;

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chair Profile - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <nav class="flex items-center space-x-2 text-sm text-gray-600 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 shadow-sm border border-blue-100">
                    <?php foreach (
                        isset($breadcrumbs) ? $breadcrumbs : [] as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        <?php endif; ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <i class="fas <?php echo htmlspecialchars($breadcrumb['icon']); ?> text-xs"></i>
                                <span><?php echo htmlspecialchars($breadcrumb['label']); ?></span>
                            </a>
                        <?php else: ?>
                            <span class="flex items-center space-x-1 text-gray-800 font-medium">
                                <i class="fas <?php echo htmlspecialchars($breadcrumb['icon']); ?> text-xs"></i>
                                <span><?php echo htmlspecialchars($breadcrumb['label']); ?></span>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Profile Header -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 mb-8 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-32 relative">
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>
                <div class="relative px-6 pb-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-end -mt-16 relative z-10">
                        <div class="relative">
                            <img src="<?php echo $profile_picture_url; ?>" alt="Profile" class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg bg-white">
                            <span class="absolute bottom-2 right-2 inline-block w-6 h-6 rounded-full border-2 border-white flex items-center justify-center <?php echo ($chair['status'] === 'active' || $chair['status'] === 'Active') ? 'bg-green-400' : 'bg-gray-400'; ?>">
                                <i class="fas fa-circle text-xs text-white"></i>
                            </span>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-6 text-center sm:text-left flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 flex items-center justify-center sm:justify-start gap-2">
                                <?php echo htmlspecialchars($chair['full_name']); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium <?php echo ($chair['status'] === 'active' || $chair['status'] === 'Active') ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600'; ?>">
                                    <i class="fas fa-user-shield mr-1"></i> <?php echo ucfirst($chair['status']); ?>
                                </span>
                            </h1>
                            <p class="text-white-400 font-medium text-lg mt-1"><?php echo htmlspecialchars($chair['Designation']); ?></p>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($chair['DepartmentName'] ?? $chair['program']); ?></p>
                            <p class="text-gray-500 text-sm mt-1">Member since <?php echo $member_since; ?></p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex gap-3">
                            <button onclick="openEditModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center gap-2">
                                <i class="fas fa-edit"></i>
                                <span class="hidden sm:inline">Edit Profile</span>
                            </button>
                            <button onclick="openPasswordModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center gap-2">
                                <i class="fas fa-key"></i>
                                <span class="hidden sm:inline">Change Password</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-blue-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Alumni in Program</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($alumni_count); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-green-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Events Managed</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($events_managed); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-purple-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg">
                            <i class="fas fa-user-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Years as Chair</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $years_as_chair; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- About Me -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-edit text-blue-500"></i>
                        About Me
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 min-h-[120px]">
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($chair['about_me'])); ?></p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user text-blue-500"></i>
                        Personal Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Full Name</label>
                            <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($chair['full_name']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Username</label>
                            <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($chair['username']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900 font-medium flex items-center gap-2">
                                <i class="fas fa-envelope text-blue-400"></i>
                                <?php echo htmlspecialchars($chair['email']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Department Information -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-building text-blue-500"></i>
                        Department Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Program/Department</label>
                            <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($chair['DepartmentName'] ?? $chair['program']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Designation</label>
                            <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($chair['Designation']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Description</label>
                            <p class="text-gray-700 text-sm"><?php echo htmlspecialchars($dept_desc); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="mt-8 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-blue-500"></i>
                    Activity Log
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-sign-in-alt text-green-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Last Login</p>
                                <p class="text-xs text-gray-500">Not tracked</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-edit text-blue-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Profile Updated</p>
                                <p class="text-xs text-gray-500"><?php echo $last_updated; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-check text-purple-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Events Managed</p>
                                <p class="text-xs text-gray-500"><?php echo $events_managed; ?> events</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative border border-gray-200 max-h-[90vh] overflow-y-auto">
            <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-user-edit text-blue-600 text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Edit Profile</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($chair['profile_picture']); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($chair['full_name']); ?>" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($chair['email']); ?>" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($chair['username']); ?>" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program *</label>
                        <select name="program" id="edit_program_select" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                onchange="updateEditDesignation()">
                            <option value="">Select Program</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        data-designation="<?php echo htmlspecialchars($dept['designation']); ?>" 
                                        <?php echo ($chair['program'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Designation</label>
                    <input type="text" name="designation" id="edit_designation_input" 
                           value="<?php echo htmlspecialchars($chair['Designation']); ?>" readonly 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-2">Max size: 5MB. Formats: JPG, PNG, GIF</p>
                    <?php if ($chair['profile_picture']): ?>
                        <div class="mt-3">
                            <img src="../admin/chair-uploads/<?php echo htmlspecialchars($chair['profile_picture']); ?>" 
                                 alt="Current Profile" class="h-16 w-16 rounded-full object-cover border-2 border-gray-200">
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">About Me</label>
                    <textarea name="about_me" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                              placeholder="Write a short bio..."><?php echo htmlspecialchars($chair['about_me']); ?></textarea>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 relative border border-gray-200">
            <button onclick="closePasswordModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 rounded-full">
                    <i class="fas fa-key text-indigo-600 text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Change Password</h2>
            </div>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password *</label>
                    <input type="password" name="current_password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                    <input type="password" name="new_password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password *</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closePasswordModal()" 
                            class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Sidebar toggle functionality
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleSidebar && sidebar && overlay) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Modal functions
    function openEditModal() {
        document.getElementById('editProfileModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editProfileModal').classList.add('hidden');
    }

    function openPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }

    // Designation autofill for edit modal
    function updateEditDesignation() {
        var select = document.getElementById('edit_program_select');
        var designationInput = document.getElementById('edit_designation_input');
        var selected = select.options[select.selectedIndex];
        var designation = selected.getAttribute('data-designation') || '';
        designationInput.value = designation;
    }

    // Autofill on page load
    window.addEventListener('DOMContentLoaded', function() {
        updateEditDesignation();
    });

    // Session refresh mechanism
    setInterval(function() {
        fetch('session_refresh.php', { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                if (!data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(() => {});
    }, 5 * 60 * 1000); // every 5 minutes

    // Close mobile menu when clicking on links
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) { // lg breakpoint
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
