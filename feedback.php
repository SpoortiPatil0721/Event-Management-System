<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}
include "db_connect.php";

$user_id = $_SESSION['user_id'];

// fetch registered events
$sql = "SELECT id, event_name FROM event_registrations WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback | CampusBuzz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
  --primary: #ff77a9;
  --bg-dark: #121212;
  --bg-card: #1c1c1c;
  --text-light: #e0e0e0;
}

body {
  background-color: var(--bg-dark);
  color: var(--text-light);
  font-family: 'Poppins', sans-serif;
  margin: 0;
}

.dashboard {
  display: flex;
  flex-direction: row;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Sidebar / Navbar */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #2c2c2c, #3a0d3d);
  padding: 30px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  transition: transform 0.3s ease-in-out;
  z-index: 999;
}

.sidebar h3 {
  text-align: center;
  color: var(--primary);
  font-weight: 600;
  margin-bottom: 30px;
}

.sidebar a {
  color: var(--text-light);
  text-decoration: none;
  padding: 12px 15px;
  border-radius: 8px;
  margin-bottom: 12px;
  display: block;
  font-weight: 500;
  transition: 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: var(--primary);
  color: var(--bg-dark);
  transform: translateX(4px);
}

.logout-link {
  background: #ff4d6d;
  color: #fff;
  text-align: center;
  border-radius: 8px;
  padding: 10px;
}

.content {
  flex: 1;
  margin-left: 260px;
  padding: 40px;
  background: #181818;
}

.content h4 {
  color: var(--primary);
  font-weight: 600;
}

button,
.btn {
  border-radius: 8px !important;
  background: var(--primary) !important;
  border: none !important;
  color: var(--bg-dark) !important;
  font-weight: 500;
  transition: 0.3s;
}

button:hover,
.btn:hover {
  background: #e65f99 !important;
  transform: translateY(-2px);
}

select,
textarea {
  background: #2c2c2c !important;
  color: var(--text-light) !important;
  border: 1px solid var(--primary) !important;
  border-radius: 6px;
}

/* Responsive Sidebar Toggle */
.menu-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--primary);
  font-size: 1.8rem;
  margin-right: 15px;
}

/* Mobile Styles */
@media (max-width: 992px) {
  .dashboard {
    flex-direction: column;
  }

  .sidebar {
    transform: translateY(-100%);
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }

  .sidebar.active {
    transform: translateY(0);
  }

  .menu-toggle {
    display: block;
  }

  .content {
    margin: 0;
    padding: 20px;
  }

  .form-select,
  textarea {
    width: 100% !important;
  }
}

/* Accessibility */
label {
  display: block;
  margin-top: 1rem;
  font-weight: 500;
}

@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
/* =========================================================
   🟣 Web Accessibility & Text Responsiveness Enhancements
   (Non-destructive — no change to layout or functionality)
   ========================================================= */

/* Focus visibility for keyboard users */
:focus {
  outline: 3px solid var(--primary);
  outline-offset: 3px;
}

/* Ensure good color contrast for links & buttons on focus/hover */
a:focus-visible, button:focus-visible {
  outline: 2px dashed var(--primary);
  outline-offset: 4px;
}

/* Improve readability for text */
body {
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

/* Accessible form labels & inputs */
label {
  font-size: clamp(0.9rem, 1.2vw, 1rem);
  color: var(--text-light);
}
input, select, textarea {
  font-size: clamp(0.9rem, 1vw, 1rem);
}

/* Responsive heading & text scaling */
h3, h4 {
  font-size: clamp(1.2rem, 2.2vw, 1.6rem);
}
p, .text-muted, option {
  font-size: clamp(0.9rem, 1.2vw, 1rem);
}

/* Accessible contrast for placeholder text */
::placeholder {
  color: #bdbdbd;
  opacity: 1;
}

/* Make feedback alert messages accessible */
[role="alert"] {
  font-weight: 600;
  color: #00e676;
  text-align: center;
}

/* Improve tap targets for mobile (buttons, selects) */
button, .btn, select, textarea {
  min-height: 44px;
}

/* Reduce animations for motion-sensitive users */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
    animation: none !important;
  }
}

/* Additional small device font scaling */
@media (max-width: 576px) {
  body {
    font-size: 0.95rem;
  }
  .content h4 {
    font-size: 1.1rem;
    text-align: center;
  }
  label, button {
    font-size: 0.9rem;
  }
}

</style>
</head>

<body>
<div class="dashboard">

  <!-- Top Bar for Mobile -->
  <nav class="navbar navbar-dark d-lg-none" style="background:#2c2c2c;">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">☰</button>
      <span class="navbar-brand mb-0 h1 text-light">CampusBuzz</span>
      <a href="signin.html" class="text-light" style="text-decoration:none;">Logout</a>
    </div>
  </nav>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar" aria-label="Sidebar navigation">
    <div class="menu">
      <h3>CampusBuzz</h3>
      <a href="userdashboard.php">🏠 Home</a>
      <a href="event.php">🎫 Event Registration</a>
      <a href="attendance.php">📋 Attendance</a>
      <a href="certificate.php">📜 Certificates</a>
      <a href="feedback.php" class="active">⭐ Feedback</a>
    </div>
    <a href="signin.html" class="logout-link">🚪 Logout</a>
  </nav>

  <!-- Main Content -->
  <main class="content" role="main">
    <div class="container-fluid">
      <div class="card p-4 shadow-sm" style="background:var(--bg-card); border:none;">
        <h4 class="mb-3 text-center text-md-start">⭐ Share Your Feedback</h4>
        <p class="mb-4 text-muted text-center text-md-start">Select the event you participated in and share your thoughts.</p>

        <form method="POST" action="submit_feedback.php" aria-label="Feedback form">
          <div class="mb-3">
            <label for="event">Event</label>
            <select id="event" name="event_id" class="form-select" required>
              <option value="">-- Select Event --</option>
              <?php while ($row = $events->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>">
                  <?php echo htmlspecialchars($row['event_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="rating">Rating</label>
            <select id="rating" name="rating" class="form-select" required>
              <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
              <option value="4">⭐⭐⭐⭐ Good</option>
              <option value="3">⭐⭐⭐ Average</option>
              <option value="2">⭐⭐ Poor</option>
              <option value="1">⭐ Very Poor</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="comment">Comments</label>
            <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Write your feedback..." required></textarea>
          </div>

          <div class="text-center text-md-start">
            <button class="btn mt-2" type="submit">Submit Feedback</button>
          </div>
        </form>

        <?php if (isset($_GET['success'])): ?>
          <p class="mt-3 text-success text-center" role="alert">💖 Thanks for your feedback!</p>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
const toggleBtn = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('active');
});
</script>
</body>
</html>
