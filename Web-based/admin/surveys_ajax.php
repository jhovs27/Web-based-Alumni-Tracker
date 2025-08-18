<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Handle search and filter
    $where = [];
    $params = [];

    if ($search) {
        $where[] = "(title LIKE :search OR target_alumni LIKE :search OR survey_type LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if ($filter && in_array($filter, ['published', 'draft'])) {
        $where[] = "status = :filter";
        $params[':filter'] = $filter;
    }

    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM survey $where_sql";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $entries_per_page);

    // Fetch paginated surveys
    $survey_sql = "SELECT * FROM survey $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $survey_stmt = $conn->prepare($survey_sql);
    foreach ($params as $k => $v) { 
        $survey_stmt->bindValue($k, $v); 
    }
    $survey_stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $survey_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $survey_stmt->execute();
    $surveys = $survey_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML using the original render function format
    ob_start();
    ?>
    <div class="overflow-x-auto">
        <table class="survey-table">
            <thead>
                <tr>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Title
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Target Alumni
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Type
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Status
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Created By
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Created At
                        </div>
                    </th>
                    <th>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                            Actions
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surveys)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-lg font-medium">No surveys found</p>
                                <p class="text-gray-400 text-sm mt-1">Create your first survey to get started!</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($surveys as $survey): ?>
                        <tr data-survey='<?php echo htmlspecialchars(json_encode($survey), ENT_QUOTES, "UTF-8"); ?>' class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($survey['title']); ?>
                            </td>
                            <td class="text-gray-600">
                                <?php echo htmlspecialchars($survey['target_alumni']); ?>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($survey['survey_type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = strtolower($survey['status']);
                                $badgeClass = '';
                                $text = ucfirst($status);
                                if ($status === 'published') {
                                    $badgeClass = 'bg-green-100 text-green-800 border-green-200';
                                } elseif ($status === 'draft') {
                                    $badgeClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                } else {
                                    $badgeClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeClass; ?>">
                                    <?php if ($status === 'published'): ?>
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php elseif ($status === 'draft'): ?>
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                    <?php echo $text; ?>
                                </span>
                            </td>
                            <td class="text-gray-600">
                                <?php echo htmlspecialchars($survey['created_by']); ?>
                            </td>
                            <td class="text-gray-500 text-sm">
                                <?php echo date('M d, Y g:i A', strtotime($survey['created_at'])); ?>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <button class="preview-btn inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors duration-200" title="Preview">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <a href="edit-survey.php?id=<?php echo $survey['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition-colors duration-200" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button class="delete-btn inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors duration-200" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    $table_html = ob_get_clean();

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = $i === $current_page ? 'active' : '';
            $pagination_html .= '<span class="' . $active_class . '" onclick="loadPage(' . $i . ')">' . $i . '</span>';
        }
    }

    // Return JSON response
    echo json_encode([
        'success' => true,
        'table_html' => $table_html,
        'pagination_html' => $pagination_html,
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
