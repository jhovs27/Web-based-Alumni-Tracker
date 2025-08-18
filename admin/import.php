<?php
require_once 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $inputFileName = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        // Start transaction
        mysqli_begin_transaction($conn);

        $successCount = 0;
        $errorCount = 0;

        // Start from row 2 to skip header
        for ($row = 2; $row <= $highestRow; $row++) {
            $studentNo = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $lastName = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $middleName = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $sex = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            $course = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            $contactNo = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
            $orNo = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
            $orDate = $worksheet->getCellByColumnAndRow(8, $row)->getValue();

            // Validate required fields
            if (empty($studentNo) || empty($lastName)) {
                $errorCount++;
                continue;
            }

            // Check if student already exists
            $checkQuery = "SELECT StudentNo FROM students WHERE StudentNo = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "s", $studentNo);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                // Update existing student
                $updateQuery = "UPDATE students SET 
                              LastName = ?, 
                              MiddleName = ?, 
                              Sex = ?, 
                              course = ?, 
                              ContactNo = ? 
                              WHERE StudentNo = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "ssssss", $lastName, $middleName, $sex, $course, $contactNo, $studentNo);
                mysqli_stmt_execute($updateStmt);
            } else {
                // Insert new student
                $insertQuery = "INSERT INTO students (StudentNo, LastName, MiddleName, Sex, course, ContactNo) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($insertStmt, "ssssss", $studentNo, $lastName, $middleName, $sex, $course, $contactNo);
                mysqli_stmt_execute($insertStmt);
            }

            // Update or insert graduation record
            if (!empty($orNo) && !empty($orDate)) {
                $gradQuery = "INSERT INTO listgradsub (StudentNo, ORNo, ORDate) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            ORNo = VALUES(ORNo), 
                            ORDate = VALUES(ORDate)";
                $gradStmt = mysqli_prepare($conn, $gradQuery);
                mysqli_stmt_bind_param($gradStmt, "sss", $studentNo, $orNo, $orDate);
                mysqli_stmt_execute($gradStmt);
            }

            $successCount++;
        }

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['import_message'] = "Successfully imported $successCount records. Failed: $errorCount records.";
        header('Location: graduate-lists.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['import_error'] = "Error importing file: " . $e->getMessage();
        header('Location: graduate-lists.php');
        exit;
    }
} else {
    $_SESSION['import_error'] = "No file uploaded or invalid request.";
    header('Location: graduate-lists.php');
    exit;
}

<?php include 'includes/footer.php'; ?>
</body>
</html> 