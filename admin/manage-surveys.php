<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
include 'includes/breadcrumb.php';
require_once 'config/database.php';

// Handle search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
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











// Pagination logic
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total filtered surveys
$count_sql = "SELECT COUNT(*) FROM survey $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_surveys = $count_stmt->fetchColumn();
$total_pages = ceil($total_surveys / $per_page);

// Fetch paginated surveys
$survey_sql = "SELECT * FROM survey $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$survey_stmt = $conn->prepare($survey_sql);
foreach ($params as $k => $v) { $survey_stmt->bindValue($k, $v); }
$survey_stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$survey_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$survey_stmt->execute();
$surveys = $survey_stmt->fetchAll(PDO::FETCH_ASSOC);

function render_survey_table($surveys) {
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
    return ob_get_clean();
}

// AJAX: Only return the table if XHR
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo render_survey_table($surveys);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys - SLSU Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-container {
            padding: 2rem;
            margin-left: 0;
            width: 100%;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding-top: 10rem;
        }
        @media (min-width: 1024px) {
            .main-container {
                margin-left: 16rem;
                width: calc(100% - 16rem);
                padding-top: 8rem;
            }
        }

        .survey-table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        .survey-table {
            width: 100%;
            border-collapse: collapse;
        }
        .survey-table th {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        .survey-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .survey-table tbody tr:last-child td {
            border-bottom: none;
        }
        .controls-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .search-input {
            position: relative;
        }
        .search-input input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        .search-input input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-input .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }
        .filter-dropdown {
            position: relative;
        }
        .filter-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(59, 130, 246, 0.4);
        }
        .filter-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 110%;
            min-width: 180px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 10;
            padding: 0.5rem 0;
            border: 1px solid #e2e8f0;
        }
        .filter-menu.show {
            display: block;
        }
        .filter-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .filter-menu a:hover {
            background: #f3f4f6;
            color: #3b82f6;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .pagination a {
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .pagination a:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .pagination span.active {
            background: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }
        /* Modal Styles */
        #previewModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }
        #previewModal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .modal-content {
            background: white;
            border-radius: 1rem;
            max-width: 4xl;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f3f4f6;
            border: none;
            border-radius: 0.5rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
        }
        .close-btn:hover {
            background: #e5e7eb;
        }
        /* Loading overlay styles */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .loading-overlay.show {
            display: flex;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
                margin-left: 0;
                width: 100%;
            }
            .controls-container {
                padding: 1rem;
            }
            .survey-table th,
            .survey-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="main-container">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Surveys', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
                
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        Survey Management
                    </h1>
                    <p class="text-gray-600 mt-1">Manage and monitor all survey activities</p>
                </div>
                <a href="create-survey.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Survey
                </a>
            </div>
        </div>
        

        
        <!-- Controls -->
        <div class="controls-container">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="search-input flex-1 max-w-md">
                    <div class="relative">
                        <svg class="search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="searchbar" placeholder="Search surveys..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                                
                <div class="filter-dropdown">
                    <button type="button" class="filter-btn" onclick="toggleSurveyFilterMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/>
                        </svg>
                        Filter
                    </button>
                    <div class="filter-menu" id="surveyFilterMenu">
                        <a href="#" onclick="applyFilter('published')">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                Published
                            </div>
                        </a>
                        <a href="#" onclick="applyFilter('draft')">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                Draft
                            </div>
                        </a>
                        <a href="#" onclick="applyFilter('')">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                                All
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Survey Table -->
        <div class="survey-table-container">
            <!-- Loading overlay -->
            <div id="loadingOverlay" class="loading-overlay">
                <div class="loading-spinner"></div>
            </div>
            
            <div id="survey-table-container">
                <?php echo render_survey_table($surveys); ?>
            </div>
                        
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" id="paginationContainer">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="#" onclick="loadPage(<?php echo $i; ?>)"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Preview Modal -->
    <div id="previewModal">
        <div class="modal-content">
            <button class="close-btn" onclick="closePreview()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        // Global variables
        let currentSearch = '<?php echo htmlspecialchars($search); ?>';
        let currentFilter = '<?php echo htmlspecialchars($filter); ?>';
        let currentPage = <?php echo $page; ?>;
        let searchTimeout;

        // AJAX function to load table data
        function loadTableData(search = currentSearch, filter = currentFilter, page = currentPage) {
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('show');
            
            // Create form data
            const formData = new FormData();
            formData.append('search', search);
            formData.append('filter', filter);
            formData.append('page', page);
            
            // Make AJAX request
            fetch('surveys_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update table
                    document.getElementById('survey-table-container').innerHTML = data.table_html;
                    
                    // Update pagination
                    const paginationContainer = document.getElementById('paginationContainer');
                    if (paginationContainer && data.total_pages > 1) {
                        let paginationHTML = '';
                        for (let i = 1; i <= data.total_pages; i++) {
                            if (i === data.current_page) {
                                paginationHTML += `<span class="active">${i}</span>`;
                            } else {
                                paginationHTML += `<a href="#" onclick="loadPage(${i})">${i}</a>`;
                            }
                        }
                        paginationContainer.innerHTML = paginationHTML;
                        paginationContainer.style.display = 'flex';
                    } else if (paginationContainer) {
                        paginationContainer.style.display = 'none';
                    }
                    
                    // Update current values
                    currentSearch = search;
                    currentFilter = filter;
                    currentPage = page;
                    
                    // Reapply event listeners
                    reapplyListeners();
                } else {
                    console.error('Error loading data:', data.error);
                    showNotification('Error loading data. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                showNotification('Network error. Please check your connection.', 'error');
            })
            .finally(() => {
                // Hide loading overlay
                loadingOverlay.classList.remove('show');
            });
        }

        // Search functionality with debounce
        const searchbar = document.getElementById('searchbar');
        searchbar.addEventListener('input', function() {
            const searchValue = this.value;
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadTableData(searchValue, currentFilter, 1);
            }, 500);
        });

        // Filter functionality
        function applyFilter(filterValue) {
            document.getElementById('surveyFilterMenu').classList.remove('show');
            loadTableData(currentSearch, filterValue, 1);
        }

        // Pagination
        function loadPage(page) {
            loadTableData(currentSearch, currentFilter, page);
        }

        // Notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'error' ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : 
                type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' :
                'bg-blue-100 border-l-4 border-blue-500 text-blue-700'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="py-1">
                        <svg class="h-6 w-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            ${type === 'error' ? 
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' :
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                            }
                        </svg>
                    </div>
                    <div>
                        <p>${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-auto">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Preview Modal Logic
        function closePreview() {
            document.getElementById('previewModal').classList.remove('active');
        }

        function openPreview(survey) {
            const modal = document.getElementById('previewModal');
            const body = document.getElementById('modalBody');
                        
            let html = `<div class='survey-printable p-8'>`;
            html += `<div class='survey-header-print border-b-2 border-blue-100 pb-6 mb-6'>`;
            html += `<h2 class='text-3xl font-bold text-blue-700 mb-2'>${survey.title}</h2>`;
            html += `<div class='survey-meta-print flex flex-wrap gap-4 text-sm text-gray-600'>`;
            html += `<span class='flex items-center gap-1'><strong>Target Alumni:</strong> ${survey.target_alumni}</span>`;
            html += `<span class='flex items-center gap-1'><strong>Type:</strong> ${survey.survey_type}</span>`;
            html += `<span class='flex items-center gap-1'><strong>Status:</strong> ${survey.status.charAt(0).toUpperCase() + survey.status.slice(1)}</span>`;
            html += `</div>`;
                        
            if (survey.description) {
                html += `<div class='survey-desc-print mt-4 p-4 bg-gray-50 rounded-lg'><strong>Description:</strong> ${survey.description}</div>`;
            }
            html += `</div>`;
            if (survey.questions) {
                let questions = survey.questions;
                if (typeof questions === 'string') {
                    try { questions = JSON.parse(questions); } catch(e) { questions = []; }
                }
                                
                if (Array.isArray(questions) && questions.length > 0) {
                    html += `<div class='survey-questions-print space-y-6'>`;
                    questions.forEach((q, idx) => {
                        html += `<div class='survey-question-print bg-white border border-gray-200 rounded-lg p-4'>`;
                        html += `<div class='q-label text-lg font-semibold text-gray-800 mb-3'><span class='text-blue-600'>Q${idx+1}.</span> ${q.text || ''} ${q.required ? '<span class="text-red-500">*</span>' : ''}</div>`;
                                                
                        if (q.type === 'multiple') {
                            const choices = (q.choices || '').split(',').map(c => c.trim()).filter(Boolean);
                            html += `<ul class='q-multiple space-y-2 ml-4'>`;
                            choices.forEach(choice => {
                                html += `<li class='flex items-center gap-2'><input type='checkbox' disabled class='rounded'> <span>${choice}</span></li>`;
                            });
                            html += `</ul>`;
                        } else if (q.type === 'rating') {
                            html += `<div class='q-rating flex items-center gap-4'><span class='text-sm text-gray-600'>Rating scale:</span>`;
                            for (let i = 1; i <= 5; i++) {
                                html += `<label class='flex items-center gap-1'><input type='radio' disabled> <span>${i}</span></label>`;
                            }
                            html += `</div>`;
                        } else if (q.type === 'short') {
                            html += `<input type='text' class='q-short w-full p-3 border border-gray-300 rounded-lg' disabled placeholder='Short answer'>`;
                        } else if (q.type === 'paragraph') {
                            html += `<textarea class='q-paragraph w-full p-3 border border-gray-300 rounded-lg' rows='4' disabled placeholder='Paragraph answer'></textarea>`;
                        } else if (q.type === 'yesno') {
                            html += `<div class='q-yesno flex gap-4'><label class='flex items-center gap-2'><input type='radio' disabled> <span>Yes</span></label> <label class='flex items-center gap-2'><input type='radio' disabled> <span>No</span></label></div>`;
                        } else if (q.type === 'email') {
                            html += `<input type='email' class='q-short w-full p-3 border border-gray-300 rounded-lg' disabled placeholder='Email address'>`;
                        }
                        html += `</div>`;
                    });
                    html += `</div>`;
                }
            }
            html += `<div class='survey-footer-print border-t-2 border-blue-100 pt-6 mt-8 flex justify-between text-sm text-gray-600'>`;
            html += `<div><strong>Created By:</strong> ${survey.created_by}</div>`;
            html += `<div><strong>Created At:</strong> ${survey.created_at}</div>`;
            html += `</div>`;
            html += `</div>`;
            body.innerHTML = html;
            modal.classList.add('active');
        }

        // Button Event Listeners
        function addActionListeners() {
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.onclick = function() {
                    const tr = btn.closest('tr');
                    const survey = JSON.parse(tr.getAttribute('data-survey'));
                    openPreview(survey);
                };
            });
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = function() {
                    if (!confirm('Are you sure you want to delete this survey?')) return;
                                        
                    const tr = btn.closest('tr');
                    const survey = JSON.parse(tr.getAttribute('data-survey'));
                                        
                    fetch('delete-survey.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: survey.id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Survey deleted successfully!', 'success');
                            loadTableData(); // Reload current data
                        } else {
                            showNotification('Failed to delete survey.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while deleting the survey.', 'error');
                    });
                };
            });
        }

        function reapplyListeners() {
             addActionListeners();
         }

        document.addEventListener('DOMContentLoaded', addActionListeners);

        function toggleSurveyFilterMenu() {
            document.getElementById('surveyFilterMenu').classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.filter-dropdown')) {
                document.getElementById('surveyFilterMenu').classList.remove('show');
            }
        });

        // Close modal when clicking outside
        document.getElementById('previewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });

        // Session keepalive
        setInterval(function() {
            fetch('session_refresh.php', { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && data.redirect) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(() => {});
        }, 5 * 60 * 1000);
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
