<!DOCTYPE html>
<html>
<body>
    <h2>Login</h2>

    <form action="../controllers/login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="signup.php">Signup</a></p>
</body>
</html>
