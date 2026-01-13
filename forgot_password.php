<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | CampusBuzz</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-form">
    <h1>Forgot Password</h1>
    <form action="send_otp.php" method="POST">
      <div class="field">
        <input type="email" name="email" id="email" required>
        <label for="email">Enter your registered Email</label>
      </div>
      <button type="submit" class="btn neon">Send OTP</button>
    </form>
  </div>
</body>
</html>
