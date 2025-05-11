<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$lawyer_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LawLink | Lawyer Chats</title>
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
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: var(--primary);
            font-size: 2rem;
            text-shadow: 0 0 10px rgba(0, 249, 255, 0.5);
        }

        /* Chat Container */
        .chat-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .chat-box {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .chat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 249, 255, 0.2);
        }

        .chat-box h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .chat-history {
            max-height: 300px;
            overflow-y: auto;
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            max-width: 80%;
        }

        .message.you {
            background: rgba(0, 249, 255, 0.15);
            margin-left: auto;
            border: 1px solid rgba(0, 249, 255, 0.3);
        }

        .message.them {
            background: rgba(92, 107, 192, 0.15);
            margin-right: auto;
            border: 1px solid rgba(92, 107, 192, 0.3);
        }

        .message-sender {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .message-content {
            line-height: 1.5;
        }

        .message-time {
            font-size: 12px;
            opacity: 0.7;
            text-align: right;
            margin-top: 5px;
        }

        .send-form {
            display: grid;
            gap: 15px;
        }

        .send-form textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--light);
            resize: vertical;
            min-height: 80px;
        }

        .send-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 249, 255, 0.2);
        }

        .send-form button {
            padding: 12px;
            background: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .send-form button:hover {
            background: var(--secondary);
            color: var(--light);
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
    <a href="lawyer_cases.php">📂 My Cases</a>
    <a href="lawyer_chats.php" class="active">💬 Chats</a>
    <a href="available_cases.php">🔍 Available Cases</a>
    <a href="../backend/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="dashboard-header">
        <h1>Your Conversations</h1>
    </div>

    <div class="chat-container">
        <?php
        $cases_query = "
            SELECT c.id, c.title, c.client_id, u.name as client_name
            FROM cases c
            JOIN bids b ON c.id = b.case_id
            JOIN users u ON c.client_id = u.id
            WHERE b.lawyer_id = '$lawyer_id' AND c.status = 'closed'
            ORDER BY c.id DESC
        ";
        $cases_result = mysqli_query($conn, $cases_query);

        if (mysqli_num_rows($cases_result) > 0) {
            while ($case = mysqli_fetch_assoc($cases_result)) {
                $case_id = $case['id'];
                echo "<div class='chat-box'>";
                echo "<h3>Case: " . htmlspecialchars($case['title']) . "</h3>";
                echo "<p>Client: " . htmlspecialchars($case['client_name']) . "</p>";

                // Display chat messages
                echo "<div class='chat-history'>";
                $msg_query = "
                    SELECT m.*, u.name, u.role 
                    FROM messages m 
                    JOIN users u ON m.sender_id = u.id 
                    WHERE m.case_id = '$case_id'
                    ORDER BY m.timestamp ASC
                ";
                $msg_result = mysqli_query($conn, $msg_query);

                if (mysqli_num_rows($msg_result) > 0) {
                    while ($msg = mysqli_fetch_assoc($msg_result)) {
                        $class = $msg['sender_id'] == $lawyer_id ? 'you' : 'them';
                        echo "<div class='message $class'>";
                        echo "<div class='message-sender'>" . htmlspecialchars($msg['name']) . "</div>";
                        echo "<div class='message-content'>" . nl2br(htmlspecialchars($msg['message'])) . "</div>";
                        echo "<div class='message-time'>" . date('M j, g:i a', strtotime($msg['timestamp'])) . "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No messages yet in this conversation.</p>";
                }
                echo "</div>";

                // Send message form
                echo "<form class='send-form' action='../backend/send_message.php' method='POST'>";
                echo "<input type='hidden' name='case_id' value='$case_id'>";
                echo "<textarea name='message' placeholder='Type your message...' required></textarea>";
                echo "<button type='submit'>Send Message</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<div class='empty-state'>";
            echo "<h3>No Active Conversations</h3>";
            echo "<p>You don't have any closed cases with chat available yet.</p>";
            echo "</div>";
        }

        mysqli_close($conn);
        ?>
    </div>
</div>

</body>
</html>