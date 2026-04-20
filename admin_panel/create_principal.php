<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminUserController.php";

$message = "";

// 🔒 Check if principal already exists (for UI control)
$check = mysqli_query($conn, "SELECT * FROM users WHERE role='principal'");
$principalExists = mysqli_num_rows($check) > 0;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $message = createPrincipal($conn, $_POST);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Principal</title>
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
    <h1>Create Principal Account</h1>

    <?php if($principalExists): ?>
        <div class="card">
            <p style="color:red;">
                Principal already exists. Only one principal is allowed in the system.
            </p>
        </div>
    <?php else: ?>

    <form method="POST" class="card" style="max-width:400px;">

        <input type="text" name="name" placeholder="Principal Name" required><br><br>

        <input type="email" name="email" placeholder="Email" required><br><br>

        <input type="password" name="password" placeholder="Password" required><br><br>

        <button type="submit">Create Principal</button>

        <p><?= $message ?></p>

    </form>

    <?php endif; ?>

</div>

</body>
</html>