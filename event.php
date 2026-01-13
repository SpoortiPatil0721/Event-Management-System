<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}
include "db_connect.php";

$today = date('Y-m-d');
$sql = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CampusBuzz | Events</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
body { 
  font-family:"Segoe UI",Arial,sans-serif; 
  background:#121212; 
  color:#eee;
  margin:0;
  display:flex; 
  min-height:100vh;
  overflow-x:hidden;
}
.sidebar { 
  width:240px; 
  background: linear-gradient(180deg,#2c2c2c,#3a0d3d); 
  padding:30px 20px; 
  display:flex; 
  flex-direction:column; 
  justify-content:space-between; 
  position:fixed; 
  top:0; 
  left:0; 
  bottom:0; 
  transition:left 0.3s ease;
  z-index:1100;
}
.sidebar h3 { 
  text-align:center; 
  font-weight:600; 
  color:#ff77a9; 
  margin-bottom:30px; 
  font-size:1.4rem;
}
.sidebar a { 
  display:block; 
  color:#e0e0e0; 
  text-decoration:none; 
  padding:12px 15px; 
  border-radius:8px; 
  margin-bottom:12px; 
  font-weight:500; 
  font-size:0.95rem; 
  transition:all 0.3s;
}
.sidebar a:hover { 
  background:#ff77a9; 
  color:#121212; 
  transform:translateX(4px);
}
.sidebar a.active { 
  background:#d43f5e; 
  color:#fff;
}
.logout-link { 
  background:#ff4d6d; 
  color:#fff; 
  text-align:center; 
  margin-top:20px;
}
.main { 
  margin-left:260px; 
  padding:40px; 
  width:calc(100% - 260px);
  transition:margin-left 0.3s ease;
}
h1 { 
  text-align:center; 
  color:#fff; 
  margin-bottom:40px; 
  font-size:2rem;
}
.grid { 
  display:grid; 
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); 
  gap:25px;
}
.card { 
  background:#1e1e1e; 
  border-radius:15px; 
  overflow:hidden; 
  box-shadow:0 6px 15px rgba(255,119,169,0.15); 
  cursor:pointer; 
  transition:0.3s;
}
.card img { 
  width:100%; 
  height:200px; 
  object-fit:cover;
}
.card .info { 
  padding:18px; 
  text-align:center;
}
.card h2 { 
  font-size:1.2rem; 
  margin:0 0 6px; 
  color:#fff;
}
.card p { 
  font-size:0.9rem; 
  color:#ff77a9; 
  margin:0; 
  font-weight:500;
}
.card:hover { 
  transform:translateY(-8px); 
  box-shadow:0 12px 25px rgba(255,119,169,0.4);
}
.modal { 
  display:none; 
  position:fixed; 
  top:0; 
  left:0; 
  width:100%; 
  height:100%; 
  background:rgba(0,0,0,0.8); 
  justify-content:center; 
  align-items:center; 
  z-index:1200;
}
.modal-content { 
  background:#1e1e1e; 
  color:#eee; 
  border-radius:12px; 
  padding:20px; 
  max-width:600px; 
  width:90%; 
  position:relative; 
  animation:fadeIn 0.3s; 
  box-shadow:0 6px 20px rgba(0,0,0,0.9);
}
.modal-content img { 
  width:100%; 
  border-radius:8px; 
  margin-bottom:15px;
}
.close {
  position: absolute;
  top: 10px;
  right: 15px;          /* move it to the right side */
  color: #ff77a9;       /* your theme color */
  background: transparent;
  border: none;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.close:hover {
  transform: scale(1.2);
  color: #fff;
}


input, select { 
  width:100%; 
  padding:8px; 
  margin-bottom:10px; 
  border-radius:6px; 
  border:1px solid #555; 
  background:#2a2a2a; 
  color:#fff;
}
.register-btn { 
  display:block; 
  width:100%; 
  padding:12px; 
  background:#007BFF; 
  color:#fff; 
  border:none; 
  border-radius:8px; 
  font-size:16px; 
  cursor:pointer; 
  margin-top:15px;
}
.register-btn:hover { background:#0056b3; }

@keyframes fadeIn { 
  from {opacity:0; transform:scale(0.9);} 
  to {opacity:1; transform:scale(1);} 
}

/* ===== Responsive Styles ===== */
@media (max-width:991px){
  .sidebar {
    width:220px;
    left:-220px;
  }
  .sidebar.show {
    left:0;
  }
  .menu-toggle {
    display:block;
    position:fixed;
    top:15px;
    left:15px;
    z-index:1300;
    background:#ff77a9;
    color:#121212;
    border:none;
    border-radius:6px;
    padding:8px 12px;
    font-weight:bold;
    font-size:18px;
  }
  .main {
    margin:0;
    width:100%;
    padding:80px 20px 20px;
  }
  h1 {
    font-size:1.5rem;
  }
  .card img {
    height:180px;
  }
}

@media (max-width:576px){
  .grid {
    grid-template-columns:1fr;
    gap:15px;
  }
  .card {
    border-radius:10px;
  }
  .card h2 {
    font-size:1rem;
  }
  .card p {
    font-size:0.8rem;
  }
  .modal-content {
    width:95%;
    padding:15px;
  }
  input, select {
    font-size:14px;
  }
  .register-btn {
    font-size:15px;
    padding:10px;
  }
  .menu-toggle {
    top:12px;
    left:12px;
    padding:6px 10px;
    font-size:16px;
  }
}

/* ===== Make registration modal responsive ===== */
@media (max-width: 768px) {
  #regModal .modal-content {
    width: 95% !important;
    padding: 15px !important;
    max-height: 90vh;
    overflow-y: auto;
  }
  #regModal input,
  #regModal select {
    font-size: 14px;
    padding: 8px;
  }
  #regModal label {
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
  }
  #regModal h2 {
    font-size: 1.2rem;
    text-align: center;
  }
  #regModal .register-btn {
    font-size: 15px;
    padding: 10px;
    width: 100%;
  }
}

