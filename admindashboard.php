<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: signin.html");
    exit();
}
include "db_connect.php";

$error = '';
$success = '';

if(isset($_POST['add_event'])){
    $name = trim($_POST['event_name']);
    $date = $_POST['event_date'];
    $location = trim($_POST['event_location']);
    $speakers = trim($_POST['event_speakers']);
    $contact = trim($_POST['event_contact']);
    $payment = $_POST['event_payment'];
    $description = trim($_POST['event_description']);
    
    // Validate date
    if($date < date('Y-m-d')){
        $error = "Event date cannot be in the past.";
    } elseif(!preg_match('/^[0-9]{10}$/', $contact)){
        $error = "Phone number must be 10 digits.";
    } elseif(isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK){
        $img = $_FILES['event_image'];
        $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if(!in_array(strtolower($ext), $allowed)){
            $error = "Invalid image type. Allowed: jpg, jpeg, png, webp.";
        } else {
            $img_name = uniqid().'_'.basename($img['name']);
            $target = 'uploads/'.$img_name;
            if(!move_uploaded_file($img['tmp_name'], $target)){
                $error = "Failed to upload image. Check folder permissions.";
            }
        }
    } else {
        $error = "Image is required.";
    }

    // If no error, insert into DB
    if(empty($error)){
        $stmt = $conn->prepare("INSERT INTO events (event_name,event_date,event_location,event_speakers,event_contact,event_payment,event_description,event_image) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss",$name,$date,$location,$speakers,$contact,$payment,$description,$img_name);
        if($stmt->execute()){
            $success = "Event added successfully!";
        } else {
            $error = "Database insert failed.";
        }
        $stmt->close();
    }
}


// Total Users
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];

// Total Events
$totalEvents = $conn->query("SELECT COUNT(*) as total FROM events")->fetch_assoc()['total'];

// Upcoming Events
$pendingEvents = $conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()")->fetch_assoc()['total'];

// Feedbacks
$totalFeedbacks = $conn->query("SELECT COUNT(*) as total FROM feedbacks")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
  --bg-dark: #121212;
  --bg-light: #181818;
  --card-bg: #1f1f1f;
  --sidebar-bg: linear-gradient(180deg, #2c2c2c, #3a0d3d);
  --primary: #ff77a9;
  --accent-hover: #e65f99;
  --text-light: #e0e0e0;
  --text-muted: #bbb;
  --shadow: rgba(0, 0, 0, 0.5);
}

body {
  background: var(--bg-dark);
  color: var(--text-light);
  font-family: "Poppins", sans-serif;
  margin: 0;
  transition: background 0.3s ease;
}

/* ================= Dashboard Layout ================= */
.dashboard {
  display: flex;
  min-height: 100vh;
  transition: all 0.3s ease;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: var(--sidebar-bg);
  padding: 30px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 1000;
  box-shadow: 4px 0 12px var(--shadow);
  transition: transform 0.3s ease;
}

.sidebar h3 {
  text-align: center;
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 30px;
  font-size: 1.4rem;
}

