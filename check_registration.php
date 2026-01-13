<?php
// check_registration.php - returns "yes" or "no" (simple for front-end)
include 'db_connect.php';
session_start();

$email = $_POST['email'] ?? '';
$event_id = intval($_POST['event_id'] ?? 0);

if (!$email || !$event_id) {
    echo 'no';
    exit;
}

$stmt = $conn->prepare("SELECT id FROM event_registrations WHERE email = ? AND event_id = ?");
$stmt->bind_param("si", $email, $event_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) echo 'yes';
else echo 'no';

$stmt->close();
$conn->close();
?>
