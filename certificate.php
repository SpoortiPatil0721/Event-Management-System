<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}
include 'db_connect.php';

$user_id = intval($_SESSION['user_id']);
$user = $conn->query("SELECT name FROM users WHERE id = $user_id")->fetch_assoc()['name'] ?? 'User';

// Fetch events
$sql = "SELECT id AS event_id, event_name, event_date FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
$events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificates | CampusBuzz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

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
    top: 0; left: 0; bottom: 0;
    transition: left 0.3s ease;
    z-index: 1000;
}

.sidebar h3 {
    text-align: center;
    font-weight: 600;
    color: #ff77a9;
    margin-bottom: 30px;
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
}

.sidebar a:hover, .sidebar a.active {
    background-color: #ff77a9;
    color: #121212;
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

/* Event Cards */
.event-card {
    background: #1f1f1f;
    border-left: 4px solid #ff77a9;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

/* Buttons */
button {
    border-radius: 8px !important;
    background: #ff77a9 !important;
    border: none !important;
    color: #121212 !important;
    font-weight: 500;
    transition: 0.3s;
}
button:hover {
    background: #e65f99 !important;
    transform: translateY(-2px);
}

/* Mobile Sidebar Toggle */
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

/* Responsive */
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
    .event-card { text-align: center; }
    .event-card h5 { font-size: 1rem; }
    button { width: 100%; margin-top: 10px; }
}
/* --- Event text responsive fix for mobile --- */
@media (max-width: 768px) {
    .event-card h5 {
        font-size: 1rem;
        word-wrap: break-word;
        text-align: center;
    }
    .event-card p {
        font-size: 0.9rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .event-card h5 {
        font-size: 0.95rem;
        line-height: 1.3;
    }
    .event-card p {
        font-size: 0.85rem;
    }
}

</style>
</head>
<body>

<!-- Skip to main content for accessibility -->
<a href="#mainContent" class="visually-hidden-focusable" style="position:absolute;left:-999px;top:auto;width:1px;height:1px;overflow:hidden;">
  Skip to main content
</a>

<!-- Menu toggle for mobile -->
<button class="menu-toggle d-lg-none" aria-label="Toggle menu" onclick="toggleSidebar()" aria-controls="sidebar" aria-expanded="false">☰</button>

<div class="dashboard">
    <nav class="sidebar" id="sidebar" role="navigation" aria-label="Sidebar Menu">
        <div class="menu">
            <h3 id="appTitle">CampusBuzz</h3>
            <a href="userdashboard.php" aria-label="Go to Home">🏠 Home</a>
            <a href="event.php" aria-label="Go to Event Registration">🎫 Event Registration</a>
            <a href="attendance.php" aria-label="Go to Attendance Page">📋 Attendance</a>
            <a href="certificate.php" class="active" aria-current="page" aria-label="Current page: Certificates">📜 Certificates</a>
            <a href="feedback.php" aria-label="Go to Feedback">⭐ Feedback</a>
        </div>
        <a href="signin.html" class="logout-link" role="button" aria-label="Logout">🚪 Logout</a>
    </nav>

    <main class="content" id="mainContent" role="main" tabindex="-1">
        <h4 class="mb-4">📜 Your Certificates</h4>

        <?php if (empty($events)): ?>
            <div class="alert alert-warning" role="alert">No events found. Ask your admin to add some events first.</div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <article class="event-card" aria-labelledby="event-<?php echo $event['event_id']; ?>">
                    <h5 id="event-<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                    <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                    <button type="button" aria-label="Generate certificate for <?php echo htmlspecialchars($event['event_name']); ?>"
                        onclick="generateCertificate('<?php echo addslashes($user); ?>', '<?php echo addslashes($event['event_name']); ?>', '<?php echo $event['event_date']; ?>')">
                        Generate Certificate
                    </button>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.querySelector('.menu-toggle');
    const isExpanded = sidebar.classList.toggle('show');
    toggleButton.setAttribute('aria-expanded', isExpanded);
}

async function generateCertificate(name, eventName, eventDate) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: "landscape" });
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    doc.setDrawColor(150, 50, 200);
    doc.setLineWidth(4);
    doc.rect(10, 10, pageWidth - 20, pageHeight - 20);

    doc.setFont("Helvetica", "bold");
    doc.setFontSize(28);
    doc.text("Certificate of Participation", pageWidth / 2, 50, { align: "center" });

    doc.setFont("Helvetica", "normal");
    doc.setFontSize(16);
    doc.text(`This is to certify that`, pageWidth / 2, 80, { align: "center" });

    doc.setFont("Helvetica", "bold");
    doc.setFontSize(22);
    doc.text(name, pageWidth / 2, 100, { align: "center" });

    doc.setFont("Helvetica", "normal");
    doc.setFontSize(16);
    doc.text(`has successfully participated in the event`, pageWidth / 2, 120, { align: "center" });
    doc.setFont("Helvetica", "bold");
    doc.text(`"${eventName}"`, pageWidth / 2, 135, { align: "center" });
    doc.setFont("Helvetica", "normal");
    doc.text(`held on ${eventDate}.`, pageWidth / 2, 150, { align: "center" });

    doc.setFont("Helvetica", "italic");
    doc.text("Authorized Signature", pageWidth - 50, pageHeight - 30, { align: "center" });

    doc.setFontSize(10);
    doc.text("CampusBuzz © " + new Date().getFullYear(), pageWidth / 2, pageHeight - 15, { align: "center" });

    doc.save(`${eventName}_Certificate.pdf`);
}
</script>
</body>
</html>
