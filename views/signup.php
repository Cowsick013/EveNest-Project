<!DOCTYPE html>
<html>
<body>
    <h2>Signup</h2>
    <form action="../controllers/signup.php" method="POST">
    <input type="text" name="name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role" required>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
        <option value="admin">Admin</option>
        <option value="principal">Principal</option>
    </select><br><br>

    <button type="submit">Signup</button>
</form>

</body>
</html>
