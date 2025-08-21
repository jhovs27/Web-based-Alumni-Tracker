<?php
include 'config/database.php';
session_start();

// Add Department
if (isset($_POST['add_department'])) {
    $departmentName = $_POST['departmentName'];
    $description = $_POST['description'];
    $departmentHead = $_POST['departmentHead'];
    $designation = $_POST['designation'];
    $active = isset($_POST['active']) ? 1 : 0;

    // Check if department already exists
    $check_stmt = $conn->prepare("SELECT * FROM department WHERE DepartmentName = ?");
    $check_stmt->execute([$departmentName]);

    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "Department already exists!";
    } else {
        // Insert new department
        $insert_stmt = $conn->prepare("INSERT INTO department (DepartmentName, Description, DepartmentHead, Designation, Active) VALUES (?, ?, ?, ?, ?)");
        if ($insert_stmt->execute([$departmentName, $description, $departmentHead, $designation, $active])) {
            $_SESSION['success'] = "Department added successfully!";
        } else {
            $_SESSION['error'] = "Error adding department.";
        }
    }
}

// Delete Department
if (isset($_POST['delete_department'])) {
    $id = $_POST['delete_department'];
    $delete_stmt = $conn->prepare("DELETE FROM department WHERE id = ?");
    if ($delete_stmt->execute([$id])) {
        $_SESSION['success'] = "Department deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting department.";
    }
}

// Edit Department
if (isset($_POST['edit_department'])) {
    $id = $_POST['edit_id'];
    $departmentName = $_POST['departmentName'];
    $description = $_POST['description'];
    $departmentHead = $_POST['departmentHead'];
    $designation = $_POST['designation'];
    $active = isset($_POST['active']) ? 1 : 0;

    // Check if department name already exists (excluding current department)
    $check_stmt = $conn->prepare("SELECT * FROM department WHERE DepartmentName = ? AND id != ?");
    $check_stmt->execute([$departmentName, $id]);

    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "Department name already exists!";
    } else {
        // Update department
        $update_stmt = $conn->prepare("UPDATE department SET DepartmentName = ?, Description = ?, DepartmentHead = ?, Designation = ?, Active = ? WHERE id = ?");
        if ($update_stmt->execute([$departmentName, $description, $departmentHead, $designation, $active, $id])) {
            $_SESSION['success'] = "Department updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating department.";
        }
    }
}

header("Location: departments.php");
exit();
?> 