<rel="stylesheet" href="./style.css">
    <style>
        body{
    margin:0;
    font-family:Segoe UI;
    background:#f4f6f9;
}

.sidebar{
    width:230px;
    height:100vh;
    position:fixed;
    background:linear-gradient(180deg,#1e3c72,#2a5298);
    padding:20px;
    color:white;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:12px;
    margin-top:8px;
    border-radius:8px;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.15);
}

.main{
    margin-left:250px;
    padding:30px;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

</style>

<div class="sidebar">
    <h2>EVENEST</h2>

    <a href="dashboard.php">Dashboard</a>

    <hr>

    <p style="margin-top:10px;">User Management</p>
    <a href="manage_users.php">User Governance</a>
    <a href="create_faculty.php">Add Faculty</a>
    <a href="create_student.php">Add Student</a>
    <a href="create_principal.php">Add Principal</a>
    <a href="bulk_upload_students.php">Upload (CSV)</a>

    <hr>

    <p>System</p>
    <a href="event_monitor.php">Event Monitoring</a>
    <a href="system_logs.php">Audit Logs</a>

    <hr>

    <a href="../logout.php">Logout</a>
</div>