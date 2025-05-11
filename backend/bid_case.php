<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Unauthorized access. <a href='../login.html'>Login</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lawyer_id = $_SESSION['id'];
    $case_id = mysqli_real_escape_string($conn, $_POST['case_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);

    $query = "INSERT INTO bids (case_id, lawyer_id, message, appointment_date) 
              VALUES ('$case_id', '$lawyer_id', '$message', '$appointment_date')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard/view_case.php?id=$case_id&success=1");

        exit();
    } else {
        echo "Failed to submit bid: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>


<?php
session_start();
include 'db.php'; // Include your database connection

$case_id = $_GET['case_id']; // Get case ID from URL
$lawyer_id = $_SESSION['id']; // Get lawyer's ID from session

// Fetch the case details
$query = "SELECT * FROM cases WHERE id = '$case_id'";
$result = mysqli_query($conn, $query);
$case = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get bid data from form
    $bid_message = mysqli_real_escape_string($conn, $_POST['bid_message']);
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);

    // Insert bid into the database
    $bid_query = "INSERT INTO bids (case_id, lawyer_id, message, appointment_date) VALUES ('$case_id', '$lawyer_id', '$bid_message', '$appointment_date')";
    if (mysqli_query($conn, $bid_query)) {
        echo "Bid placed successfully!";
        header("Location: ../dashboard/lawyer.html");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LawLink | Place Bid</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

    <div class="dashboard-container">
        <div class="dashboard-box">
            <h1>Bid on Case: <?php echo $case['title']; ?></h1>

            <!-- Display case details -->
            <h3><?php echo $case['title']; ?></h3>
            <p><?php echo $case['description']; ?></p>
            <p>Status: <?php echo $case['status']; ?></p>

            <!-- Lawyer Bid Form -->
            <form action="" method="POST">
                <div class="textbox">
                    <textarea name="bid_message" placeholder="Your message to the client" required></textarea>
                </div>
                <div class="textbox">
                    <input type="date" name="appointment_date" required>
                </div>
                <input type="submit" value="Place Bid">
            </form>

            <div>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <?php
// [KEEP ALL YOUR EXISTING PHP CODE]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LawLink | Place Bid</title>
    <!-- Replace with your new styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        /* [PASTE THE CSS FROM MY PREVIOUS bid_case.php EXAMPLE] */
        :root {
            --primary: #00F9FF;
            --secondary: #5C6BC0;
            /* ... etc ... */
        }
        /* ... rest of the CSS ... */
    </style>
</head>
<body>
    <!-- [KEEP YOUR EXISTING PHP LOGIC] -->
    <div class="bid-container">
        <!-- [USE THE NEW HTML STRUCTURE BUT KEEP PHP VARIABLES] -->
        <h1>Bid on Case: <?php echo htmlspecialchars($case['title']); ?></h1>
        <!-- ... etc ... -->
    </div>
</body>
</html>
</body>
</html>
