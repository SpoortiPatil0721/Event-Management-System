<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['event_name'];
    $desc = $_POST['event_description'] ?? '';
    $date = $_POST['event_date'];
    $location = $_POST['event_location'] ?? '';
    $speakers = $_POST['event_speakers'] ?? '';
    $contact = $_POST['event_contact'] ?? '';
    $payment = $_POST['event_payment'] ?? 0.00;

    // Handle image upload
    $imageName = 'default_event.jpeg';
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
        $ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('event_') . '.' . $ext;
        move_uploaded_file($_FILES['event_image']['tmp_name'], 'uploads/' . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO events (event_name, event_description, event_date, event_location, event_speakers, event_contact, event_payment, event_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssds", $name, $desc, $date, $location, $speakers, $contact, $payment, $imageName);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
