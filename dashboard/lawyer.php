<?php
session_start();
include '../backend/db.php';
include '../backend/ai_summary.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$lawyer_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LawLink | Lawyer Dashboard</title>
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

        .section-title {
            color: var(--primary);
            margin: 30px 0 20px;
            font-size: 1.5rem;
        }

        /* Case Cards */
        .case-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .case-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .case-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 249, 255, 0.2);
        }

        .case-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .case-card p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-top: 10px;
        }

        .open {
            background: rgba(0, 249, 255, 0.15);
            color: var(--primary);
        }

        .closed {
            background: rgba(92, 107, 192, 0.15);
            color: var(--secondary);
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

        /* Available Cases Section */
        .available-cases {
            margin-top: 40px;
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
            .case-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile">
        <img src="../assets/images/profile.jpg" alt="Lawyer Profile">
        <h3>Lawyer Dashboard</h3>
    </div>
    <a href="lawyer.php" class="active">🏠 Dashboard</a>
    <a href="lawyer_cases.php">📂 My Cases</a>
    <a href="available_cases.php">🔍 Available Cases</a>
    <a href="lawyer_chats.php">💬 Chats</a>
    <a href="../backend/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="dashboard-header">
        <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    </div>

    <!-- My Bidded Cases Section -->
    <h2 class="section-title">My Bidded Cases</h2>
    <div class="case-grid">
        <?php
        $bidded_query = "
            SELECT bids.*, cases.title, cases.status, cases.description, cases.summary 
            FROM bids 
            JOIN cases ON bids.case_id = cases.id 
            WHERE bids.lawyer_id = '$lawyer_id' 
            ORDER BY bids.id DESC
            LIMIT 4
        ";
        $bidded_result = mysqli_query($conn, $bidded_query);

        if (mysqli_num_rows($bidded_result) > 0) {
            while ($case = mysqli_fetch_assoc($bidded_result)) {
                // Use stored summary if available, otherwise generate one
                $summary = !empty($case['summary']) ? $case['summary'] : summarizeText($case['description']);
                
                echo "<div class='case-card'>";
                echo "<h3>" . htmlspecialchars($case['title']) . "</h3>";
                echo "<p>" . htmlspecialchars($summary) . "</p>";
                echo "<p><strong>Status:</strong> <span class='status-badge " . htmlspecialchars($case['status']) . "'>" . ucfirst($case['status']) . "</span></p>";
                
                echo "<div class='action-btns'>";
                if ($case['status'] === 'closed') {
                    echo "<a href='chat.php?case_id=" . $case['case_id'] . "' class='btn btn-primary'>💬 Open Chat</a>";
                }
                echo "<a href='view_case.php?id=" . $case['case_id'] . "' class='btn btn-secondary'>View Details</a>";
                echo "</div>";
                
                echo "</div>";
            }
        } else {
            echo "<p>You haven't bid on any cases yet.</p>";
        }
        ?>
    </div>

    <!-- Available Cases Section -->
    <div class="available-cases">
        <h2 class="section-title">Available Cases</h2>
        <div class="case-grid">
            <?php
            $available_query = "
                SELECT * FROM cases 
                WHERE status = 'open' 
                AND id NOT IN (SELECT case_id FROM bids WHERE lawyer_id = '$lawyer_id')
                ORDER BY id DESC 
                LIMIT 4
            ";
            $available_result = mysqli_query($conn, $available_query);

            if (mysqli_num_rows($available_result) > 0) {
                while ($case = mysqli_fetch_assoc($available_result)) {
                    // Use stored summary if available, otherwise generate one
                    $summary = !empty($case['summary']) ? $case['summary'] : summarizeText($case['description']);
                    
                    echo "<div class='case-card'>";
                    echo "<h3>" . htmlspecialchars($case['title']) . "</h3>";
                    echo "<p>" . htmlspecialchars($summary) . "</p>";
                    echo "<p><strong>Type:</strong> " . ucfirst($case['type']) . "</p>";
                    
                    echo "<div class='action-btns'>";
                    echo "<a href='bid_case.php?id=" . $case['id'] . "' class='btn btn-primary'>✍️ Place Bid</a>";
                    echo "<a href='view_case.php?id=" . $case['id'] . "' class='btn btn-secondary'>View Details</a>";
                    echo "</div>";
                    
                    echo "</div>";
                }
            } else {
                echo "<p>No available cases at the moment.</p>";
            }
            ?>
        </div>
        <a href="available_cases.php" class="btn btn-secondary" style="margin-top: 20px;">View All Available Cases</a>
    </div>
</div>

</body>
</html>