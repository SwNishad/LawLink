<?php
$conn = mysqli_connect("localhost", "root", "", "lawlink_db");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
