<?php
session_start();
require_once "../db.php";


/* keep your existing login logic above this */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | EVENEST</title>

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

        .login-container {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #0d47a1;
        }

        .login-container p {
            text-align: center;
            margin-bottom: 25px;
            font-size: 14px;
            color: #555;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 11px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1976d2;
        }

        .login-btn {
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
        }

        .login-btn:hover {
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
            margin-top: 25px;
            font-size: 12px;
            color: #777;
        }

        /* Error message */
        .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>EVENEST</h1>
    <p>College Event Management System</p>

    <?php if (isset($_SESSION['login_error'])): ?>
        <div class="error-box">
            <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="../controllers/login.php">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="extra-links">
        Don’t have an account?
        <a href="signup.php">Sign up</a>
    </div>

    <div class="footer-text">
        © <?= date('Y') ?> EVENEST
    </div>
</div>

</body>
</html>
