<?php
session_start();
require_once '../admin/config/database.php';

// Validate alumni_id
$alumni_id = $_POST['alumni_id'] ?? '';
if (empty($alumni_id)) {
    header('Location: profile.php?error=missing_id');
    exit();
}

// Collect and sanitize inputs
$fields = [
    'first_name', 'last_name', 'email', 'contact_number', 'address',
    'program', 'year_graduated', 'employment_status'
];
$data = [];
foreach ($fields as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
}

// Password handling
$new_password = $_POST['new_password'] ?? '';
$password_sql = '';
$password_params = [];
if (!empty($new_password)) {
    // Hash the new password securely
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $password_sql = ', password = ?';
    $password_params[] = $hashed;
}

// Prepare SQL
$sql = "UPDATE alumni SET 
    first_name = ?, last_name = ?, email = ?, contact_number = ?, address = ?, 
    program = ?, year_graduated = ?, employment_status = ?
    $password_sql
    WHERE alumni_id = ?";

$params = [
    $data['first_name'], $data['last_name'], $data['email'], $data['contact_number'], $data['address'],
    $data['program'], $data['year_graduated'], $data['employment_status']
];
$params = array_merge($params, $password_params, [$alumni_id]);

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Optionally update session data if needed
    $_SESSION['alumni_name'] = $data['first_name'] . ' ' . $data['last_name'];
    $_SESSION['alumni_email'] = $data['email'];

    header('Location: profile.php?success=1');
    exit();
} catch (Exception $e) {
    header('Location: profile.php?error=update_failed');
    exit();
}