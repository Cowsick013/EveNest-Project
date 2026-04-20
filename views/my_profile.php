<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* SAFE QUERY – ONLY EXISTING COLUMNS */
$stmt = $conn->prepare("
    SELECT name, email, role
    FROM users
    WHERE id = ?
");

if (!$stmt) {
    die("Profile query failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include "../includes/header.php";
?>

<div class="page-container">
    <h2 class="page-title">My Profile</h2>
    <p class="page-subtitle">
        Account and role information
    </p>

    <div class="profile-card">

        <div class="profile-row">
            <span class="label">Full Name</span>
            <span class="value"><?= htmlspecialchars($user['name']) ?></span>
        </div>

        <div class="profile-row">
            <span class="label">Email</span>
            <span class="value"><?= htmlspecialchars($user['email']) ?></span>
        </div>

        <div class="profile-row">
            <span class="label">Role</span>
            <span class="value"><?= ucfirst($user['role']) ?></span>
        </div>

        <?php if (!empty($user['department'])): ?>
        <div class="profile-row">
            <span class="label">Department</span>
            <span class="value"><?= htmlspecialchars($user['department']) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($user['created_at'])): ?>
        <div class="profile-row">
            <span class="label">Account Created</span>
            <span class="value">
                <?= date("d M Y", strtotime($user['created_at'])) ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="profile-actions">
            <a href="change_password.php" class="btn primary">
                Change Password
            </a>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
