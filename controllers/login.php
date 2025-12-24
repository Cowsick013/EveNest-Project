<?php
session_start();
include "../db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $name, $hashed_password, $role);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {

        // Set session variables
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['role'] = $role;

        // Redirect based on role
        if ($role === 'student') {
            header("Location: ../views/student_dashboard.php");
        } elseif ($role === 'faculty') {
            header("Location: ../views/faculty_dashboard.php");
        } elseif ($role === 'admin') {
            header("Location: ../views/admin_dashboard.php");
        } elseif ($role === 'principal') {
            header("Location: ../views/principal_dashboard.php");
        }
        exit();
        
    } else {
        echo "Invalid password!";
    }
} else {
    echo "No user found with this email!";
}
?>
