<?php
// Load session, DB, and alumni data
include 'includes/header.php';
?>

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content lg:ml-72 pt-16 min-h-screen">
    <div class="p-6">
        <?php
        // Build profile photo URL
        $alumni_first_name = isset($alumni['first_name']) ? trim($alumni['first_name']) : '';
        $alumni_last_name = isset($alumni['last_name']) ? trim($alumni['last_name']) : '';
        $alumni_full_name = trim($alumni_first_name . ' ' . $alumni_last_name);
        $profile_photo = $alumni['profile_photo_path'] ?? '';
        if (!empty($profile_photo) && file_exists('../admin/uploads/profile_photos/' . $profile_photo)) {
            $photo_url = '../admin/uploads/profile_photos/' . htmlspecialchars($profile_photo);
        } else {
            $photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($alumni_full_name ?: 'Alumni') . '&background=0D8ABC&color=fff';
        }

        // Optional fields
        $program = $alumni['program'] ?? ($alumni['course'] ?? '');
        $year_graduated = $alumni['year_graduated'] ?? '';
        $employment_status = $alumni['employment_status'] ?? '';

        // Filter which fields to show
        $blacklist = [
            'password', 'password_hash', 'pwd', 'pass', 'salt', 'token', 'reset_token', 'reset_token_expires',
            'remember_token', 'verification_code', 'otp', 'session_id', 'session_token', 'csrf_token',
            'profile_photo_path', // shown separately
        ];

        // Nicely formatted labels
        $labels = [
            'alumni_id' => 'Alumni ID',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'contact_number' => 'Contact Number',
            'phone' => 'Phone',
            'address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'zip' => 'ZIP Code',
            'birthdate' => 'Birthdate',
            'gender' => 'Gender',
            'civil_status' => 'Civil Status',
            'student_no' => 'Student Number',
            'program' => 'Program',
            'course' => 'Course',
            'major' => 'Major',
            'department' => 'Department',
            'year_graduated' => 'Year Graduated',
            'employment_status' => 'Employment Status',
            'company' => 'Company',
            'job_title' => 'Job Title',
            'industry' => 'Industry',
            'created_at' => 'Registered At',
            'updated_at' => 'Last Updated',
        ];

        // Build a clean key=>value list from $alumni
        $details = [];
        foreach ($alumni as $key => $value) {
            if ($value === null || $value === '') continue;
            if (in_array($key, $blacklist, true)) continue;

            // Skip obviously internal numeric keys if any
            if (is_int($key)) continue;

            // Normalize boolean
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            // Format timestamps if they look like dates
            if (in_array($key, ['created_at', 'updated_at', 'birthdate'], true)) {
                $ts = strtotime((string)$value);
                if ($ts) $value = date('M d, Y', $ts);
            }

            $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
            $details[$label] = $value;
        }
        ?>

        <!-- Profile Header -->
        <div class="mb-6">
            <div class="relative rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 via-sky-400/20 to-purple-500/20"></div>
                <div class="relative bg-white border border-gray-100 rounded-2xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex p-1 rounded-full bg-gradient-to-tr from-indigo-400 via-fuchsia-400 to-emerald-400">
                                <img src="<?php echo $photo_url; ?>" alt="Profile Photo" class="h-20 w-20 md:h-24 md:w-24 rounded-full object-cover border-2 border-white">
                            </span>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($alumni_full_name ?: 'Alumni'); ?></h1>
                                <div class="flex flex-wrap items-center gap-3 mt-1 text-sm">
                                    <?php if (!empty($program)): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <i class="fas fa-graduation-cap mr-2"></i><?php echo htmlspecialchars($program); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($year_graduated)): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-calendar-check mr-2"></i>Class of <?php echo htmlspecialchars($year_graduated); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($employment_status)): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="fas fa-briefcase mr-2"></i><?php echo htmlspecialchars(ucwords($employment_status)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="settings.php" class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors duration-200 shadow">
                                <i class="fas fa-user-edit mr-2"></i> Edit Profile
                            </a>
                            <a href="../logout.php" class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">Profile Details</h2>
                        <p class="text-sm text-gray-500">Information provided during registration</p>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($details)): ?>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                                <?php foreach ($details as $label => $value): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($label); ?></dt>
                                        <dd class="mt-1 text-sm text-gray-900 break-words"><?php echo htmlspecialchars((string)$value); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php else: ?>
                            <p class="text-sm text-gray-600">No profile details available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Contact Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Contact</h3>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <?php if (!empty($alumni['email'])): ?>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-envelope text-indigo-600 mt-0.5"></i>
                                <a href="mailto:<?php echo htmlspecialchars($alumni['email']); ?>" class="text-gray-700 hover:text-indigo-700 transition-colors duration-150"><?php echo htmlspecialchars($alumni['email']); ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($alumni['contact_number'])): ?>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-phone text-indigo-600 mt-0.5"></i>
                                <a href="tel:<?php echo htmlspecialchars($alumni['contact_number']); ?>" class="text-gray-700 hover:text-indigo-700 transition-colors duration-150"><?php echo htmlspecialchars($alumni['contact_number']); ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($alumni['address'])): ?>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-map-marker-alt text-indigo-600 mt-0.5"></i>
                                <p class="text-gray-700"><?php echo htmlspecialchars($alumni['address']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-3 text-sm">
                        <a href="events.php" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100">
                            <i class="fas fa-calendar-alt mr-2"></i> Events
                        </a>
                        <a href="jobs.php" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100">
                            <i class="fas fa-briefcase mr-2"></i> Jobs
                        </a>
                        <a href="surveys.php" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100">
                            <i class="fas fa-poll mr-2"></i> Surveys
                        </a>
                        <a href="payments.php" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100">
                            <i class="fas fa-credit-card mr-2"></i> Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>


