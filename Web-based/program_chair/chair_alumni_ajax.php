<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../admin/config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$school_year = isset($_POST['school_year']) ? $_POST['school_year'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Build query with filters
    $query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, s.ContactNo, m.SchoolYear, m.Semester, a.alumni_id
              FROM students s
              LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo
              LEFT JOIN listgradmain m ON l.MainID = m.id
              LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(DISTINCT s.StudentNo) as total
                    FROM students s
                    LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo
                    LEFT JOIN listgradmain m ON l.MainID = m.id
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
        $count_query .= " AND (s.StudentNo LIKE :search
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
        $count_query .= " AND m.SchoolYear = :school_year";
        $params[':school_year'] = $school_year;
    }

    // Add sorting
    $query .= " ORDER BY s.LastName ASC";

    // Get total records for pagination
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    // Add pagination
    $query .= " LIMIT :offset, :limit";

    // Execute main query
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $table_html = '';
    if (count($result) > 0) {
        foreach ($result as $row) {
            $table_html .= '<tr class="hover:bg-blue-50/50 transition-colors duration-150">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">';
            if (!empty($row['alumni_id'])) {
                $table_html .= htmlspecialchars($row['alumni_id']);
            } else {
                $table_html .= '<span class="text-yellow-600">Not Generated</span>';
            }
            $table_html .= '</td>';
            
            $fullName = array_filter([
                $row['LastName'],
                $row['FirstName'],
                $row['MiddleName']
            ]);
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars(implode(', ', $fullName)) . '</td>';
            
            $sex_class = $row['Sex'] === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">';
            $table_html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $sex_class . '">' . htmlspecialchars($row['Sex']) . '</span>';
            $table_html .= '</td>';
            
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['ContactNo']) . '</td>';
            
            if (isset($row['SchoolYear'])) {
                $sy = (int)$row['SchoolYear'];
                $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($sy . ' - ' . ($sy + 1)) . '</td>';
            } else {
                $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>';
            }
            
            if (isset($row['Semester'])) {
                if ($row['Semester'] == 1) {
                    $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">1st Semester</span></td>';
                } elseif ($row['Semester'] == 2) {
                    $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">2nd Semester</span></td>';
                } else {
                    $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['Semester']) . '</td>';
                }
            } else {
                $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>';
            }
            
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $table_html .= '<div class="flex items-center space-x-2">';
            $table_html .= '<button class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="View Details">';
            $table_html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $table_html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            $table_html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            $table_html .= '</svg>';
            $table_html .= '</button>';
            $table_html .= '<button class="text-yellow-600 hover:text-yellow-900 p-1 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">';
            $table_html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $table_html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
            $table_html .= '</svg>';
            $table_html .= '</button>';
            $table_html .= '</div>';
            $table_html .= '</td>';
            $table_html .= '</tr>';
        }
    } else {
        $table_html = '<tr>';
        $table_html .= '<td colspan="7" class="px-6 py-12 text-center">';
        $table_html .= '<div class="flex flex-col items-center justify-center text-gray-500">';
        $table_html .= '<svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $table_html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>';
        $table_html .= '</svg>';
        $table_html .= '<p class="text-lg font-medium text-gray-900 mb-1">No alumni records found</p>';
        $table_html .= '<p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>';
        $table_html .= '</div>';
        $table_html .= '</td>';
        $table_html .= '</tr>';
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        // Previous Button
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">';
            $pagination_html .= '<span class="sr-only">Previous</span>';
            $pagination_html .= '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">';
            $pagination_html .= '<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />';
            $pagination_html .= '</svg>';
            $pagination_html .= '</button>';
        }
        
        // Page Numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $current_page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ' . $active_class . '">' . $i . '</button>';
        }
        
        // Next Button
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">';
            $pagination_html .= '<span class="sr-only">Next</span>';
            $pagination_html .= '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">';
            $pagination_html .= '<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />';
            $pagination_html .= '</svg>';
            $pagination_html .= '</button>';
        }
    }

    // Generate pagination info
    $showing_from = $offset + 1;
    $showing_to = min($offset + $entries_per_page, $total_records);
    $pagination_info = "Showing <span class=\"font-medium\">$showing_from</span> to <span class=\"font-medium\">$showing_to</span> of <span class=\"font-medium\">$total_records</span> alumni records";

    // Generate complete HTML for the table container
    $html = '';
    
    // Results Summary
    $html .= '<div class="mb-4">';
    $html .= '<p class="text-sm text-gray-600">' . $pagination_info . '</p>';
    $html .= '</div>';
    
    // Table Card
    $html .= '<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">';
    $html .= '<div class="overflow-x-auto">';
    $html .= '<table class="min-w-full divide-y divide-gray-200">';
    $html .= '<thead class="bg-gray-50/80">';
    $html .= '<tr>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alumni ID</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Full Name</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sex</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact No</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School Year</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Semester</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody class="bg-white divide-y divide-gray-200">';
    $html .= $table_html;
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    
    // Pagination
    if ($total_pages > 1) {
        $html .= '<div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">';
        $html .= '<div class="flex items-center justify-between">';
        $html .= '<div class="flex-1 flex justify-between sm:hidden">';
        if ($current_page > 1) {
            $html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</button>';
        }
        if ($current_page < $total_pages) {
            $html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</button>';
        }
        $html .= '</div>';
        $html .= '<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">';
        $html .= '<div><p class="text-sm text-gray-700">' . $pagination_info . '</p></div>';
        $html .= '<div><nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">';
        $html .= $pagination_html;
        $html .= '</nav></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';

    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total_records' => $total_records,
        'current_page' => $current_page,
        'total_pages' => $total_pages
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

