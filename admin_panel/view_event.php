<?php
require_once "includes/admin_auth.php";
require_once "../db.php";

$id = intval($_GET['id']);

$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id=$id"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">
    <h1><?= $event['title'] ?></h1>

    <div class="card">
        <p><strong>Status:</strong> <?= $event['status'] ?></p>
        <p><strong>Description:</strong> <?= $event['description'] ?? 'N/A' ?></p>
    </div>
</div>

</body>
</html>