@media (max-width: 480px) {
  #regModal .modal-content {
    width: 92% !important;
    padding: 12px !important;
  }
  #regModal input,
  #regModal select {
    font-size: 13px;
  }
  #regModal .register-btn {
    font-size: 14px;
    padding: 8px;
  }
}
/* ===== Fix for hidden heading in registration modal ===== */
#regModal {
  display: none; /* stays hidden by default */
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.8);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

#regModal.show {
  display: flex; /* ensures modal centers vertically & horizontally */
}

#regModal .modal-content {
  background: #1f1f1f;
  border-radius: 12px;
  padding: 25px 30px;
  max-width: 500px;
  width: 90%;
  color: #e0e0e0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.6);
  max-height: 90vh;
  overflow-y: auto;          /* Keep scrolling active */
  -ms-overflow-style: none;  /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari and Opera */
#regModal .modal-content::-webkit-scrollbar {
  display: none;
}

/* Make sure the title never gets hidden */
#regModal h2 {
  margin-top: 0;
  padding-top: 10px;
  text-align: center;
  color: #ff77a9;
  font-weight: 600;
}
/* ===== Ensure registration form always appears on top ===== */
#modal {
  z-index: 1200;
}

#regModal {
  z-index: 1300 !important;
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.85);
  justify-content: center;
  align-items: center;
}

#regModal .modal-content {
  z-index: 1400 !important;
  position: relative;
}

</style>
</head>
<body>
<button class="menu-toggle d-lg-none" onclick="toggleSidebar()" aria-label="Toggle sidebar menu">☰</button>

<div class="sidebar" id="sidebar" role="navigation" aria-label="Sidebar Navigation">
  <div class="menu">
    <h3 id="siteTitle">CampusBuzz</h3>
    <a href="userdashboard.php">🏠 Home</a>
    <a href="event.php" class="active" aria-current="page">🎫 Event Registration</a>
    <a href="attendance.php">📋 Attendance</a>
    <a href="certificate.php">📜 Certificates</a>
    <a href="feedback.php">⭐ Feedback</a>
  </div>
  <a href="signin.html" class="logout-link" aria-label="Logout from CampusBuzz">🚪 Logout</a>
</div>

