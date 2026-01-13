<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $event_id = intval($_POST['event_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Fetch event name from event_registrations
    $stmt = $conn->prepare("SELECT event_name FROM event_registrations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "Invalid event selected.";
        exit();
    }
    $event_name = $result->fetch_assoc()['event_name'];
    $stmt->close();

    // Insert into feedbacks table
    $stmt = $conn->prepare("INSERT INTO feedbacks (user_id, event_id, event_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisds", $user_id, $event_id, $event_name, $rating, $comment);

    if ($stmt->execute()) {
        header("Location: feedback.php?success=1");
        exit();
    } else {
        echo "Database Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
