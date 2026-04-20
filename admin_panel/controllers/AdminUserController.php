
<?php
require_once __DIR__ . "/../includes/audit.php";
function createFaculty($conn, $data){

    //session_start();

    $name = mysqli_real_escape_string($conn, $data['name']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $dept = mysqli_real_escape_string($conn, $data['department']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    // ✅ Load allowed domains
    $config = require __DIR__ . "/../../config/system_config.php";
    $allowedDomains = $config['allowed_domains'];

    $valid = false;
    foreach ($allowedDomains as $domain) {
        if (str_ends_with($email, $domain)) {
            $valid = true;
            break;
        }
    }

    if (!$valid) {
        return "Only approved email domains are allowed";
    }

    // ✅ Check duplicate email in users
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        return "Email already exists";
    }

    // 🔁 STEP 1: Insert into users
    $userQuery = "INSERT INTO users (email,password,role)
                  VALUES ('$email','$password','faculty')";

    if(!mysqli_query($conn,$userQuery)){
        return "User creation failed: " . mysqli_error($conn);
    }

    // 🔁 STEP 2: Get inserted user_id
    $user_id = mysqli_insert_id($conn);

    // 🔁 STEP 3: Insert into faculty table
    $facultyQuery = "INSERT INTO faculty (user_id,name,department)
                     VALUES ('$user_id','$name','$dept')";

    if(mysqli_query($conn,$facultyQuery)){
        return "Faculty created successfully";
    } else {
        return "Faculty insert failed: " . mysqli_error($conn);
    }
    require_once __DIR__ . "/../../includes/audit.php";
    logAction($conn, $_SESSION['user_id'], 
    "CREATE_FACULTY", 
    "Created faculty: $name"
);
}
function createStudent($conn, $data){

    session_start();

    $name = mysqli_real_escape_string($conn, $data['name']);
    $roll = mysqli_real_escape_string($conn, $data['roll_no']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $stream = mysqli_real_escape_string($conn, $data['stream']);
    $section = mysqli_real_escape_string($conn, $data['section']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    // ✅ Domain validation (same config)
    $config = require __DIR__ . "/../../config/system_config.php";
    $allowedDomains = $config['allowed_domains'];

    $valid = false;
    foreach ($allowedDomains as $domain) {
        if (str_ends_with($email, $domain)) {
            $valid = true;
            break;
        }
    }

    if (!$valid) {
        return "Only approved email domains are allowed";
    }

    // ✅ Duplicate check
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        return "Email already exists";
    }

    // 🔁 STEP 1 → users
    $userQuery = "INSERT INTO users (email,password,role)
                  VALUES ('$email','$password','student')";

    if(!mysqli_query($conn,$userQuery)){
        return "User creation failed: " . mysqli_error($conn);
    }

    $user_id = mysqli_insert_id($conn);

    // 🔁 STEP 2 → students table
    $studentQuery = "INSERT INTO students (user_id,name,roll_no,stream,section)
                     VALUES ('$user_id','$name','$roll','$stream','$section')";

    if(mysqli_query($conn,$studentQuery)){
        return "Student created successfully";
    } else {
        return "Student insert failed: " . mysqli_error($conn);
    }

    logAction($conn, $_SESSION['user_id'], 
    "CREATE_STUDENT", 
    "Created student: $name"
);
}
function createPrincipal($conn, $data){

    session_start();

    $name = mysqli_real_escape_string($conn, $data['name']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    // 🔒 Check if principal already exists
    $checkPrincipal = mysqli_query($conn, "SELECT * FROM users WHERE role='principal'");
    if(mysqli_num_rows($checkPrincipal) > 0){
        return "Principal already exists. Only one allowed.";
    }

    // 🔁 STEP 1 → users
    $userQuery = "INSERT INTO users (email,password,role)
                  VALUES ('$email','$password','principal')";

    if(!mysqli_query($conn,$userQuery)){
        return "User creation failed: " . mysqli_error($conn);
    }

    $user_id = mysqli_insert_id($conn);

    // 🔁 STEP 2 → principal table
    $principalQuery = "INSERT INTO principals (user_id,name)
                       VALUES ('$user_id','$name')";

    if(mysqli_query($conn,$principalQuery)){
        return "Principal created successfully";
    } else {
        return "Principal insert failed: " . mysqli_error($conn);
    }
}
function getAllUsers($conn){
    return mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}
function bulkUploadStudents($conn, $file){

    if($file['error'] != 0){
        return "File upload failed";
    }

    $filename = $file['tmp_name'];

    $handle = fopen($filename, "r");

    if(!$handle){
        return "Unable to read file";
    }

    $rowCount = 0;
    $success = 0;

    // Skip header
    fgetcsv($handle);

    while(($row = fgetcsv($handle)) !== FALSE){

        $name = mysqli_real_escape_string($conn, $row[0]);
        $email = mysqli_real_escape_string($conn, $row[1]);
        $roll = mysqli_real_escape_string($conn, $row[2]);
        $stream = mysqli_real_escape_string($conn, $row[3]);
        $section = mysqli_real_escape_string($conn, $row[4]);
        $password = password_hash($row[5], PASSWORD_DEFAULT);

        // Check duplicate
        $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
        if(mysqli_num_rows($check) > 0){
            continue;
        }

        // Insert into users
        $userQuery = "INSERT INTO users (email,password,role)
                      VALUES ('$email','$password','student')";

        if(!mysqli_query($conn,$userQuery)){
            continue;
        }

        $user_id = mysqli_insert_id($conn);

        // Insert into students
        $studentQuery = "INSERT INTO students (user_id,name,roll_no,stream,section)
                         VALUES ('$user_id','$name','$roll','$stream','$section')";

        if(mysqli_query($conn,$studentQuery)){
            $success++;
        }

        $rowCount++;
    }

    fclose($handle);

    return "Uploaded $success out of $rowCount students successfully";

    logAction($conn, $_SESSION['user_id'], 
    "BULK_UPLOAD", 
    "Uploaded $success students via CSV"

    
);
}

if(isset($_GET['action']) && isset($_GET['id'])){

    require "../../db.php";

    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if($action == "suspend"){
        mysqli_query($conn, "UPDATE users SET status='suspended' WHERE id=$id");
    }

    if($action == "activate"){
        mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$id");
    }

    logAction($conn, $_SESSION['user_id'], 
    "CREATE_EVENT", 
    "Created event: $title"
);
    header("Location: ../manage_users.php");
    
    exit();
}