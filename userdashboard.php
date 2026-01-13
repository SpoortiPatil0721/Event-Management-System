<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}
include "db_connect.php";

$user_id = intval($_SESSION['user_id']);
$today = date('Y-m-d');

// Fetch user info
$sqlUser = "SELECT name, application_no FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

// Stats
$sqlRegistered = "SELECT COUNT(*) AS count FROM event_registrations WHERE user_id = ?";
$stmt = $conn->prepare($sqlRegistered);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registeredCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$sqlOngoing = "SELECT COUNT(*) AS count 
               FROM event_registrations r 
               JOIN events e ON r.event_id = e.id 
               WHERE r.user_id = ? AND e.event_date >= ?";
$stmt = $conn->prepare($sqlOngoing);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$ongoingCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$sqlCompleted = "SELECT COUNT(*) AS count 
                 FROM event_registrations r
                 JOIN events e ON r.event_id = e.id
                 JOIN attendance a ON a.event_id = e.id AND a.user_id = r.user_id
                 WHERE r.user_id = ? 
                 AND a.status = 'Present'";
$stmt = $conn->prepare($sqlCompleted);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completedCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$sqlUpcoming = "SELECT COUNT(*) AS count FROM events WHERE event_date >= ?";
$stmt = $conn->prepare($sqlUpcoming);
$stmt->bind_param("s", $today);
$stmt->execute();
$upcomingCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

// Registered events
$sqlRegisteredEvents = "
    SELECT e.event_name, e.event_date, r.payment_method, r.created_at
    FROM event_registrations r
    LEFT JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    ORDER BY e.event_date DESC
";
$stmt = $conn->prepare($sqlRegisteredEvents);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registeredEvents = $stmt->get_result();
$stmt->close();

// Upcoming events
$sqlUpcomingEvents = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
$stmt = $conn->prepare($sqlUpcomingEvents);
$stmt->bind_param("s", $today);
$stmt->execute();
$upcomingEvents = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | CampusBuzz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#121212; color:#e0e0e0; font-family:'Poppins',sans-serif; margin:0; }
.dashboard { display:flex; min-height:100vh; flex-wrap:wrap; }

/* Sidebar */
.sidebar { width:240px; background:linear-gradient(180deg,#2c2c2c,#3a0d3d); padding:30px 20px; display:flex; flex-direction:column; justify-content:space-between; position:fixed; top:0; left:0; bottom:0; transition:all 0.3s ease; z-index:1000; }
.sidebar a { color:#e0e0e0; text-decoration:none; padding:12px 15px; border-radius:8px; margin-bottom:12px; display:block; font-size:15px; }
.sidebar a.active { background:#ff77a9; color:#121212; }
.sidebar .logout-link { background:#ff4d6d; color:#fff; text-align:center; padding:10px; border-radius:8px; text-decoration:none; }

/* Content */
.content { flex:1; padding:40px; margin-left:240px; background:#181818; transition:all 0.3s ease; width:calc(100% - 240px); }
.profile-card { background:#1f1f1f; border-left:5px solid #ff77a9; border-radius:12px; padding:25px; margin-bottom:30px; }
.event-stats { display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap; }
.event-card { flex:1; min-width:200px; background:linear-gradient(135deg,#2c2c2c,#3a0d3d); padding:20px; border-radius:12px; text-align:center; }
.event-card h2 { color:#ff77a9; font-size:36px; margin-bottom:10px; }

/* Tables */
.table-dark th { background-color:#ff77a9; color:#121212; }
.table-dark td { background-color:#2c2c2c; }

/* Responsive */
@media (max-width:991px) {
  .sidebar {
    position:fixed;
    width:200px;
    left:-200px;
    top:0; bottom:0;
  }
  .sidebar.show {
    left:0;
  }
  .menu-toggle {
    display:block;
    position:fixed;
    top:15px;
    left:15px;
    z-index:1100;
    background:#ff77a9;
    color:#121212;
    border:none;
    border-radius:6px;
    padding:8px 12px;
    font-weight:bold;
  }
  .content {
    margin-left:0;
    width:100%;
    padding:80px 20px 20px;
  }
}

@media (max-width:576px) {
  .event-card h2 { font-size:28px; }
  .event-card p { font-size:13px; }
  .profile-card { padding:18px; }
  h4 { font-size:18px; }
}

.table-responsive { overflow-x:auto; }

/* Accessibility Focus */
a:focus, button:focus {
  outline: 2px solid #ff77a9;
  outline-offset: 2px;
}
</style>
</head>
<body>
<button class="menu-toggle d-lg-none" onclick="toggleSidebar()" aria-label="Toggle sidebar menu">☰</button>

<div class="dashboard">
  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar" role="navigation" aria-label="Main Menu">
    <div>
      <h3 style="color:#ff77a9;text-align:center">CampusBuzz</h3>
      <a href="userdashboard.php" class="active" aria-current="page">🏠 Home</a>
      <a href="event.php">🎫 Event Registration</a>
      <a href="attendance.php">📋 Attendance</a>
      <a href="certificate.php">📜 Certificates</a>
      <a href="feedback.php">⭐ Feedback</a>
    </div>
    <a href="signin.html" class="logout-link" aria-label="Logout">🚪 Logout</a>
  </nav>

  <!-- Main Content -->
  <main class="content" role="main">
    <section class="profile-card" aria-labelledby="welcome-heading">
      <h4 id="welcome-heading">Welcome back, <span style="color:#ff77a9;"><?php echo htmlspecialchars($user['name']); ?></span>!</h4>
      <p><strong>Application No:</strong> <?php echo htmlspecialchars($user['application_no']); ?></p>
    </section>

    <section class="event-stats" aria-label="Event Statistics">
      <div class="event-card" role="status"><h2><?php echo $registeredCount; ?></h2><p>Registered Events</p></div>
      <div class="event-card" role="status"><h2><?php echo $ongoingCount; ?></h2><p>Ongoing Events</p></div>
      <div class="event-card" role="status"><h2><?php echo $upcomingCount; ?></h2><p>Upcoming Events</p></div>
    </section>

    <h4 style="color:#ff77a9;">🎫 Your Registered Events</h4>
    <div class="table-responsive">
      <table class="table table-dark table-bordered table-hover mt-2" role="table" aria-label="Registered Events Table">
        <thead>
          <tr><th scope="col">Event Name</th><th scope="col">Date</th><th scope="col">Payment Method</th><th scope="col">Registered On</th></tr>
        </thead>
        <tbody>
          <?php if($registeredEvents->num_rows > 0): ?>
            <?php while($row = $registeredEvents->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['event_name'] ?? 'Deleted Event'); ?></td>
                <td><?php echo htmlspecialchars($row['event_date'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">No events registered yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <h4 style="color:#ff77a9;">📅 Upcoming Events</h4>
    <div class="table-responsive">
      <table class="table table-dark table-bordered table-hover mt-2" role="table" aria-label="Upcoming Events Table">
        <thead>
          <tr><th scope="col">Event Name</th><th scope="col">Date</th><th scope="col">Location</th></tr>
        </thead>
        <tbody>
          <?php while($row = $upcomingEvents->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['event_name']); ?></td>
              <td><?php echo htmlspecialchars($row['event_date']); ?></td>
              <td><?php echo htmlspecialchars($row['event_location']); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('show');
}
</script>
</body>
</html>
<?php
$conn->close();
?>
