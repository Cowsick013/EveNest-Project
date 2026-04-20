<?php
session_start();
require_once "../db.php";

/* keep your existing signup logic / flash messages above */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | EVENEST</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #0d47a1, #1976d2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .signup-container h1 {
            text-align: center;
            margin-bottom: 8px;
            color: #0d47a1;
        }

        .signup-container p {
            text-align: center;
            margin-bottom: 22px;
            font-size: 14px;
            color: #555;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1976d2;
        }

        .signup-btn {
            width: 100%;
            padding: 12px;
            background: #1976d2;
            border: none;
            color: white;
            font-size: 15px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .signup-btn:hover {
            background: #0d47a1;
        }

        .extra-links {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .extra-links a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: #777;
        }

        .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .success-box {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h1>EVENEST</h1>
    <p>Create your account</p>

    <?php if (isset($_SESSION['signup_error'])): ?>
        <div class="error-box">
            <?= $_SESSION['signup_error']; unset($_SESSION['signup_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['signup_success'])): ?>
        <div class="success-box">
            <?= $_SESSION['signup_success']; unset($_SESSION['signup_success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="../controllers/signup.php">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="Enter full name">
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter email">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Create password">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="">Select role</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
            </select>
        </div>

        <button type="submit" class="signup-btn">Create Account</button>
    </form>

    <div class="extra-links">
        Already have an account?
        <a href="login.php">Login</a>
    </div>

    <div class="footer-text">
        © <?= date('Y') ?> EVENEST
    </div>
</div>

</body>
</html>
