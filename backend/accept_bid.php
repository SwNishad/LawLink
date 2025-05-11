<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'client') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

if (isset($_GET['bid_id']) && isset($_GET['case_id'])) {
    $case_id = $_GET['case_id'];

    // Close the case
    $update_case = "UPDATE cases SET status = 'closed' WHERE id = '$case_id'";
    if (mysqli_query($conn, $update_case)) {
        header("Location: ../dashboard/client.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}

mysqli_close($conn);
?>
