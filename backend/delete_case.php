<?php
include 'db.php';
$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM cases WHERE id = $id");
header("Location: ../dashboard/admin.php");
