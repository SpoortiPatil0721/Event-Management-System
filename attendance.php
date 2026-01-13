<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}
include "db_connect.php";

$user_id = $_SESSION['user_id'];

// Fetch attendance for this user
$sql = "SELECT e.event_name, e.event_date, a.status 
        FROM event_registrations e
        LEFT JOIN attendance a ON a.user_id=? AND a.event_id=e.id
        WHERE e.user_id=? 
        ORDER BY e.event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance | CampusBuzz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #121212;
        color: #e0e0e0;
        font-family: 'Poppins', sans-serif;
        margin: 0;
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        background: linear-gradient(180deg, #2c2c2c, #3a0d3d);
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        transition: left 0.3s ease;
        z-index: 1000;
    }

    .menu {
        display: flex;
        flex-direction: column;
    }

    .sidebar h3 {
        text-align: center;
        font-weight: 600;
        color: #ff77a9;
        margin-bottom: 30px;
        font-size: 1.4rem;
    }

    .sidebar a {
        display: block;
        color: #e0e0e0;
        text-decoration: none;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background-color: #ff77a9;
        color: #121212;
        transform: translateX(4px);
    }

    .logout-link {
        background: #ff4d6d;
        color: #fff;
        text-align: center;
    }

    .logout-link:hover {
        background: #d43f5e;
        transform: translateY(-2px);
        color: #fff;
    }

    /* Content */
    .content {
        flex: 1;
        padding: 40px;
        margin-left: 240px;
        background-color: #181818;
        transition: margin-left 0.3s;
    }

    th {
        background-color: #ff77a9;
        color: #121212;
    }

    .table-dark th {
        background-color: #ff77a9;
        color: #121212;
    }

    .table-dark td {
        background-color: #2c2c2c;
    }

    /* Toggle button for mobile */
    .menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1101;
        background: #ff77a9;
        color: #121212;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        font-weight: bold;
    }

    /* Mobile View */
    @media (max-width: 991px) {
        .sidebar {
            left: -240px;
            width: 240px;
        }

        .sidebar.show {
            left: 0;
        }

        .menu-toggle {
            display: block;
        }

        .content {
            margin-left: 0;
            padding: 80px 20px 20px;
        }
    }

    @media (max-width: 576px) {
        h4 { font-size: 18px; }
        table { font-size: 14px; }
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }
    /* ======================================================
   ♿ Web Accessibility & Text Responsiveness Enhancements
   (Non-breaking — layout, structure & responsiveness preserved)
   ====================================================== */

/* Ensure good color contrast & readable text */
body {
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

/* Improve focus visibility for keyboard users */
:focus {
  outline: 3px solid #ff77a9;
  outline-offset: 3px;
}

/* Buttons & links focus styles */
button:focus-visible,
a:focus-visible {
  outline: 3px dashed #ff77a9;
  outline-offset: 4px;
}

/* Improve heading and label readability */
h3, h4 {
  font-size: clamp(1.2rem, 2.2vw, 1.6rem);
  font-weight: 600;
  color: #ffffff;
}

/* Responsive text for table content */
table, td, th {
  font-size: clamp(0.85rem, 1.1vw, 1rem);
}

/* Ensure alert text or dynamic status updates are readable */
[role="alert"] {
  font-weight: 600;
  color: #00e676;
}

/* Maintain accessible tap target sizes */
a, button {
  min-height: 44px;
  min-width: 44px;
}

/* Accessible placeholder color contrast */
::placeholder {
  color: #bdbdbd;
  opacity: 1;
}

/* Reduce animations for motion-sensitive users */
@media (prefers-reduced-motion: reduce) {
  * {
    animation: none !important;
    transition: none !important;
  }
}

/* Small devices - scale down text gracefully */
@media (max-width: 576px) {
  h3, h4 {
    font-size: clamp(1rem, 4vw, 1.2rem);
    text-align: center;
  }
  td, th {
    font-size: 0.9rem;
  }
  body {
    font-size: 0.95rem;
  }
}

</style>
</head>
<body>

<button class="menu-toggle d-lg-none" onclick="toggleSidebar()">☰</button>

<div class="dashboard">
    <div class="sidebar" id="sidebar">
        <div class="menu">
            <h3>CampusBuzz</h3>
            <a href="userdashboard.php">🏠 Home</a>
            <a href="event.php">🎫 Event Registration</a>
            <a href="attendance.php" class="active">📋 Attendance</a>
            <a href="certificate.php">📜 Certificates</a>
            <a href="feedback.php">⭐ Feedback</a>
        </div>
        <a href="signin.html" class="logout-link">🚪 Logout</a>
    </div>

    <div class="content">
        <h4>📋 Attendance Record</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-3 table-dark">
                <thead>
                    <tr><th>Event</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'] ?? 'Not marked';
        $class = ($status == 'Present') ? 'text-success' : (($status == 'Absent') ? 'text-danger' : 'text-warning');
        echo "<tr>
                <td>{$row['event_name']}</td>
                <td>{$row['event_date']}</td>
                <td><span class='{$class}'>{$status}</span></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3' class='text-center'>No attendance records found.</td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
}
</script>

</body>
</html>
