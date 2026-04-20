<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminUserController.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $message = createFaculty($conn, $_POST);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Faculty</title>
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
    <h1>Create Faculty Account</h1>

    <form method="POST" class="card" style="max-width:400px;">
        
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        
        <input type="email" name="email" placeholder="College Email" required><br><br>

        <input type="text" name="department" placeholder="Department" required><br><br>

        <input type="password" name="password" placeholder="Password" required><br><br>

        <button type="submit">Create Faculty</button>

        <p><?= $message ?></p>
    </form>
</div>

</body>
</html>