<div class="main" role="main" aria-labelledby="siteTitle">
  <h1>Upcoming Events</h1>
  <div class="grid" id="eventGrid" role="list">
    <?php if(!empty($events)): ?>
      <?php foreach($events as $event): ?>
        <div class="card" role="button" tabindex="0" aria-label="View details of <?= htmlspecialchars($event['event_name']) ?>" 
          onclick='openModal({
            event_id: <?= $event["id"] ?>,
            event_name: "<?= addslashes($event["event_name"]) ?>",
            event_date: "<?= $event["event_date"] ?>",
            event_location: "<?= addslashes($event["event_location"]) ?>",
            event_speakers: "<?= addslashes($event["event_speakers"]) ?>",
            event_contact: "<?= addslashes($event["event_contact"]) ?>",
            event_payment: "<?= addslashes($event["event_payment"]) ?>",
            event_image: "<?= addslashes($event["event_image"]) ?>",
            event_description: "<?= addslashes($event["event_description"]) ?>"
          })'>
          <img src="uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
               alt="Image of <?php echo htmlspecialchars($event['event_name']); ?>">
          <div class="info">
            <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
            <p><?php echo htmlspecialchars($event['event_date']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color:#aaa;text-align:center;">No upcoming events.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Event Modal -->
<div id="modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
  <div class="modal-content">
    <button class="close" onclick="closeModal()" aria-label="Close event details">&times;</button>
    <img id="modalImg" src="" alt="">
    <h2 id="modalTitle"></h2>
    <p id="modalDesc"></p>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>
    <p><strong>Location:</strong> <span id="modalLocation"></span></p>
    <p><strong>Speakers:</strong> <span id="modalSpeakers"></span></p>
    <p><strong>Contact:</strong> <span id="modalContact"></span></p>
    <p><strong>Payment:</strong> ₹<span id="modalPay"></span></p>
    <button id="registerBtn" class="register-btn" aria-label="Register for this event">Register</button>
  </div>
</div>

<!-- Registration Modal -->
<div id="regModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="regTitle">
  <div class="modal-content">
    <button class="close" onclick="closeRegModal()" aria-label="Close registration form">&times;</button>
    <h2 id="regTitle">Register for Event</h2>
    <form id="regForm">
      <label for="regEvent">Event:</label>
      <input type="text" id="regEvent" name="regEvent" readonly aria-readonly="true">

      <label for="regDate">Date:</label>
      <input type="text" id="regDate" name="regDate" readonly aria-readonly="true">

      <label for="regLocation">Location:</label>
      <input type="text" id="regLocation" name="regLocation" readonly aria-readonly="true">

      <label for="name">Your Name:</label>
      <input type="text" id="name" name="name" required aria-required="true">

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required aria-required="true" value="<?php echo $user_email; ?>">

      <label for="branch">Branch:</label>
      <input type="text" id="branch" name="branch" required aria-required="true">

      <label for="phone">Phone:</label>
      <input type="tel" id="phone" name="phone"pattern="[0-9]{10}" required aria-required="true">

      <label for="regPay">Payment:</label>
      <input type="text" id="regPay" name="regPay" readonly aria-readonly="true">

      <label for="payment">Payment Method:</label>
      <select id="payment" name="payment" required aria-required="true">
        <option value="">Select Payment</option>
        <option value="Credit Card">Credit Card</option>
        <option value="PayPal">PayPal</option>
        <option value="UPI">UPI</option>
      </select>

      <button type="submit" class="register-btn" aria-label="Proceed and pay for event registration">Proceed & Pay</button>
    </form>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('show');
}

// Accessibility: allow ESC to close modals
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (document.getElementById("modal").style.display === "flex") closeModal();
    if (document.getElementById("regModal").style.display === "flex") closeRegModal();
  }
});

// Allow Enter key to open event card
document.querySelectorAll('.card').forEach(card => {
  card.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') card.click();
  });
});

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('show');
}
const modal = document.getElementById("modal");
const regModal = document.getElementById("regModal");
const modalImg = document.getElementById("modalImg");
const modalTitle = document.getElementById("modalTitle");
const modalDesc = document.getElementById("modalDesc");
const modalDate = document.getElementById("modalDate");
const modalLocation = document.getElementById("modalLocation");
const modalSpeakers = document.getElementById("modalSpeakers");
const modalContact = document.getElementById("modalContact");
const modalPay = document.getElementById("modalPay");
const regEvent = document.getElementById("regEvent");
const regDate = document.getElementById("regDate");
const regLocation = document.getElementById("regLocation");
const regPay = document.getElementById("regPay");

let currentEvent = null;

// ======================
// Open event modal
// ======================
function openModal(eventData) {
    currentEvent = eventData; // ensure it's the actual event object
    modal.style.display = "flex";
    modalImg.src = "uploads/" + eventData.event_image;
    modalTitle.textContent = eventData.event_name;
    modalDesc.textContent = eventData.event_description;
    modalDate.textContent = eventData.event_date;
    modalLocation.textContent = eventData.event_location;
    modalSpeakers.textContent = eventData.event_speakers;
    modalContact.textContent = eventData.event_contact;
    modalPay.textContent = eventData.event_payment;
}

function closeModal() { modal.style.display = "none"; }
function closeRegModal() { regModal.style.display = "none"; }

