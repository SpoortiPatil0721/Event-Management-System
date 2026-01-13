<?php
session_start();
require 'db_connect.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate OTP & expiry (10 mins)
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $update = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
        $update->bind_param("sss", $otp, $expiry, $email);
        $update->execute();

        $_SESSION['email'] = $email;

        // Send OTP Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'campussbuzzz0@gmail.com';       // 🔹 Replace
            $mail->Password = 'owkk cdmi ftfe qjni';    // 🔹 Replace
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('campussbuzzz0@gmail.com', 'CampusBuzz');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'CampusBuzz Password Reset OTP';
            $mail->Body = "
                <div style='font-family:sans-serif;padding:20px'>
                    <h2>CampusBuzz</h2>
                    <p>Hello,</p>
                    <p>Your OTP for password reset is:</p>
                    <h1 style='color:#007BFF;'>$otp</h1>
                    <p>This OTP will expire in 10 minutes.</p>
                </div>
            ";
            $mail->send();

            header("Location: verify_otp.php");
            exit;
        } catch (Exception $e) {
            $error = "Mail error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found in database.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password | CampusBuzz</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-form">
    <h1 class="headline">Forgot Password</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
      <div class="field">
        <input type="email" name="email" id="email" required>
        <label for="email">Enter your email</label>
      </div>
      <button type="submit" class="btn neon">Send OTP</button>
    </form>
  </div>
</body>
</html>
