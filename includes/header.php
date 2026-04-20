<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>EVENEST</title>
    <link rel="stylesheet" href="/evenest/assets/css/main.css">
</head>
<body>

<header class="evenest-nav">
    <div class="nav-left">
        <span class="evenest-logo">EVENEST</span>
    </div>

    <nav class="nav-center">
        <a href="/evenest/views/<?=$_SESSION['role']?>_dashboard.php">Dashboard</a>
        <a href="/evenest/views/event_list.php">Events</a>
        <a href="/evenest/views/event_reports.php">Reports</a>
    
    </nav>

    <div class="nav-right">
        <button class="burger-nav-btn" onclick="toggleSideTab()">☰</button>

        <span class="nav-role"><?= ucfirst($_SESSION['role']) ?></span>

        <div class="nav-profile">
            <span class="nav-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </span>

            <div class="nav-dropdown">
                <a href="/evenest/views/my_profile.php">My Profile</a>
                <a href="/evenest/controllers/logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>

