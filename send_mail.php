<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event = $_POST['event'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $ticketID = $_POST['ticket_id'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'campussbuzzz0@gmail.com'; 
        $mail->Password = 'owkk cdmi ftfe qjni';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;


        $mail->setFrom('campussbuzzz0@gmail.com', 'Event Management Team');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "Registration Successful for $event";
        $mail->Body = "
        <h2>Registration Successful 🎟️</h2>
        <p>Hello <strong>$name</strong>,</p>
        <p>Thank you for registering for <strong>$event</strong>!</p>
        <p><b>Date:</b> $date<br>
        <b>Location:</b> $location<br>
        <b>Ticket ID:</b> $ticketID</p>
        <p>Your registration was successful. See you soon!</p>
        <br><p>Best Regards,<br><b>Event Management Team</b></p>
        ";

        $mail->send();
         echo "success";

    } catch (Exception $e) {
        echo "Email failed: {$mail->ErrorInfo}";
    }
}
?>
