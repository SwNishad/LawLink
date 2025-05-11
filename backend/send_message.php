<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    die("Not logged in");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['id'];
    $sender_role = $_SESSION['role'];
    $case_id = intval($_POST['case_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $insert = "INSERT INTO messages (case_id, sender_id, message) 
               VALUES ('$case_id', '$sender_id', '$message')";

    if (mysqli_query($conn, $insert)) {
        // Redirect based on role
        if ($sender_role === 'lawyer') {
            header("Location: ../dashboard/lawyer_chats.php");
        } elseif ($sender_role === 'client') {
            header("Location: ../dashboard/client_messages.php");
        } else {
            echo "Unknown role. Message sent, but no proper redirect.";
        }
        exit();
    } else {
        echo "Error sending message: " . mysqli_error($conn);
    }
}
