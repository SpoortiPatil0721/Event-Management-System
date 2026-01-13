<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['otp_verified'])) {
    die("Unauthorized access.");
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=?, otp=NULL, otp_expiry=NULL WHERE email=?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();

        unset($_SESSION['otp_verified']);
        echo "<script>alert('Password reset successful!');window.location='signin.html';</script>";
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password | CampusBuzz</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-form">
    <h1 class="headline">Reset Password</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
      <div class="field">
        <input type="password" name="password" id="password" required>
        <label for="password">New Password</label>
      </div>
      <div class="field">
        <input type="password" name="confirm_password" id="confirm_password" required>
        <label for="confirm_password">Confirm Password</label>
      </div>
      <button type="submit" class="btn neon">Reset Password</button>
    </form>
  </div>
</body>
</html>
