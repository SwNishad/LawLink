<?php
session_start();
include '../backend/db.php';
include '../backend/ai_summary.php'; // Include the AI summary function

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'lawyer') {
    die("Access denied. <a href='../login.html'>Login</a>");
}

$lawyer_id   = $_SESSION['id'];
$type_filter = $_GET['type'] ?? '';
$sort_by     = $_GET['sort'] ?? 'newest';

// Build base query
$query = "
    SELECT *
      FROM cases
     WHERE status = 'open'
       AND id NOT IN (
           SELECT case_id
             FROM bids
            WHERE lawyer_id = ?
       )
";

// Add type filter if valid
$valid_types = ['divorce','property','criminal','other'];
if ($type_filter !== '' && in_array($type_filter, $valid_types, true)) {
    $query .= " AND type = ?";
}

// Add sorting
if ($sort_by === 'oldest') {
    $query .= " ORDER BY id ASC";
} else {
    $query .= " ORDER BY id DESC";
}

// Prepare & bind
$stmt = $conn->prepare($query);

if ($type_filter !== '' && in_array($type_filter, $valid_types, true)) {
    $stmt->bind_param("is", $lawyer_id, $type_filter);
} else {
    $stmt->bind_param("i", $lawyer_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LawLink | Available Cases</title>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <style>
    :root {
      --primary: #00F9FF;
      --secondary: #5C6BC0;
      --dark: #0a192f;
      --light: #f8f9fa;
      --glass: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family:'Poppins', sans-serif;
      background: url('../assets/images/background.png') no-repeat center/cover fixed;
      color: var(--light);
      min-height:100vh;
      display:flex;
      position:relative;
    }
    body::before {
      content:'';
      position:absolute; top:0; left:0;
      width:100%; height:100%;
      background:rgba(10,25,47,0.85);
      z-index:-1;
    }
    .sidebar {
      width:280px;
      background:rgba(0,0,0,0.7);
      backdrop-filter:blur(10px);
      padding:30px 20px;
      height:100vh;
      position:fixed;
      border-right:1px solid var(--glass-border);
    }
    .sidebar .profile { text-align:center; margin-bottom:30px; }
    .sidebar .profile img {
      width:80px; height:80px;
      border-radius:50%; object-fit:cover;
      border:2px solid var(--primary);
      margin-bottom:15px;
    }
    .sidebar .profile h3 { color:var(--primary); font-size:18px; }
    .sidebar a {
      display:flex; align-items:center;
      gap:12px;
      color:rgba(255,255,255,0.8);
      text-decoration:none;
      padding:12px 15px;
      margin-bottom:10px;
      border-radius:8px;
      transition:all 0.3s ease;
    }
    .sidebar a:hover, .sidebar a.active {
      background:var(--primary);
      color:var(--dark);
    }
    .main-content {
      margin-left:280px;
      padding:40px;
      width:calc(100% - 280px);
    }
    .dashboard-header {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:30px;
    }
    .dashboard-header h1 {
      color:var(--primary);
      font-size:2rem;
      text-shadow:0 0 10px rgba(0,249,255,0.5);
    }
    .filter-controls {
      display:flex;
      gap:15px;
      flex-wrap:wrap;
    }
    .filter-select {
      padding:10px 15px;
      background:var(--glass);
      border:1px solid var(--glass-border);
      border-radius:8px;
      color:var(--light);
      min-width:200px;
    }

    .filter-select option {
       background-color: var(--dark);
       color: var(--light);
    }

    .case-grid {
      display:grid;
      grid-template-columns:repeat(auto-fill,minmax(350px,1fr));
      gap:25px;
    }
    .case-card {
      background:var(--glass);
      backdrop-filter:blur(10px);
      border:1px solid var(--glass-border);
      border-radius:12px;
      padding:25px;
      transition:all 0.3s ease;
    }
    .case-card:hover {
      transform:translateY(-5px);
      box-shadow:0 10px 25px rgba(0,249,255,0.2);
    }
    .case-card h3 {
      color:var(--primary);
      margin-bottom:15px;
      font-size:1.3rem;
    }
    .case-meta {
      display:flex; gap:15px; margin-bottom:15px; flex-wrap:wrap;
    }
    .meta-item { font-size:14px; }
    .action-btns {
      display:flex; gap:10px; margin-top:15px;
    }
    .btn {
      padding:8px 16px;
      border-radius:6px;
      text-decoration:none;
      font-weight:500;
      display:inline-flex;
      align-items:center;
      gap:8px;
      transition:all 0.3s ease;
    }
    .btn-primary {
      background:var(--primary);
      color:var(--dark);
    }
    .btn-primary:hover {
      background:var(--secondary);
      color:var(--light);
      transform:translateY(-2px);
    }
    .btn-secondary {
      background:rgba(255,255,255,0.1);
      color:var(--light);
      border:1px solid var(--glass-border);
    }
    .btn-secondary:hover {
      background:rgba(255,255,255,0.2);
      transform:translateY(-2px);
    }
    .empty-state {
      text-align:center;
      padding:40px;
      background:var(--glass);
      border-radius:12px;
      border:1px dashed var(--glass-border);
    }
    @media (max-width:1024px) {
      .sidebar { width:240px; padding:20px 15px; }
      .main-content {
        margin-left:240px;
        width:calc(100% - 240px);
        padding:30px;
      }
    }
    @media (max-width:768px) {
      .sidebar {
        width:100%; height:auto; position:relative;
        border-right:none; border-bottom:1px solid var(--glass-border);
      }
      .main-content { margin-left:0; width:100%; padding:25px; }
      .case-grid { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <div class="profile">
      <img src="../assets/images/profile.jpg" alt="Lawyer Profile">
      <h3>Lawyer Panel</h3>
    </div>
    <a href="lawyer.php">🏠 Dashboard</a>
    <a href="lawyer_cases.php">📂 My Cases</a>
    <a href="available_cases.php" class="active">🔍 Available Cases</a>
    <a href="lawyer_chats.php">💬 Chats</a>
    <a href="../backend/logout.php">🚪 Logout</a>
  </div>

  <div class="main-content">
    <div class="dashboard-header">
      <h1>Available Cases</h1>
      <form method="GET" class="filter-controls">
        <select name="type" class="filter-select">
          <option value="">All Case Types</option>
          <option value="divorce"  <?php if($type_filter==='divorce') echo 'selected'; ?>>Divorce</option>
          <option value="property" <?php if($type_filter==='property') echo 'selected'; ?>>Property</option>
          <option value="criminal"<?php if($type_filter==='criminal') echo 'selected'; ?>>Criminal</option>
          <option value="other"    <?php if($type_filter==='other') echo 'selected'; ?>>Other</option>
        </select>
        <select name="sort" class="filter-select">
          <option value="newest"<?php if($sort_by==='newest') echo 'selected'; ?>>Newest First</option>
          <option value="oldest"<?php if($sort_by==='oldest') echo 'selected'; ?>>Oldest First</option>
        </select>
        <button type="submit" class="btn btn-secondary">Apply Filters</button>
      </form>
    </div>

    <div class="case-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($case = $result->fetch_assoc()): ?>
          <div class="case-card">
            <h3><?php echo htmlspecialchars($case['title']); ?></h3>

            <div class="case-meta">
              <span class="meta-item"><strong>Type:</strong> <?php echo ucfirst($case['type']); ?></span>
              <span class="meta-item"><strong>Case ID:</strong> #<?php echo $case['id']; ?></span>
            </div>

            <?php
            // Use stored summary if available, otherwise generate one
            $summary = !empty($case['summary']) ? $case['summary'] : summarizeText($case['description']);
            ?>
            <p><?php echo htmlspecialchars($summary); ?></p>

            <div class="action-btns">
              <a href="bid_case.php?id=<?php echo $case['id']; ?>" class="btn btn-primary">✍️ Place Bid</a>
              <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-secondary">View Details</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <h3>No Available Cases</h3>
          <p>There are currently no open cases available for bidding.</p>
          <a href="available_cases.php" class="btn btn-primary">Reset Filters</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>