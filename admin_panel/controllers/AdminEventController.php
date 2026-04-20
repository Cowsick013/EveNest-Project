<?php

function safeCount($conn, $query){
    $result = mysqli_query($conn, $query);

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

return $result;
}

function getDashboardStats($conn){

    $stats = [];

    // Total Students
    $students = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM students")
    );
    $stats['students'] = $students['total'];

    // Total Faculty
    $faculty = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM faculty")
    );
    $stats['faculty'] = $faculty['total'];

    // Active Events
    $events = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM events WHERE status='active'")
    );
    $stats['events'] = $events['total'];

    // Pending Cancellations
    $cancellations = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM events WHERE cancel_request=1")
    );
    $stats['cancellations'] = $cancellations['total'];

    // Reports Generated
    $reports = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM post_event_reports")
    );
    $stats['reports'] = $reports['total'];

    return $stats;
}

function getAllEvents($conn){

    $query = "
        SELECT 
            e.id,
            e.title,
            e.status,
            e.created_by,

            (SELECT COUNT(*) 
             FROM event_registrations r 
             WHERE r.event_id = e.id) AS participant_count,

            (SELECT COUNT(*) 
             FROM event_attendance a 
             WHERE a.event_id = e.id) AS attendance_count

        FROM events e
        ORDER BY e.id DESC
    ";

    $result = mysqli_query($conn, $query);

    if(!$result){
        die("Query Error: " . mysqli_error($conn));
    }

    return $result;
}