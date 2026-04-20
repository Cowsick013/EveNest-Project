<?php
session_start();
require_once "../db.php";

if (!isset($_POST['email'], $_POST['password'])) {
    header("Location: ../login.php?error=missing_fields");
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

/* ===============================
   TEMP TEST PRINCIPAL LOGIN
================================ */
if ($email === 'test@principal' && $password === '123') {
    $_SESSION['user_id'] = 999;
    $_SESSION['user_name'] = 'Test Principal';
    $_SESSION['role'] = 'principal';
    header("Location: ../views/principal_dashboard.php");
    exit;
}

/* ===============================
   DATABASE LOGIN
================================ */
$stmt = $conn->prepare("
    SELECT id, email, password, role, status
    FROM users
    WHERE email = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    header("Location: ../login.php?error=user_not_found");
    exit;
}

$stmt->bind_result($id, $db_email, $hashed_password, $role, $status);
$stmt->fetch();

/* 🔐 Password Check */
if (!password_verify($password, $hashed_password)) {
    header("Location: ../login.php?error=invalid_password");
    exit;
}

/* 🔒 Suspension Check (IMPORTANT) */
if ($status === 'suspended') {
    header("Location: ../login.php?error=account_suspended");
    exit;
}

/* ===============================
   SUCCESS LOGIN
================================ */
$_SESSION['user_id']   = $id;
$_SESSION['user_name'] = $db_email; // or fetch name later
$_SESSION['role']      = $role;

/* ===============================
   ROLE REDIRECT
================================ */
switch ($role) {
    case 'student':
        header("Location: ../views/student_dashboard.php");
        break;
    case 'faculty':
        header("Location: ../views/faculty_dashboard.php");
        break;
    case 'admin':
        header("Location: ../admin_panel/dashboard.php"); // 🔥 FIXED
        break;
    case 'principal':
        header("Location: ../views/principal_dashboard.php");
        break;
    default:
        session_destroy();
        header("Location: ../login.php?error=invalid_role");
}

exit;