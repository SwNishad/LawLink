<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['id'])) {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];
$case_id = intval($_GET['case_id'] ?? 0);

// Get case info (ensure it's a valid case and accepted)
$case_query = "SELECT * FROM cases WHERE id = '$case_id'";
$case_result = mysqli_query($conn, $case_query);
$case = mysqli_fetch_assoc($case_result);

if (!$case || $case['status'] !== 'closed') {
    die("Chat is only available for accepted/closed cases.");
}

// Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $insert = "INSERT INTO messages (case_id, sender_id, message) VALUES ('$case_id', '$user_id', '$msg')";
    mysqli_query($conn, $insert);
}

// Fetch chat messages
$msg_query = "SELECT m.*, u.name FROM messages m 
              JOIN users u ON m.sender_id = u.id 
              WHERE m.case_id = '$case_id' 
              ORDER BY m.timestamp ASC";
$msg_result = mysqli_query($conn, $msg_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat | Case #<?php echo $case_id; ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #00F9FF;
            --secondary: #5C6BC0;
            --dark: #0a192f;
            --light: #f8f9fa;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --dark-glass: rgba(0, 0, 0, 0.5);
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

        .chat-container {
            max-width: 800px;
            width: 100%;
            margin: 40px auto;
            background: var(--dark-glass);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 25px rgba(0, 249, 255, 0.1);
        }

        .chat-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .chat-header h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 10px;
            text-shadow: 0 0 10px rgba(0, 249, 255, 0.3);
        }

        .chat-header p {
            color: var(--light);
            opacity: 0.8;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            margin-bottom: 25px;
            background: var(--glass);
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 3px;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .message.you {
            align-items: flex-end;
        }

        .message.other {
            align-items: flex-start;
        }

        .message .sender {
            font-size: 0.8rem;
            color: var(--primary);
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .message .bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
        }

        .message.you .bubble {
            background: var(--primary);
            color: var(--dark);
            border-bottom-right-radius: 4px;
        }

        .message.other .bubble {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-bottom-left-radius: 4px;
        }

        .message .time {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 5px;
            text-align: right;
        }

        .chat-form {
            margin-top: 20px;
        }

        .chat-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--light);
            font-family: 'Poppins', sans-serif;
            resize: none;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .chat-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 249, 255, 0.2);
        }

        .chat-form button {
            padding: 12px 25px;
            background: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-form button:hover {
            background: var(--secondary);
            color: var(--light);
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--light);
            text-shadow: 0 0 10px rgba(0, 249, 255, 0.5);
        }

        @media (max-width: 768px) {
            .chat-container {
                margin: 20px;
                padding: 20px;
            }
            
            .message .bubble {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <h2><?php echo htmlspecialchars($case['title']); ?></h2>
        <p><strong>Case Type:</strong> <?php echo htmlspecialchars(ucfirst($case['type'])); ?></p>
    </div>

    <div class="chat-messages">
        <?php
        while ($msg = mysqli_fetch_assoc($msg_result)) {
            $is_you = $msg['sender_id'] == $user_id ? 'you' : 'other';
            echo "<div class='message $is_you'>";
            echo "<span class='sender'>" . htmlspecialchars($msg['name']) . "</span>";
            echo "<div class='bubble'>" . htmlspecialchars($msg['message']) . "</div>";
            echo "<span class='time'>" . date('h:i A', strtotime($msg['timestamp'])) . "</span>";
            echo "</div>";
        }
        ?>
    </div>

    <form method="POST" class="chat-form">
        <textarea name="message" placeholder="Type your message..." required></textarea>
        <button type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
            </svg>
            Send Message
        </button>
    </form>

    <a href="<?php echo $user_role === 'lawyer' ? 'lawyer.php' : 'client.php'; ?>" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>