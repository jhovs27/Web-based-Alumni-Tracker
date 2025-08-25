<?php
// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="employment_status_report_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';

// Sample data (in real implementation, this would come from database)
$alumniData = [
    ['id' => 1, 'last' => 'ABING', 'first' => 'JHOANNA', 'mi' => 'M', 'sex' => 'F', 'status' => 'Employed', 'company' => 'LGU - Hinunangan - Admin Aide I', 'doc' => 'Company ID'],
    ['id' => 2, 'last' => 'BAGOOD', 'first' => 'QUENNIE JANE', 'mi' => 'M', 'sex' => 'F', 'status' => 'Employed', 'company' => "Excellent People's Multi Purpose Cooperative", 'doc' => 'Company ID'],
    ['id' => 3, 'last' => 'BARRAMEDA', 'first' => 'MHAILANE', 'mi' => 'H', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 4, 'last' => 'BASANEZ', 'first' => 'JOHN CARLOS', 'mi' => 'N', 'sex' => 'M', 'status' => 'Employed', 'company' => 'Alorica Cebu', 'doc' => 'Company ID'],
    ['id' => 5, 'last' => 'BAYANO', 'first' => 'AYLWIN', 'mi' => 'M', 'sex' => 'M', 'status' => 'Employed', 'company' => 'Self-employed - Businessman', 'doc' => ''],
    ['id' => 6, 'last' => 'BONADOR', 'first' => 'MIRABELLE', 'mi' => 'P', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 7, 'last' => 'CAMANTIGUE', 'first' => 'JULIUS', 'mi' => 'P', 'sex' => 'M', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 8, 'last' => 'CATABAS', 'first' => 'JUSTINE NYLE', 'mi' => 'O', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Catabas Printing Services', 'doc' => 'No ID Issued'],
    ['id' => 9, 'last' => 'COQUILLA', 'first' => 'HANNAH', 'mi' => 'R', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 10, 'last' => 'DAVID', 'first' => 'ZYRA', 'mi' => 'E', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 11, 'last' => 'DELA CRUZ', 'first' => 'MARIA', 'mi' => 'A', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Tech Solutions Inc.', 'doc' => 'Company ID'],
    ['id' => 12, 'last' => 'ESTRADA', 'first' => 'JUAN', 'mi' => 'B', 'sex' => 'M', 'status' => 'Unemployed', 'company' => '', 'doc' => ''],
    ['id' => 13, 'last' => 'FLORES', 'first' => 'ANA', 'mi' => 'C', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Global Services', 'doc' => 'Employment Certificate'],
    ['id' => 14, 'last' => 'GARCIA', 'first' => 'PEDRO', 'mi' => 'D', 'sex' => 'M', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 15, 'last' => 'HERNANDEZ', 'first' => 'LUCIA', 'mi' => 'E', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Local Business', 'doc' => 'Business Permit']
];

// Filter data based on search and status
$filteredData = array_filter($alumniData, function($row) use ($search, $status, $school_year) {
    $matchesSearch = empty($search) || 
        stripos($row['last'], $search) !== false ||
        stripos($row['first'], $search) !== false ||
        stripos($row['mi'], $search) !== false ||
        stripos($row['sex'], $search) !== false ||
        stripos($row['status'], $search) !== false ||
        stripos($row['company'], $search) !== false ||
        stripos($row['doc'], $search) !== false;
    
    $matchesStatus = empty($status) || $row['status'] === $status;
    
    $matchesSchoolYear = empty($school_year) || (isset($row['school_year']) && $row['school_year'] == $school_year);
    
    return $matchesSearch && $matchesStatus && $matchesSchoolYear;
});

// Calculate statistics
$employed_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Employed'; }));
$unemployed_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Unemployed'; }));
$not_tracked_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Not Tracked'; }));
$total_records = count($alumniData);
$employment_rate = $total_records > 0 ? round(($employed_count / $total_records) * 100, 2) : 0;

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
            <td colspan="8" style="text-align: center; font-size: 18px; font-weight: bold; padding: 10px;">
                SOUTHERN LEYTE STATE UNIVERSITY - HINUNANGAN CAMPUS
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px;">
                EMPLOYMENT STATUS REPORT
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 12px; padding: 5px;">
                Generated on: <?php echo date('F d, Y h:i A'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 12px; padding: 5px;">
                Total Records: <?php echo count($filteredData); ?> | 
                Employed: <?php echo $employed_count; ?> | 
                Unemployed: <?php echo $unemployed_count; ?> | 
                Not Tracked: <?php echo $not_tracked_count; ?> | 
                Employment Rate: <?php echo $employment_rate; ?>%
            </td>
        </tr>
    </table>

    <!-- Filter Information -->
    <?php if (!empty($search) || !empty($status) || !empty($school_year)): ?>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr class="header">
            <td colspan="8" style="padding: 8px;">Applied Filters</td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>Search:</strong></td>
            <td colspan="7" style="padding: 5px;"><?php echo !empty($search) ? htmlspecialchars($search) : 'All'; ?></td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>Status:</strong></td>
            <td colspan="7" style="padding: 5px;"><?php echo !empty($status) ? htmlspecialchars($status) : 'All Status'; ?></td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>School Year:</strong></td>
            <td colspan="7" style="padding: 5px;"><?php echo !empty($school_year) ? htmlspecialchars($school_year) : 'All Years'; ?></td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- Employment Data Table -->
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Last Name</th>
                <th style="width: 15%;">First Name</th>
                <th style="width: 8%;">M.I.</th>
                <th style="width: 8%;">Sex</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 25%;">Company/Business</th>
                <th style="width: 12%;">Documents</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($filteredData) > 0): ?>
                <?php foreach ($filteredData as $index => $row): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['last']); ?></td>
                        <td><?php echo htmlspecialchars($row['first']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['mi']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['sex']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['company'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['doc'] ?: 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; font-style: italic;">
                        No employment records found matching the current filters
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Statistics -->
    <table style="width: 100%; margin-top: 20px;">
        <tr class="header">
            <th colspan="5" style="padding: 10px;">Employment Statistics Summary</th>
        </tr>
        <tr class="summary">
            <td style="padding: 8px; text-align: center;"><strong>Total Records</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Employed</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Unemployed</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Not Tracked</strong></td>
            <td style="padding: 8px; text-align: center;"><strong>Employment Rate</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px; text-align: center;"><?php echo count($filteredData); ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $employed_count; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $unemployed_count; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $not_tracked_count; ?></td>
            <td style="padding: 8px; text-align: center;"><?php echo $employment_rate; ?>%</td>
        </tr>
    </table>

    <!-- Report Footer -->
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td colspan="8" style="text-align: center; font-size: 10px; padding: 10px; border-top: 2px solid #000;">
                This report was generated by the SLSU-HC Alumni Tracking System on <?php echo date('F d, Y \a\t h:i A'); ?><br>
                For questions or concerns, please contact the Program Chair's Office
            </td>
        </tr>
    </table>
</body>
</html>