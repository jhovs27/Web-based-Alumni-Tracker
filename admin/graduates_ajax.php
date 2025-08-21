<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$course_filter = isset($_POST['course']) ? $_POST['course'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Base query
    $query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, c.accro as Course, s.ContactNo, m.SchoolYear, m.Semester
              FROM students s 
              LEFT JOIN course c ON s.course = c.id 
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
        $params[':search'] = "%$search%";
    }

    // Add course filter
    if (!empty($course_filter)) {
        $query .= " AND c.id = :course";
        $params[':course'] = $course_filter;
    }

    // Get total records for pagination
    $total_query = "SELECT COUNT(DISTINCT s.StudentNo) as total FROM students s
                     LEFT JOIN course c ON s.course = c.id
                     LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo
                     LEFT JOIN listgradmain m ON l.MainID = m.id
                     WHERE 1=1";
    if (!empty($search)) {
        $total_query .= " AND (s.StudentNo LIKE :search 
                         OR s.LastName LIKE :search 
                         OR s.FirstName LIKE :search
                        OR s.MiddleName LIKE :search
                        OR s.ContactNo LIKE :search
                        OR m.SchoolYear LIKE :search
                        OR m.Semester LIKE :search)";
    }
    if (!empty($course_filter)) {
        $total_query .= " AND c.id = :course";
    }

    $stmt = $conn->prepare($total_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    // Add pagination to main query
    $query .= " ORDER BY s.LastName ASC LIMIT :offset, :limit";

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
            $fullName = array_filter([
                $row['LastName'],
                $row['FirstName'],
                $row['MiddleName']
            ]);
            $fullNameStr = htmlspecialchars(implode(', ', $fullName));
            
            $schoolYear = '';
            if (isset($row['SchoolYear'])) {
                $sy = (int)$row['SchoolYear'];
                $schoolYear = htmlspecialchars($sy . ' - ' . ($sy + 1));
            }
            
            $semester = '';
            if (isset($row['Semester'])) {
                if ($row['Semester'] == 1) $semester = 'FIRST SEMESTER';
                elseif ($row['Semester'] == 2) $semester = 'SECOND SEMESTER';
                else $semester = htmlspecialchars($row['Semester']);
            }
            
            $table_html .= '<tr class="hover:bg-gray-50 transition-colors duration-150">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['StudentNo']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">' . $fullNameStr . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['Sex']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['Course']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['ContactNo']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $schoolYear . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $semester . '</td>';
            $table_html .= '</tr>';
        }
    } else {
        $table_html = '<tr><td colspan="7" class="px-6 py-8 text-center">';
        $table_html .= '<div class="flex flex-col items-center justify-center text-gray-500">';
        $table_html .= '<i class="fas fa-search text-4xl mb-3 text-gray-300"></i>';
        $table_html .= '<p class="text-lg font-medium">No records found</p>';
        $table_html .= '<p class="text-sm mt-1">Try adjusting your search or filter to find what you\'re looking for.</p>';
        $table_html .= '</div></td></tr>';
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="px-3 py-1 bg-white border rounded-md hover:bg-gray-50 transition-colors duration-200">Previous</button>';
        }
        
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = $i === $current_page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="px-3 py-1 ' . $active_class . ' border rounded-md transition-colors duration-200">' . $i . '</button>';
        }
        
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="px-3 py-1 bg-white border rounded-md hover:bg-gray-50 transition-colors duration-200">Next</button>';
        }
    }

    // Generate pagination info
    $showing_from = $offset + 1;
    $showing_to = min($offset + $entries_per_page, $total_records);
    $pagination_info = "Showing $showing_from to $showing_to of $total_records entries";

    // Return JSON response
    echo json_encode([
        'success' => true,
        'table_html' => $table_html,
        'pagination_html' => $pagination_html,
        'pagination_info' => $pagination_info,
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
