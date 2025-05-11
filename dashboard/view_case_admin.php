<?php
session_start();
include '../backend/db.php';
include '../backend/ai_summary.php';  // <-- load the summarization helper

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$case_id    = intval($_GET['id'] ?? 0);
$case_query = mysqli_query($conn, "SELECT * FROM cases WHERE id = '$case_id'");
$case       = mysqli_fetch_assoc($case_query);

if (!$case) {
    die("Case not found.");
}

// If you’ve already stored a summary in the DB, use it; otherwise generate one now:
if (!empty($case['summary'])) {
    $summary = $case['summary'];
} else {
    $summary = summarizeText($case['description']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Case | Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #00F9FF;
            --secondary:    #5C6BC0;
            --dark:         #0a192f;
            --light:        #f8f9fa;
            --glass:        rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.4);
        }
        * {
            margin: 0; padding: 0; box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: url('../assets/images/background.png') no-repeat center/cover;
            background-attachment: fixed;
            color: var(--light);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .case-details {
            width: 100%; max-width: 900px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 251, 255, 0.44);
        }
        .case-details h2 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .case-details p {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        .case-details p strong {
            color: var(--light);
        }
        .ai-summary {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: var(--primary);
            color: var(--dark);
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
        }
        .back-link:hover {
            background: var(--secondary);
        }
        .bids-list {
            list-style: none;
            margin-top: 20px;
        }
        .bids-list li {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .bids-list li p {
            margin-bottom: 8px;
        }
        .bids-list li p span {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="case-details">
        <h2><?= htmlspecialchars($case['title']) ?></h2>

        <p><strong>Case Type:</strong> <?= htmlspecialchars(ucfirst($case['type'])) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($case['status'])) ?></p>
        <p><strong>Description:</strong><br>
            <?= nl2br(htmlspecialchars($case['description'])) ?>
        </p>

        <div class="ai-summary">
            <p><strong>AI Summary:</strong></p>
            <p><?= htmlspecialchars($summary) ?></p>
        </div>

        <p><strong>Client ID:</strong> <?= htmlspecialchars($case['client_id']) ?></p>

        <a href="admin.php" class="back-link">← Back to Dashboard</a>

        <h3 style="margin-top: 40px; color: var(--primary);">📥 Bids on This Case</h3>

        <?php
        $bids_query = mysqli_query($conn, "
            SELECT b.*, u.name AS lawyer_name
            FROM bids b
            JOIN users u ON b.lawyer_id = u.id
            WHERE b.case_id = '$case_id'
        ");

        if (mysqli_num_rows($bids_query) > 0): ?>
            <ul class="bids-list">
                <?php while ($bid = mysqli_fetch_assoc($bids_query)): ?>
                    <li>
                        <p><span>Lawyer:</span> <?= htmlspecialchars($bid['lawyer_name']) ?></p>
                        <p><span>Message:</span> <?= htmlspecialchars($bid['message']) ?></p>
                        <p><span>Appointment Date:</span> <?= htmlspecialchars($bid['appointment_date']) ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p style="margin-top: 15px;">No bids have been made yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
