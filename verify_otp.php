<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['email'])) {
    die("Session expired. Please request a new OTP.");
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $entered_otp == $result['otp'] && strtotime($result['otp_expiry']) > time()) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Verify OTP | CampusBuzz</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-form">
    <h1 class="headline">Verify OTP</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
      <div class="field">
        <input type="text" name="otp" id="otp" required>
        <label for="otp">Enter OTP</label>
      </div>
      <button type="submit" class="btn neon">Verify OTP</button>
    </form>
  </div>
</body>
</html>
