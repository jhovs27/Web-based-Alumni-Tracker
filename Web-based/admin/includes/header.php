<?php
// Include session configuration
require_once __DIR__ . '/session_config.php';

// Check if admin session is valid
if (!isAdminSessionValid()) {
    // Redirect to login if session is invalid
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
                width: 100% !important;
                transition: all 0.3s ease-in-out;
            }
            
            .content-wrapper {
                padding-top: 4rem !important;
                transition: all 0.3s ease-in-out;
            }
            
            /* Enhanced mobile table styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table-responsive table {
                min-width: 800px;
            }
            
            /* Mobile form improvements */
            .form-grid-mobile {
                grid-template-columns: 1fr !important;
            }
            
            /* Mobile button improvements */
            .btn-mobile {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            /* Mobile modal improvements */
            .modal-mobile {
                padding: 0.5rem !important;
            }
            
            .modal-content-mobile {
                width: 95% !important;
                margin: 1rem auto !important;
                max-height: 90vh;
                overflow-y: auto;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -1rem;
                padding: 0 1rem;
            }

            .table-container table {
                min-width: 640px;
            }

            .filter-section {
                flex-direction: column;
                gap: 1rem;
            }

            .filter-section > div {
                width: 100%;
            }

            .pagination-section {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .action-buttons {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .modal-content {
                width: 95% !important;
                margin: 1rem !important;
                max-height: 90vh;
                overflow-y: auto;
            }

            /* Enhanced Mobile Styles */
            .card {
                margin: 0 -1rem;
                border-radius: 0;
            }

            .search-bar {
                width: 100%;
            }

            .search-bar input {
                width: 100%;
            }

            .table-header {
                position: sticky;
                top: 0;
                background: white;
                z-index: 10;
            }

            .table-cell {
                white-space: nowrap;
            }

            .table-actions {
                position: sticky;
                right: 0;
                background: white;
                z-index: 5;
            }

            .pagination-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .pagination-buttons a {
                padding: 0.5rem;
                min-width: 2.5rem;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr !important;
            }

            .button-group {
                flex-direction: column;
                width: 100%;
            }

            .button-group button {
                width: 100%;
            }
        }

        /* General Styles */
        .main-content {
            transition: all 0.3s ease-in-out;
            margin-left: 16rem;
            width: calc(100% - 16rem);
        }

        .table-container {
            scrollbar-width: thin;
            scrollbar-color: #CBD5E0 #EDF2F7;
        }

        .table-container::-webkit-scrollbar {
            height: 6px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #EDF2F7;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #CBD5E0;
            border-radius: 3px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-content {
            background-color: white;
            margin: 2rem auto;
            padding: 1.5rem;
            border-radius: 0.5rem;
            max-width: 600px;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Enhanced Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #1F2937;
            font-size: 0.95rem;
            letter-spacing: 0.025em;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background-color: #FFFFFF;
            color: #374151;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-input:hover {
            border-color: #D1D5DB;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        /* Enhanced Textarea Styles */
        .form-input[type="textarea"], 
        textarea.form-input {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
            font-family: inherit;
        }

        /* Enhanced Select Styles */
        select.form-input {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }

        select.form-input:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%233b82f6' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        }

        /* Enhanced File Input Styles */
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, #F3F4F6 0%, #E5E7EB 100%);
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            font-weight: 500;
            color: #374151;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-input-label:hover {
            background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%);
            border-color: #D1D5DB;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Radio Button Styles */
        .form-radio {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #D1D5DB;
            border-radius: 50%;
            background-color: #FFFFFF;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-radio:checked {
            border-color: #3B82F6;
            background-color: #3B82F6;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
            background-size: 0.75rem 0.75rem;
            background-position: center;
            background-repeat: no-repeat;
        }

        .form-radio:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Button Styles */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: #3B82F6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563EB;
        }

        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4B5563;
        }

        .btn-danger {
            background-color: #EF4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #DC2626;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: #D1FAE5;
            border: 1px solid #34D399;
            color: #065F46;
        }

        .alert-error {
            background-color: #FEE2E2;
            border: 1px solid #F87171;
            color: #991B1B;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: #F9FAFB;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Search Bar Styles */
        .search-bar {
            position: relative;
        }

        .search-bar input {
            padding-left: 2.5rem;
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
        }
    </style>
    
    <!-- Session Management Script -->
    <script>
        // Session refresh mechanism
        let sessionRefreshInterval;
        
        // Function to refresh session
        function refreshSession() {
            fetch('session_refresh.php', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update profile picture if it changed
                    if (data.profile_photo_path) {
                        const profileImages = document.querySelectorAll('img[src*="profile"], .profile-photo, .sidebar-footer-user-panel img');
                        profileImages.forEach(img => {
                            if (img.src !== data.profile_photo_path) {
                                img.src = data.profile_photo_path;
                            }
                        });
                    }
                } else if (data.redirect) {
                    // Redirect to login if session expired
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                console.error('Session refresh error:', error);
            });
        }
        
        // Start session refresh every 5 minutes
        function startSessionRefresh() {
            sessionRefreshInterval = setInterval(refreshSession, 300000); // 5 minutes
        }
        
        // Stop session refresh
        function stopSessionRefresh() {
            if (sessionRefreshInterval) {
                clearInterval(sessionRefreshInterval);
            }
        }
        
        // Start session refresh when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startSessionRefresh();
            
            // Refresh session on user activity
            let activityTimeout;
            const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
            
            activityEvents.forEach(event => {
                document.addEventListener(event, function() {
                    clearTimeout(activityTimeout);
                    activityTimeout = setTimeout(refreshSession, 60000); // Refresh after 1 minute of inactivity
                });
            });
        });
        
        // Stop refresh when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopSessionRefresh();
            } else {
                startSessionRefresh();
            }
        });
    </script>
</head>
<body class="bg-gray-100">
    <div class="content-wrapper">
        <!-- Main content will be inserted here -->
    </div>
</body>
</html> 