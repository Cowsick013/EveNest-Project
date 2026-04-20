
<?php

function logAction($conn, $user_id, $action, $description){

    $action = mysqli_real_escape_string($conn, $action);
    $description = mysqli_real_escape_string($conn, $description);

    $query = "INSERT INTO audit_logs (user_id, action, description)
              VALUES ('$user_id','$action','$description')";

    mysqli_query($conn, $query);
}