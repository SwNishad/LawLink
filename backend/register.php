<?php
session_start();
include 'db.php'; // Your DB connection file

// Collecting data from POST request
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$role = mysqli_real_escape_string($conn, $_POST['role']);

// Check if user already exists
$query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "Username or Email already taken. <a href='../register.html'>Try again</a>";
    exit();
}

// Insert the new user into the database
$query = "INSERT INTO users (name, email, username, password, role) VALUES ('$name', '$email', '$username', '$password', '$role')";
if (mysqli_query($conn, $query)) {
    echo "Registration successful! <a href='../login.html'>Login</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
