<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'client') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$client_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LawLink | Client Dashboard</title>
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

        .section {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        h1, h2 {
            color: var(--primary);
            margin-bottom: 25px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--light);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control {
    /* … */
           background: rgba(0, 0, 0, 0.3);  /* ← updated */
    /* … */
        }
        .form-control option {
            background: var(--dark);
            color: var(--light);
        }


        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 249, 255, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--dark);
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 249, 255, 0.3);
        }

        /* Case Cards */
        .case-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }

        .case-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .case-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
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

        .bid-list {
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .bid-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .bid-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .bid-actions {
            margin-top: 10px;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 14px;
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
        <img src="../assets/images/profile.jpg" alt="Client Profile">
        <h3>Client Panel</h3>
    </div>
    <a href="#post" class="active">📝 Post Case</a>
    <a href="#my-cases">📁 My Cases</a>
    <a href="client_messages.php">💬 Messages</a>
    <a href="../backend/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Post Case Section -->
    <section id="post" class="section">
        <h2>📝 Post a New Case</h2>
        <form action="../backend/post_case.php" method="POST">
            <div class="form-group">
                <label for="case_title">Case Title</label>
                <input type="text" id="case_title" name="case_title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="case_description">Description</label>
                <textarea id="case_description" name="case_description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="case_type">Case Type</label>
                <select id="case_type" name="case_type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="divorce">Divorce</option>
                    <option value="property">Property</option>
                    <option value="criminal">Criminal</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Post Case</button>
        </form>
    </section>

    <!-- My Cases Section -->
    <section id="my-cases" class="section">
        <h2>📂 Your Posted Cases</h2>
        
        <?php
        $query = "SELECT * FROM cases WHERE client_id = '$client_id' ORDER BY id DESC";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($case = mysqli_fetch_assoc($result)) {
                echo '<div class="case-card">';
                echo '<h3>' . htmlspecialchars($case['title']) . '</h3>';
                echo '<div class="case-meta">';
                echo '<span><strong>Type:</strong> ' . ucfirst($case['type']) . '</span>';
                echo '<span class="status-badge status-' . $case['status'] . '">' . ucfirst($case['status']) . '</span>';
                echo '</div>';
                echo '<p>' . nl2br(htmlspecialchars($case['description'])) . '</p>';
                
                // Bids Section
                $case_id = $case['id'];
                $bid_query = "SELECT bids.*, users.name AS lawyer_name FROM bids 
                             JOIN users ON bids.lawyer_id = users.id 
                             WHERE case_id = '$case_id'";
                $bid_result = mysqli_query($conn, $bid_query);

                if (mysqli_num_rows($bid_result) > 0) {
                    echo '<div class="bid-list">';
                    echo '<h4>Bids Received:</h4>';
                    
                    while ($bid = mysqli_fetch_assoc($bid_result)) {
                        echo '<div class="bid-item">';
                        echo '<div class="bid-meta">';
                        echo '<span><strong>Lawyer:</strong> ' . htmlspecialchars($bid['lawyer_name']) . '</span>';
                        echo '<span><strong>Date:</strong> ' . $bid['appointment_date'] . '</span>';
                        echo '</div>';
                        echo '<p>' . nl2br(htmlspecialchars($bid['message'])) . '</p>';
                        
                        if ($case['status'] === 'open') {
                            echo '<div class="bid-actions">';
                            echo '<a href="../backend/accept_bid.php?bid_id=' . $bid['id'] . '&case_id=' . $case['id'] . '" class="btn btn-primary btn-sm">Accept Offer</a>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No bids yet for this case.</p>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<p>You haven\'t posted any cases yet.</p>';
        }

        mysqli_close($conn);
        ?>
    </section>
</div>

</body>
</html>