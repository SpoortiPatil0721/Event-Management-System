<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$event_id = $_POST['event_id'] ?? 0;
$event_name = $_POST['event_name'] ?? '';
$event_date = $_POST['event_date'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (!$user_id || !$event_id || !$event_name || !$event_date || !$name || !$email || !$phone) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO event_registrations 
(user_id, event_id, event_name, event_date, name, email, phone, payment_method)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => $conn->error]);
    exit;
}

$stmt->bind_param("iissssss", 
    $user_id, 
    $event_id, 
    $event_name, 
    $event_date, 
    $name, 
    $email, 
    $phone, 
    $payment_method
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
