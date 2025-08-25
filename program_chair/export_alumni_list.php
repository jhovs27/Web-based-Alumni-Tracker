<?php
session_start();
require_once '../admin/config/database.php';

// Check if user is logged in as program chair
if (!isset($_SESSION['chair_username'])) {
    header('Location: ../login.php');
    exit();
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="alumni_list_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
$entries = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;

// For debugging - you can uncomment this to see what parameters are being received
// error_log("Export parameters - search: $search, school_year: $school_year, entries: $entries");

// Get program chair's department/program from session or DB
$chair_program = $_SESSION['chair_program'] ?? null;
if (!$chair_program && isset($_SESSION['chair_username'])) {
    // Fallback: fetch from DB if not in session
    $stmt = $conn->prepare("SELECT program FROM program_chairs WHERE username = ? LIMIT 1");
    $stmt->execute([$_SESSION['chair_username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $chair_program = $row['program'] ?? null;
}

// Fetch the matching course id(s) for this program string
$course_ids = [];
if ($chair_program) {
    $stmt = $conn->prepare("SELECT id FROM course WHERE CONCAT(course_title, ' (', accro, ')') = ?");
    $stmt->execute([$chair_program]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $course_ids[] = $row['id'];
    }
}

// Build the query to fetch alumni data
$query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, s.ContactNo, m.SchoolYear, m.Semester,
    a.alumni_id              
    FROM students s               
    LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo              
    LEFT JOIN listgradmain m ON l.MainID = m.id
    LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo              
    WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " AND (s.StudentNo LIKE :search
                OR s.LastName LIKE :search
                OR s.FirstName LIKE :search
                OR s.MiddleName LIKE :search
                OR s.ContactNo LIKE :search
                OR m.SchoolYear LIKE :search
                OR m.Semester LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add school year filter
if (!empty($school_year)) {
    $query .= " AND m.SchoolYear = :school_year";
    $params[':school_year'] = $school_year;
}

// Add course filter if program chair has specific program
if (!empty($course_ids)) {
    $placeholders = [];
    foreach ($course_ids as $i => $course_id) {
        $placeholder = ":course_id_$i";
        $placeholders[] = $placeholder;
        $params[$placeholder] = $course_id;
    }
    $query .= " AND s.course IN (" . implode(',', $placeholders) . ")";
}

$query .= " ORDER BY s.LastName, s.FirstName, s.MiddleName";

// Execute the query
try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $alumniData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // For debugging, you can uncomment the next line to see the error
    // die("Database Error: " . $e->getMessage() . " Query: " . $query);
    $alumniData = [];
}

// Calculate statistics
$total_records = count($alumniData);
$male_count = count(array_filter($alumniData, function($row) { return $row['Sex'] === 'M'; }));
$female_count = count(array_filter($alumniData, function($row) { return $row['Sex'] === 'F'; }));
$registered_alumni = count(array_filter($alumniData, function($row) { return !empty($row['alumni_id']); }));
$unregistered_alumni = $total_records - $registered_alumni;

// Start Excel output
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000000; padding: 5px; }
        th { background-color: #4472C4; color: white; font-weight: bold; }
        .header { background-color: #D9E1F2; font-weight: bold; }
        .summary { background-color: #E7E6E6; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Report Header -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="10" style="text-align: center; font-size: 18px; font-weight: bold; padding: 10px;">
                SOUTHERN LEYTE STATE UNIVERSITY - HINUNANGAN CAMPUS
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px;">
                ALUMNI LIST REPORT
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-size: 12px; padding: 5px;">
                Generated on: <?php echo date('F d, Y h:i A'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-size: 12px; padding: 5px;">
                Program: <?php echo htmlspecialchars($chair_program ?? 'All Programs'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-size: 12px; padding: 5px;">
                Total Records: <?php echo $total_records; ?> | 
                Male: <?php echo $male_count; ?> | 
                Female: <?php echo $female_count; ?> | 
                Registered Alumni: <?php echo $registered_alumni; ?> | 
                Unregistered: <?php echo $unregistered_alumni; ?>
            </td>
        </tr>
    </table>

    <!-- Filter Information -->
    <?php if (!empty($search) || !empty($school_year)): ?>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr class="header">
            <td colspan="10" style="padding: 8px;">Applied Filters</td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>Search:</strong></td>
            <td colspan="9" style="padding: 5px;"><?php echo !empty($search) ? htmlspecialchars($search) : 'All'; ?></td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>School Year:</strong></td>
            <td colspan="9" style="padding: 5px;"><?php echo !empty($school_year) ? htmlspecialchars($school_year . ' - ' . ($school_year + 1)) : 'All Years'; ?></td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- Alumni Data Table -->
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Student No</th>
                <th style="width: 15%;">Last Name</th>
                <th style="width: 15%;">First Name</th>
                <th style="width: 10%;">M.I.</th>
                <th style="width: 8%;">Sex</th>
                <th style="width: 15%;">Contact No</th>
                <th style="width: 10%;">School Year</th>
                <th style="width: 10%;">Semester</th>
                <th style="width: 10%;">Alumni Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($alumniData) > 0): ?>
                <?php foreach ($alumniData as $index => $row): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['StudentNo'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['LastName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['MiddleName'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['Sex'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['ContactNo'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['SchoolYear'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['Semester'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;">
                            <?php 
                            if (!empty($row['alumni_id'])) {
                                echo 'Registered';
                            } else {
                                echo 'Not Registered';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; font-style: italic;">
                        No alumni records found matching the current filters
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Statistics -->
    <table style="width: 100%; margin-top: 20px;">
        <tr class="header">
            <th colspan="6" style="padding: 10px;">Alumni Statistics Summary</th>
        </tr>
        <tr class="summary">
            <td style="padding: 8px; text-align: center;"><strong>Total Records</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Male</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Female</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Registered Alumni</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Not Registered</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Registration Rate</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px; text-align: center;"><?php echo $total_records; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $male_count; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $female_count; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $registered_alumni; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $unregistered_alumni; ?></td>
            <td style="padding: 8px; text-align: center;">
                <?php echo $total_records > 0 ? round(($registered_alumni / $total_records) * 100, 2) : 0; ?>%
            </td>
        </tr>
    </table>

    <!-- Report Footer -->
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td colspan="10" style="text-align: center; font-size: 10px; padding: 10px; border-top: 2px solid #000;">
                This report was generated by the SLSU-HC Alumni Tracking System on <?php echo date('F d, Y \a\t h:i A'); ?><br>
                For questions or concerns, please contact the Program Chair's Office
            </td>
        </tr>
    </table>
</body>
</html>
