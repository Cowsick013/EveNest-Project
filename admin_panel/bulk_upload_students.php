<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminUserController.php";

$message = "";

if(isset($_POST['upload'])){
    $message = bulkUploadStudents($conn, $_FILES['csv_file']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload Students</title>
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
.btn {
    background: #5c86ac;    
    color: white;
}
    </style>
<?php include "includes/sidebar.php"; ?>

<div class="main">
    <h1>Bulk Upload Students (CSV)</h1>

    <div class="card">
        <form method="POST" enctype="multipart/form-data">

            <input type="file" name="csv_file" accept=".csv" required><br><br>

            <button class="btn" type="submit" name="upload">Upload CSV</button>

        </form>

        <p><?= $message ?></p>
    </div>

    <div class="card">
        <h3>CSV Format:</h3>
        <pre>
name,email,roll_no,stream,section,password
John,john@gmail.com,101,BCA,A,123456
        </pre>
    </div>

</div>

</body>
</html>