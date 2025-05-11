<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. <a href='../login.html'>Login</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LawLink | Admin Dashboard</title>
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

    /* Sidebar */
    .sidebar {
      width:280px;
      background:rgba(0,0,0,0.7);
      backdrop-filter:blur(10px);
      padding:30px 20px;
      height:100vh;
      position:fixed;
      border-right:1px solid var(--glass-border);
    }
    .sidebar .profile {
      text-align:center;
      margin-bottom:30px;
    }
    .sidebar .profile img {
      width:80px; height:80px;
      border-radius:50%; object-fit:cover;
      border:2px solid var(--primary);
      margin-bottom:10px;
    }
    .sidebar .profile h3 {
      color:var(--primary);
      font-size:18px;
    }
    .sidebar a {
      display:flex; align-items:center;
      gap:8px;
      color:rgba(255,255,255,0.8);
      text-decoration:none;
      padding:12px 15px;
      margin-bottom:10px;
      border-radius:8px;
      transition:all 0.3s;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background:var(--primary);
      color:var(--dark);
    }

    /* Main content */
    .main-content {
      margin-left:280px;
      padding:40px;
      width:calc(100% - 280px);
    }
    h1 {
      color:var(--primary);
      margin-bottom:30px;
      text-shadow:0 0 10px rgba(0,249,255,0.5);
    }

    /* Sections */
    .section {
      background:var(--glass);
      backdrop-filter:blur(10px);
      border:1px solid var(--glass-border);
      border-radius:16px;
      padding:30px;
      margin-bottom:40px;
      box-shadow:0 8px 32px rgba(0,0,0,0.2);
    }
    .section h2 {
      margin-bottom:20px;
      color:var(--primary);
    }

    /* Tables */
    table {
      width:100%;
      border-collapse:collapse;
      background:var(--glass);
      backdrop-filter:blur(10px);
      border:1px solid var(--glass-border);
      border-radius:12px;
      overflow:hidden;
    }
    table th, table td {
      padding:12px;
      text-align:left;
      color:var(--light);
      font-size:14px;
    }
    table th {
      background:rgba(0,0,0,0.3);
      font-weight:500;
    }
    table tr:nth-child(even) td {
      background:rgba(255,255,255,0.05);
    }
    .action-link {
      color:var(--primary);
      text-decoration:none;
      font-weight:500;
      transition:all 0.3s;
      position:relative;
    }
    .action-link:hover {
      color:var(--secondary);
    }

    @media (max-width:1024px) {
      .sidebar { width:240px; }
      .main-content {
        margin-left:240px;
        width:calc(100% - 240px);
        padding:30px;
      }
    }
    @media (max-width:768px) {
      .sidebar {
        width:100%; height:auto;
        position:relative;
        border-right:none;
        border-bottom:1px solid var(--glass-border);
      }
      .main-content {
        margin-left:0;
        width:100%;
        padding:25px;
      }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="profile">
      <img src="../assets/images/profile.jpg" alt="Admin Profile">
      <h3>Admin Panel</h3>
    </div>
    <a href="#users" class="active">👥 Users</a>
    <a href="#cases">📂 Cases</a>
    <a href="../backend/logout.php">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h1>Welcome, Admin</h1>

    <!-- Users Section -->
    <div id="users" class="section">
      <h2>👥 All Users</h2>
      <table>
        <tr>
          <th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Action</th>
        </tr>
        <?php
        $users = mysqli_query($conn, "SELECT * FROM users");
        while ($user = mysqli_fetch_assoc($users)) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($user['id'])."</td>";
            echo "<td>".htmlspecialchars($user['name'])."</td>";
            echo "<td>".htmlspecialchars($user['email'])."</td>";
            echo "<td>".htmlspecialchars($user['username'])."</td>";
            echo "<td>".htmlspecialchars($user['role'])."</td>";
            echo "<td><a class='action-link' href='../backend/delete_user.php?id={$user['id']}' onclick=\"return confirm('Are you sure?')\">Delete</a></td>";
            echo "</tr>";
        }
        ?>
      </table>
    </div>

    <!-- Cases Section -->
    <div id="cases" class="section">
      <h2>📂 All Cases</h2>
      <table>
        <tr>
          <th>ID</th><th>Title</th><th>Type</th><th>Status</th><th>Client ID</th><th>Action</th>
        </tr>
        <?php
        $cases = mysqli_query($conn, "SELECT * FROM cases ORDER BY id DESC");
        while ($case = mysqli_fetch_assoc($cases)) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($case['id'])."</td>";
            echo "<td><a class='action-link' href='view_case_admin.php?id={$case['id']}'>".htmlspecialchars($case['title'])."</a></td>";
            echo "<td>".htmlspecialchars($case['type'])."</td>";
            echo "<td>".htmlspecialchars($case['status'])."</td>";
            echo "<td>".htmlspecialchars($case['client_id'])."</td>";
            echo "<td><a class='action-link' href='../backend/delete_case.php?id={$case['id']}' onclick=\"return confirm('Delete this case?')\">Delete</a></td>";
            echo "</tr>";
        }
        mysqli_close($conn);
        ?>
      </table>
    </div>
  </div>

</body>
</html>