.sidebar a {
  display: block;
  color: var(--text-light);
  text-decoration: none;
  padding: 12px 15px;
  border-radius: 10px;
  margin-bottom: 12px;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.sidebar a:hover,
.sidebar a.active {
  background: var(--primary);
  color: var(--bg-dark);
  transform: translateX(4px);
}

.logout-link {
  background: #ff4d6d;
  color: #fff !important;
  text-align: center;
  border-radius: 10px;
  padding: 10px;
}

.logout-link:hover {
  background: #d43f5e;
  transform: translateY(-2px);
}

/* ================= Content ================= */
.content {
  flex: 1;
  padding: 40px;
  margin-left: 250px;
  background: var(--bg-light);
  transition: margin-left 0.3s ease;
}

.stats {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  flex: 1;
  min-width: 220px;
  background: var(--card-bg);
  border-radius: 16px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 4px 12px var(--shadow);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 6px 18px rgba(255, 119, 169, 0.3);
}

.stat-number {
  font-size: 36px;
  color: var(--primary);
  font-weight: bold;
}

.stat-label {
  color: var(--text-muted);
}

/* ================= Sections ================= */
.section {
  background: var(--card-bg);
  border-left: 5px solid var(--primary);
  border-radius: 16px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px var(--shadow);
  display: none;
}

.section.active {
  display: block;
}

/* ================= Tables ================= */
.table-container {
  overflow-x: auto;
  width: 100%;
  margin-top: 1rem;
  border-radius: 8px;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: #ff77a9 #1f1f1f;
  animation: dropIn 0.6s ease-out;
}

.table-container::-webkit-scrollbar {
  height: 6px;
}

.table-container::-webkit-scrollbar-thumb {
  background: #ff77a9;
  border-radius: 4px;
}

.table-dark {
  width: 100%;
  border-collapse: collapse;
  min-width: 700px; /* this is the key — keeps columns aligned but scrollable */
  border-radius: 8px;
  overflow: hidden;
}

.table-dark th {
  background-color: #ff77a9;
  color: #121212;
  text-align: left;
  padding: 12px;
  font-size: 0.95rem;
  white-space: nowrap;
}

.table-dark td {
  background-color: #2c2c2c;
  color: #eee;
  border-color: #444;
  padding: 12px;
  vertical-align: middle;
  white-space: nowrap;
}

.table-dark tr:hover td {
  background-color: #3a0d3d;
}

/* Responsive Fixes */
@media (max-width: 768px) {
  .table-dark {
    min-width: 500px;
  }
  .table-dark th,
  .table-dark td {
    font-size: 0.8rem;
    padding: 8px;
  }
}

/* Drop animation */
@keyframes dropIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ================= Buttons ================= */
button,
.btn {
  border-radius: 10px !important;
  background: var(--primary) !important;
  border: none !important;
  color: var(--bg-dark) !important;
  font-weight: 500;
  transition: 0.3s ease;
}

button:hover,
.btn:hover {
  background: var(--accent-hover) !important;
  transform: translateY(-2px);
}

/* ================= Navbar (Mobile) ================= */
.navbar {
  display: none;
  background: #2c2c2c;
  position: sticky;
  top: 0;
  z-index: 1100;
  padding: 12px 20px;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 8px var(--shadow);
}

.navbar .navbar-brand {
  color: var(--primary);
  font-weight: 600;
  font-size: 1.2rem;
}

.menu-toggle {
  background: none;
  border: none;
  color: var(--primary);
  font-size: 1.8rem;
  cursor: pointer;
}

.menu-toggle:focus {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* ================= Accessibility ================= */
a:focus,
button:focus,
input:focus,
textarea:focus,
select:focus {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}

/* ================= Responsive Text ================= */
@media (max-width: 1200px) {
  body {
    font-size: 0.95rem;
  }
  h3, h4 {
    font-size: 1.1rem;
  }
  .stat-number {
    font-size: 1.8rem;
  }
}

@media (max-width: 768px) {
  body {
    font-size: 0.9rem;
  }
  h3, h4 {
    font-size: 1rem;
  }
  .navbar .navbar-brand {
    font-size: 1rem;
  }
  .stat-number {
    font-size: 1.6rem;
  }
}

@media (max-width: 480px) {
  body {
    font-size: 0.85rem;
  }
  .stat-number {
    font-size: 1.4rem;
  }
  .sidebar a {
    font-size: 0.9rem;
  }
}

/* ================= Responsive ================= */
@media (max-width: 992px) {
  .navbar {
    display: flex;
  }

  .sidebar {
    transform: translateX(-100%);
    width: 220px;
    position: fixed;
    height: 100vh;
  }

  .sidebar.active {
    transform: translateX(0);
  }

  .content {
    margin-left: 0;
    padding: 20px;
  }

  .stats {
    flex-direction: column;
  }

  .table {
    font-size: 0.9rem;
  }
}

</style>
</head>
<body>
<nav class="navbar" role="navigation">
  <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>
  <span class="navbar-brand">Admin Dashboard</span>
  <a href="signin.html" class="logout-link">Logout</a>
</nav>


<div class="dashboard">
  <div class="sidebar">
    <div>
      <h3>CampusBuzz</h3>
      <a href="#" class="active" onclick="showSection('overview')">📊 Overview</a>
      <a href="#" onclick="showSection('users')">👥 Manage Users</a>
      <a href="#" onclick="showSection('events')">🎫 Manage Events</a>
      <a href="#" onclick="showSection('feedback')">⭐ Feedbacks</a>
    </div>
    <a href="signin.html" class="logout-link">🚪 Logout</a>
  </div>

  <div class="content">
    <h4>👋 Welcome, Admin!</h4>
    <p class="mb-4">Manage users, events, and feedbacks here.</p>

    <!-- Overview -->
    <div class="section active" id="overviewSection">
      <h2>System Overview</h2>
      <div class="stats">
        <div class="stat-card"><div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">Total Users</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $totalEvents; ?></div><div class="stat-label">Total Events</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $pendingEvents; ?></div><div class="stat-label">Upcoming Events</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $totalFeedbacks; ?></div><div class="stat-label">Feedbacks Received</div></div>
      </div>
    </div>

    <!-- Manage Users -->
    <div class="section" id="usersSection">
      <h2>Manage Users & Attendance</h2>
<div class="table-container">
      <table class="table table-dark table-hover">
        <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Event</th><th>Date</th><th>Attendance</th></tr></thead>
        <tbody>
          <?php
          $sql = "SELECT u.id AS user_id,u.name,u.email,e.id AS event_id,e.event_name,e.event_date,a.status AS attendance_status 
                  FROM users u
                  JOIN event_registrations e ON u.id=e.user_id
                  LEFT JOIN attendance a ON a.user_id=u.id AND a.event_id=e.id
                  ORDER BY u.id,e.event_date DESC";
          $res = $conn->query($sql);
          if($res && $res->num_rows>0){
              while($r=$res->fetch_assoc()){
                  echo "<tr>
                      <td>{$r['user_id']}</td>
                      <td>{$r['name']}</td>
                      <td>{$r['email']}</td>
                      <td>{$r['event_name']}</td>
                      <td>{$r['event_date']}</td>
                      <td>
                        <button class='btn btn-success btn-sm' onclick=\"markAttendance({$r['user_id']},{$r['event_id']},'Present')\">Present</button>
                        <button class='btn btn-danger btn-sm' onclick=\"markAttendance({$r['user_id']},{$r['event_id']},'Absent')\">Absent</button>
                        <span id='status_{$r['user_id']}_{$r['event_id']}' style='margin-left:10px;color:#ff77a9;'>".($r['attendance_status'] ?? '')."</span>
                      </td>
                  </tr>";
              }
          } else echo "<tr><td colspan='6' class='text-center'>No event registrations found.</td></tr>";
          ?>
        </tbody>
      </table>
    </div>
</div>

    <!-- Manage Events -->
    <div class="section" id="eventsSection">
      <h2>Manage Events</h2>
      <form id="addEventForm" enctype="multipart/form-data" class="mb-4">
<?php if($error): ?>
  <div style="color:red;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if($success): ?>
  <div style="color:green;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

        <input type="text" name="event_name" class="form-control mb-2" placeholder="Event Name" required>
        <input type="date" name="event_date" class="form-control mb-2" min="<?= date('Y-m-d') ?>" required>
        <textarea name="event_description" class="form-control mb-2" placeholder="Description"></textarea>
        <input type="text" name="event_location" class="form-control mb-2" placeholder="Location">
        <input type="text" name="event_speakers" class="form-control mb-2" placeholder="Speakers">
        <input type="text" name="event_contact" class="form-control mb-2" pattern="\d{10}" placeholder="Contact">
        <input type="number" step="0.01" name="event_payment" class="form-control mb-2" placeholder="Payment">
        <input type="file" name="event_image" accept=".jpg,.jpeg,.png,.webp" class="form-control mb-2">
        <button type="submit" name="add_event" class="btn w-100">Add Event</button>
      </form>
      <div id="eventList"></div>
    </div>

    <!-- Feedbacks -->
    <div class="section" id="feedbackSection">
      <h2>Student Feedback</h2>
      <?php
      $f = $conn->query("SELECT f.id,u.name AS user_name,f.event_name,f.rating,f.comment,f.created_at 
                         FROM feedbacks f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC");
      if($f && $f->num_rows>0){
          echo '<table class="table table-dark table-striped"><thead><tr><th>ID</th><th>Student</th><th>Event</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead><tbody>';
          while($row=$f->fetch_assoc()){
              echo "<tr>
                <td>{$row['id']}</td>
                <td>".htmlspecialchars($row['user_name'])."</td>
                <td>".htmlspecialchars($row['event_name'])."</td>
                <td>".str_repeat('⭐',$row['rating'])."</td>
                <td>".htmlspecialchars($row['comment'])."</td>
                <td>{$row['created_at']}</td>
              </tr>";
          }
          echo '</tbody></table>';
      } else echo "<p>No feedback yet.</p>";
      ?>
    </div>
  </div>
</div>

<script>
function showSection(section){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.getElementById(section+'Section').classList.add('active');
}

function markAttendance(userId,eventId,status){
    fetch('update_attendance.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`user_id=${userId}&event_id=${eventId}&status=${status}`
    }).then(r=>r.text()).then(d=>{
        document.getElementById(`status_${userId}_${eventId}`).innerText=status;
        alert(d);
    });
}

// Load events
async function loadAdminEvents(){
    const res = await fetch('fetch_events.php');
    const data = await res.json();
    const list = document.getElementById('eventList');
    list.innerHTML = '';
    data.events.forEach(e=>{
        list.innerHTML += `
        <div class="p-3 mb-2 rounded" style="background:#2c2c2c;">
          <img src="uploads/${e.event_image}" style="width:100px;height:60px;object-fit:cover;margin-right:10px;float:left;">
          <strong>${e.event_name}</strong> (${e.event_date}) - ${e.event_location||''}
          <br><small>${e.event_description||''}</small>
          <button class="btn btn-sm btn-danger float-end" onclick="deleteEvent(${e.id})">Delete</button>
          <div style="clear:both;"></div>
        </div>`;
    });
}

async function deleteEvent(id){
    if(confirm("Delete this event?")){
        const r = await fetch(`delete_event.php?id=${id}`);
        if((await r.text()).includes("success")) loadAdminEvents();
    }
}

// Add event form
document.getElementById('addEventForm').onsubmit = async e=>{
    e.preventDefault();
    const formData = new FormData(e.target);
    const r = await fetch('add_event.php',{method:'POST',body:formData});
    const text = await r.text();
    if(text.includes('success')){ e.target.reset(); loadAdminEvents(); } else alert(text);
}

loadAdminEvents();

const menuToggle = document.getElementById('menuToggle');
const sidebar = document.querySelector('.sidebar');

if (menuToggle && sidebar) {
  menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
  });

  // Close sidebar when a link is clicked (on mobile)
  document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 992) {
        sidebar.classList.remove('active');
      }
    });
  });
}



</script>
</body>
</html>
