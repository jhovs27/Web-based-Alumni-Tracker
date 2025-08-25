<?php
require_once 'admin/config/database.php';

echo "=== ALUMNI DATA CHECK ===\n";

// Check alumni table
$stmt = $conn->query("SELECT COUNT(*) FROM alumni");
$alumni_count = $stmt->fetchColumn();
echo "Alumni count: $alumni_count\n";

if ($alumni_count > 0) {
    echo "\n=== SAMPLE ALUMNI DATA ===\n";
    $stmt = $conn->query("SELECT * FROM alumni LIMIT 3");
    $alumni_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($alumni_data as $alumni) {
        echo "ID: " . $alumni['alumni_id'] . "\n";
        echo "Name: " . $alumni['last_name'] . ", " . $alumni['first_name'] . " " . $alumni['middle_name'] . "\n";
        echo "Course: " . $alumni['course'] . "\n";
        echo "Employment Status: " . $alumni['employment_status'] . "\n";
        echo "Student No: " . $alumni['student_no'] . "\n";
        echo "---\n";
    }
}

// Check students table
$stmt = $conn->query("SELECT COUNT(*) FROM students");
$students_count = $stmt->fetchColumn();
echo "\nStudents count: $students_count\n";

// Check employment table
$stmt = $conn->query("SELECT COUNT(*) FROM employment");
$employment_count = $stmt->fetchColumn();
echo "Employment records count: $employment_count\n";

if ($employment_count > 0) {
    echo "\n=== SAMPLE EMPLOYMENT DATA ===\n";
    $stmt = $conn->query("SELECT * FROM employment LIMIT 3");
    $employment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($employment_data as $emp) {
        echo "Alumni ID: " . $emp['alumni_id'] . "\n";
        echo "Status: " . $emp['employment_status'] . "\n";
        echo "Company: " . $emp['company_name'] . "\n";
        echo "Business: " . $emp['business_name'] . "\n";
        echo "Document: " . $emp['proof_document_path'] . "\n";
        echo "---\n";
    }
}

// Test the JOIN query
echo "\n=== TESTING JOIN QUERY ===\n";
$join_sql = "
    SELECT 
        a.alumni_id,
        a.student_no,
        a.last_name,
        a.first_name,
        a.middle_name,
        a.course,
        a.employment_status,
        s.Sex,
        e.company_name,
        e.business_name,
        e.proof_document_path
    FROM alumni a
    LEFT JOIN students s ON a.student_no = s.StudentNo
    LEFT JOIN employment e ON a.alumni_id = e.alumni_id
    LIMIT 3
";

$stmt = $conn->prepare($join_sql);
$stmt->execute();
$join_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($join_data as $row) {
    echo "Alumni: " . $row['last_name'] . ", " . $row['first_name'] . "\n";
    echo "Sex: " . ($row['Sex'] ?? 'N/A') . "\n";
    echo "Status: " . $row['employment_status'] . "\n";
    echo "Company: " . ($row['company_name'] ?? $row['business_name'] ?? 'N/A') . "\n";
    echo "Document: " . ($row['proof_document_path'] ?? 'N/A') . "\n";
    echo "---\n";
}
?>
