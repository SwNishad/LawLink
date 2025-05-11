<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$lawyer_id   = $_SESSION['id'];
$case_id     = intval($_GET['id'] ?? 0);

// Fetch case details
$case_query  = "SELECT * FROM cases WHERE id = '$case_id'";
$case_result = mysqli_query($conn, $case_query);
$case        = mysqli_fetch_assoc($case_result);

if (!$case) {
    die("Case not found.");
}

// Check if this lawyer already bid
$check_bid_query = "SELECT * FROM bids WHERE lawyer_id = '$lawyer_id' AND case_id = '$case_id'";
$bid_result      = mysqli_query($conn, $check_bid_query);
$already_bid     = mysqli_num_rows($bid_result) > 0;

// Success message
$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Case | LawLink</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #00F9FF;
            --secondary:    #5C6BC0;
            --dark:         #0a192f;
            --light:        #f8f9fa;
            --glass:        rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: url('../assets/images/background.png') no-repeat center/cover;
            background-attachment: fixed;
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .case-details {
            width: 100%;
            max-width: 800px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,249,255,0.2);
        }

        .case-details h2 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 2rem;
        }

        .case-details p,
        .case-details label {
            color: var(--light);
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .success-msg {
            background: rgba(212,237,218,0.3);
            color: rgba(21,87,36,0.8);
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .already-bid {
            background: rgba(255,243,205,0.3);
            color: rgba(133,101,4,0.8);
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .case-details .input-field,
        .case-details textarea {
            width: 100%;
            padding: 12px 15px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 6px;
            color: var(--light);
            margin-bottom: 20px;
            resize: vertical;
            transition: border-color 0.3s ease;
            font-size: 1rem;
        }

        .case-details .input-field:focus,
        .case-details textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .case-details button,
        .back-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .case-details button.btn-primary {
            background: var(--primary);
            color: var(--dark);
            margin-right: 10px;
        }

        .case-details button.btn-primary:hover {
            background: var(--secondary);
            color: var(--light);
            transform: translateY(-2px);
        }

        .back-btn {
            background: var(--secondary);
            color: var(--light);
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 20px;
        }

        .back-btn:hover {
            background: var(--primary);
        }
    </style>
</head>
<body>

    <div class="case-details">
        <h2><?= htmlspecialchars($case['title']) ?></h2>
        <p><strong>Type:</strong>   <?= htmlspecialchars(ucfirst($case['type'])) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($case['status'])) ?></p>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($case['description'])) ?></p>

        <?php if ($success): ?>
            <div class="success-msg">
                ✅ Your bid was successfully submitted.
            </div>
        <?php endif; ?>

        <?php if ($already_bid): ?>
            <div class="already-bid">
                ⚠️ You have already submitted a bid for this case.
            </div>
        <?php elseif ($case['status'] === 'closed'): ?>
            <div class="already-bid">
                ❌ This case is closed. Bidding is disabled.
            </div>
        <?php else: ?>
            <form action="../backend/bid_case.php" method="POST">
                <input type="hidden" name="case_id" value="<?= $case_id ?>">

                <label for="message">Message to Client</label>
                <textarea id="message" name="message" class="input-field" placeholder="Your message..." required></textarea>

                <label for="appointment_date">Appointment Date</label>
                <input type="date" id="appointment_date" name="appointment_date" class="input-field" required>

                <button type="submit" class="btn-primary">Submit Bid</button>
            </form>
        <?php endif; ?>

        <a href="lawyer.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>

</body>
</html>