// ======================
// Open registration modal
// ======================
document.getElementById("registerBtn").onclick = function () {
    if (currentEvent) {
        regEvent.value = currentEvent.event_name;
        regDate.value = currentEvent.event_date;
        regLocation.value = currentEvent.event_location;
        regPay.value = currentEvent.event_payment;
        document.querySelector("input[name='email']").value = "<?php echo $user_email; ?>";
        regModal.style.display = "flex";
    }
};

// ======================
// QR Code Generator
// ======================
async function generateQRCode(data) {
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data)}`;
    const response = await fetch(qrUrl);
    const blob = await response.blob();
    return await new Promise(resolve => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
}

// ======================
// Payment modal setup
// ======================
const paymentModal = document.createElement("div");
paymentModal.style = `display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.8);justify-content:center;align-items:center;z-index:2000;`;
paymentModal.innerHTML = `
<div style="background:#1e1e1e;padding:20px;border-radius:10px;text-align:center;max-width:350px;width:90%;">
  <h2>Scan to Pay</h2>
  <img id="qrCodeImg" src="" style="width:150px;height:150px;margin:10px auto;display:block;border-radius:8px;">
  <p style="margin:10px 0;">UPI: campusbuzz@upi</p>
  <p style="color:#aaa;">Waiting for payment confirmation...</p>
</div>`;
document.body.appendChild(paymentModal);

// ======================
// Registration submit
// ======================
document.getElementById("regForm").onsubmit = async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const name = formData.get("name");
    const email = formData.get("email");
    const phone = formData.get("phone");
    const paymentMethod = formData.get("payment");
    const eventTitle = currentEvent?.event_name || "";
    const date = currentEvent?.event_date || "";
    const location = currentEvent?.event_location || "";

    if (email !== "<?php echo $user_email; ?>") {
        alert("❌ You must use your logged-in email to register.");
        return;
    }

    // Already registered check
    const checkRes = await fetch("check_registration.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `email=${encodeURIComponent(email)}&event_id=${encodeURIComponent(currentEvent.event_id)}`

    });
    const isRegistered = await checkRes.text();
    if (isRegistered.trim() === "yes") {
        alert("⚠️ You have already registered for this event.");
        return;
    }

    const ticketID = "EVT" + Math.floor(100000 + Math.random() * 900000);

    // Payment QR
    const paymentData = `Pay for ${eventTitle} - ${ticketID}`;
    const qrImage = await generateQRCode(paymentData);
    document.getElementById("qrCodeImg").src = qrImage;

    regModal.style.display = "none";
    paymentModal.style.display = "flex";

    // Simulate payment success
    setTimeout(async () => {
        paymentModal.style.display = "none";
        alert("✅ Payment Successful! Saving registration...");

 console.log({
  event_id: currentEvent.event_id,
  event_name: currentEvent.event_name,
  event_date: currentEvent.event_date,
  name: name,
  email: email,
  phone: phone,
  payment_method: paymentMethod
});

        // Send data to PHP
        const resp = await fetch("register_event.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
    event_id: currentEvent.event_id,
    event_name: currentEvent.event_name,
    event_date: currentEvent.event_date,
    event_location: currentEvent.event_location,
    name: name,
    email: email,
    phone: phone,
    payment_method: paymentMethod
})

});
        const dbResult = await resp.json();

        if (dbResult.status === "success") {
            alert("✅ Registration stored successfully! Sending confirmation email...");

            await fetch("send_mail.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    email: email,
                    name: name,
                    event: eventTitle,
                    ticket_id: ticketID,
                    date: date,
                    location: location
                })
            });

            // Generate ticket PDF
            const ticketQRData = `Ticket ID: ${ticketID}\nEvent: ${eventTitle}\nName: ${name}\nDate: ${date}\nEmail: ${email}`;
            const ticketQR = await generateQRCode(ticketQRData);
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(18);
            doc.text("Event Ticket", 20, 20);
            doc.setFontSize(12);
            doc.addImage(ticketQR, "PNG", 10, 40, 50, 50);
            doc.text("Scan to verify", 15, 100);
            doc.save(`${eventTitle}_Ticket.pdf`);
        } else {
            alert(`❌ Database registration failed: ${dbResult.message || "Unknown error"}`);
        }

        this.reset();
    }, 3000);
};

// ======================
// Modal close handling
// ======================
window.onclick = function (e) {
    if (e.target === modal) closeModal();
    if (e.target === regModal) closeRegModal();
    if (e.target === paymentModal) paymentModal.style.display = "none";
};

</script>
</body>
</html>
<?php $conn->close(); ?>
