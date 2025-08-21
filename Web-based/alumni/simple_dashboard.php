<?php
session_start();

// Simple session check without database
if (!isset($_SESSION['is_alumni']) || !$_SESSION['is_alumni'] || !isset($_SESSION['alumni_id'])) {
    header('Location: ../login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Alumni Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Alumni Dashboard</h1>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['alumni_name'] ?? 'Alumni'); ?></span>
                        <a href="../logout.php" class="ml-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome to Your Alumni Dashboard!</h2>
                    
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Your Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['alumni_name'] ?? 'N/A'); ?></p>
                                <p><strong>Alumni ID:</strong> <?php echo htmlspecialchars($_SESSION['alumni_alumni_id'] ?? 'N/A'); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['alumni_email'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p><strong>Student No:</strong> <?php echo htmlspecialchars($_SESSION['alumni_student_no'] ?? 'N/A'); ?></p>
                                <p><strong>Course:</strong> <?php echo htmlspecialchars($_SESSION['alumni_course'] ?? 'N/A'); ?></p>
                                <p><strong>Employment Status:</strong> <?php echo htmlspecialchars($_SESSION['alumni_employment_status'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">Session Debug Info</h3>
                        <pre class="bg-gray-100 p-4 rounded text-sm overflow-auto"><?php print_r($_SESSION); ?></pre>
                    </div>
                    
                    <div class="mt-6">
                        <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go to Full Dashboard</a>
                        <a href="../login.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 ml-2">Back to Login</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 