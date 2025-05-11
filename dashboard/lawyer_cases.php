<?php
session_start();
include '../backend/db.php';
include '../backend/ai_summary.php'; // Include the AI summary function

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$lawyer_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LawLink | My Cases</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #00F9FF;
            --secondary: #5C6BC0;
            --dark: #0a192f;
            --light: #f8f9fa;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: url('../assets/images/background.png') no-repeat center center/cover;
            background-attachment: fixed;
            color: var(--light);
            min-height: 100vh;
            display: flex;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 25, 47, 0.85);
            z-index: -1;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
            border-right: 1px solid var(--glass-border);
        }

        .sidebar .profile {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            margin-bottom: 15px;
        }

        .sidebar .profile h3 {
            color: var(--primary);
            font-size: 18px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background: var(--primary);
            color: var(--dark);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: var(--primary);
            font-size: 2rem;
            text-shadow: 0 0 10px rgba(0, 249, 255, 0.5);
        }

        /* Case List Styles */
        .case-list {
            list-style: none;
        }

        .case-item {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .case-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 249, 255, 0.2);
        }

        .case-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .case-title {
            color: var(--primary);
            font-size: 1.3rem;
            margin: 0;
        }

        .case-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-open {
            background: rgba(0, 249, 255, 0.15);
            color: var(--primary);
        }

        .status-closed {
            background: rgba(92, 107, 192, 0.15);
            color: var(--secondary);
        }

        .case-details {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .case-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .action-btns {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--dark);
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: var(--light);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: var(--glass);
            border-radius: 12px;
            border: 1px dashed var(--glass-border);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
                padding: 20px 15px;
            }
            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--glass-border);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 25px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile">
        <img src="../assets/images/profile.jpg" alt="Lawyer Profile">
        <h3>Lawyer Panel</h3>
    </div>
    <a href="lawyer.php">🏠 Dashboard</a>
    <a href="lawyer_cases.php" class="active">📂 My Cases</a>
    <a href="available_cases.php">🔍 Available Cases</a>
    <a href="lawyer_chats.php">💬 Chats</a>
    <a href="../backend/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="dashboard-header">
        <h1>My Bidded Cases</h1>
    </div>

    <ul class="case-list">
        <?php
        $query = "
            SELECT bids.*, cases.title, cases.status, cases.description, cases.type, cases.summary 
            FROM bids 
            JOIN cases ON bids.case_id = cases.id 
            WHERE bids.lawyer_id = '$lawyer_id' 
            ORDER BY bids.id DESC
        ";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Use stored summary if available, otherwise generate one
                $summary = !empty($row['summary']) ? $row['summary'] : summarizeText($row['description']);
                
                echo "<li class='case-item'>";
                echo "<div class='case-header'>";
                echo "<h3 class='case-title'>" . htmlspecialchars($row['title']) . "</h3>";
                echo "<span class='case-status status-" . htmlspecialchars($row['status']) . "'>" . ucfirst($row['status']) . "</span>";
                echo "</div>";
                
                echo "<div class='case-meta'>";
                echo "<span class='meta-item'><strong>Type:</strong> " . ucfirst($row['type']) . "</span>";
                echo "<span class='meta-item'><strong>Appointment:</strong> " . htmlspecialchars($row['appointment_date']) . "</span>";
                echo "</div>";
                
                echo "<div class='case-details'>";
                echo "<p><strong>Your Message:</strong> " . htmlspecialchars($row['message']) . "</p>";
                echo "<p>" . htmlspecialchars($summary) . "</p>";
                echo "</div>";
                
                echo "<div class='action-btns'>";
                if ($row['status'] === 'closed') {
                    echo "<a href='chat.php?case_id=" . $row['case_id'] . "' class='btn btn-primary'>💬 Open Chat</a>";
                }
                echo "<a href='view_case.php?id=" . $row['case_id'] . "' class='btn btn-secondary'>View Case Details</a>";
                echo "</div>";
                
                echo "</li>";
            }
        } else {
            echo "<div class='empty-state'>";
            echo "<h3>No Cases Found</h3>";
            echo "<p>You haven't bid on any cases yet.</p>";
            echo "<a href='available_cases.php' class='btn btn-primary' style='margin-top: 15px;'>Browse Available Cases</a>";
            echo "</div>";
        }

        mysqli_close($conn);
        ?>
    </ul>
</div>

</body>
</html>