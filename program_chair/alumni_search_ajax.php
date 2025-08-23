<?php
session_start();
require_once '../admin/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['chair_name'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$school_year = isset($_POST['school_year']) ? trim($_POST['school_year']) : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

if ($entries_per_page < 1) $entries_per_page = 10;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    $query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, s.ContactNo, 
                     m.SchoolYear, m.Semester, a.alumni_id
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

    if (!empty($search)) {
        $search_condition = " AND (s.StudentNo LIKE :search
                                OR s.LastName LIKE :search
                                OR s.FirstName LIKE :search
                                OR s.MiddleName LIKE :search
                                OR s.ContactNo LIKE :search
                                OR m.SchoolYear LIKE :search
                                OR m.Semester LIKE :search)";
        $query .= $search_condition;
        $count_query .= $search_condition;
        $params[':search'] = "%$search%";
    }

    if (!empty($school_year)) {
        $year_condition = " AND m.SchoolYear = :school_year";
        $query .= $year_condition;
        $count_query .= $year_condition;
        $params[':school_year'] = $school_year;
    }

    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    $query .= " ORDER BY s.LastName ASC LIMIT :offset, :limit";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $table_rows = '';
    if (count($result) > 0) {
        foreach ($result as $row) {
            $table_rows .= '<tr class="hover:bg-blue-50/50 transition-colors duration-150">';
            
            $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">';
            if (!empty($row['alumni_id'])) {
                $table_rows .= htmlspecialchars($row['alumni_id']);
            } else {
                $table_rows .= '<span class="text-yellow-600">Not Generated</span>';
            }
            $table_rows .= '</td>';
            
            $fullName = array_filter([$row['LastName'], $row['FirstName'], $row['MiddleName']]);
            $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . 
                          htmlspecialchars(implode(', ', $fullName)) . '</td>';
            
            $sex_class = $row['Sex'] === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800';
            $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">';
            $table_rows .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $sex_class . '">' . 
                          htmlspecialchars($row['Sex']) . '</span>';
            $table_rows .= '</td>';
            
            $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . 
                          htmlspecialchars($row['ContactNo']) . '</td>';
            
            if (isset($row['SchoolYear']) && !empty($row['SchoolYear'])) {
                $sy = (int)$row['SchoolYear'];
                $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . 
                              htmlspecialchars($sy . ' - ' . ($sy + 1)) . '</td>';
            } else {
                $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>';
            }
            
            if (isset($row['Semester']) && !empty($row['Semester'])) {
                if ($row['Semester'] == 1) {
                    $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">';
                    $table_rows .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">1st Semester</span>';
                    $table_rows .= '</td>';
                } elseif ($row['Semester'] == 2) {
                    $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">';
                    $table_rows .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">2nd Semester</span>';
                    $table_rows .= '</td>';
                } else {
                    $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . 
                                  htmlspecialchars($row['Semester']) . '</td>';
                }
            } else {
                $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>';
            }
            
            $table_rows .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $table_rows .= '<div class="flex items-center space-x-2">';
            $table_rows .= '<button class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="View Details">';
            $table_rows .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $table_rows .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            $table_rows .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            $table_rows .= '</svg>';
            $table_rows .= '</button>';
            $table_rows .= '<button class="text-yellow-600 hover:text-yellow-900 p-1 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">';
            $table_rows .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $table_rows .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
            $table_rows .= '</svg>';
            $table_rows .= '</button>';
            $table_rows .= '</div>';
            $table_rows .= '</td>';
            
            $table_rows .= '</tr>';
        }
    } else {
        $table_rows = '<tr><td colspan="7" class="px-6 py-12 text-center">';
        $table_rows .= '<div class="flex flex-col items-center justify-center text-gray-500">';
        $table_rows .= '<svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $table_rows .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>';
        $table_rows .= '</svg>';
        $table_rows .= '<p class="text-lg font-medium text-gray-900 mb-1">No alumni records found</p>';
        $table_rows .= '<p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>';
        $table_rows .= '</div></td></tr>';
    }

    $pagination_html = '';
    if ($total_pages > 1) {
        $pagination_html .= '<div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">';
        $pagination_html .= '<div class="flex items-center justify-between">';
        $pagination_html .= '<div class="flex-1 flex justify-between sm:hidden">';
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</button>';
        }
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</button>';
        }
        $pagination_html .= '</div>';
        $pagination_html .= '<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">';
        $showing_from = $offset + 1;
        $showing_to = min($offset + $entries_per_page, $total_records);
        $pagination_html .= '<div><p class="text-sm text-gray-700">Showing <span class="font-medium">' . $showing_from . '</span> to <span class="font-medium">' . $showing_to . '</span> of <span class="font-medium">' . $total_records . '</span> results</p></div>';
        $pagination_html .= '<div><nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">';
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"><span class="sr-only">Previous</span><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></button>';
        }
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $current_page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ' . $active_class . '">' . $i . '</button>';
        }
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"><span class="sr-only">Next</span><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></button>';
        }
        $pagination_html .= '</nav></div>';
        $pagination_html .= '</div></div></div>';
    }

    $showing_from = $offset + 1;
    $showing_to = min($offset + $entries_per_page, $total_records);
    
    $html = '<div class="mb-4"><p class="text-sm text-gray-600">Showing ' . $showing_from . ' to ' . $showing_to . ' of ' . $total_records . ' alumni records</p></div>';
    $html .= '<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">';
    $html .= '<div class="overflow-x-auto">';
    $html .= '<table class="min-w-full divide-y divide-gray-200">';
    $html .= '<thead class="bg-gray-50/80"><tr>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alumni ID</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Full Name</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sex</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact No</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School Year</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Semester</th>';
    $html .= '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>';
    $html .= '</tr></thead>';
    $html .= '<tbody class="bg-white divide-y divide-gray-200">';
    $html .= $table_rows;
    $html .= '</tbody></table></div>';
    $html .= $pagination_html;
    $html .= '</div>';

    echo json_encode([
        'success' => true,
        'html' => $html,
        'total_records' => $total_records,
        'current_page' => $current_page,
        'total_pages' => $total_pages
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
