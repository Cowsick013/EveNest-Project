<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminUserController.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $message = createStudent($conn, $_POST);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<style>
button {
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
}

button {
    background: #5c86ac;
    color: white;
}
    </style>
<?php include "includes/sidebar.php"; ?>

<div class="main">
    <h1>Create Student</h1>

    <form method="POST" class="card">

        <input type="text" name="name" placeholder="Name" required><br><br>

        <input type="text" name="roll_no" placeholder="Roll No" required><br><br>

        <input type="email" name="email" placeholder="Email" required><br><br>

        <input type="text" name="stream" placeholder="Stream (BCA/BBA/BCom)" required><br><br>

        <input type="text" name="section" placeholder="Section (A/B)" required><br><br>

        <input type="password" name="password" placeholder="Password" required><br><br>

        <button type="submit">Create Student</button>

        <p><?= $message ?></p>

    </form>
</div>

</body>
</html>