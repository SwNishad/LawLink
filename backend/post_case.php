<?php
session_start();
include 'db.php';
include 'ai_summary.php';

if (!isset($_SESSION['id'])) {
    die("Session expired. Please log in again. <a href='../login.php'>Login</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_title = mysqli_real_escape_string($conn, $_POST['case_title']);
    $case_description = mysqli_real_escape_string($conn, $_POST['case_description']);
    $case_type = mysqli_real_escape_string($conn, $_POST['case_type']);
    $client_id = $_SESSION['id'];

    // Generate AI summary
    $summary = summarizeText($case_description);
    $summary = mysqli_real_escape_string($conn, $summary);

    // Insert the case with summary
    $query = "INSERT INTO cases (client_id, title, description, type, status, ai_summary) 
              VALUES ('$client_id', '$case_title', '$case_description', '$case_type', 'open', '$summary')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard/client.php?success=1");
        exit();
    } else {
        // Log error and show user-friendly message
        error_log("Case post failed: " . mysqli_error($conn));
        header("Location: ../post_case.php?error=1");
        exit();
    }
}

mysqli_close($conn);
?>