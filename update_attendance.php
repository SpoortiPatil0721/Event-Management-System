<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $event_id = intval($_POST['event_id']);
    $status = $_POST['status'];

    // Check if record exists
    $check = $conn->prepare("SELECT id FROM attendance WHERE user_id=? AND event_id=?");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Update attendance
        $stmt = $conn->prepare("UPDATE attendance SET status=?, marked_at=NOW() WHERE user_id=? AND event_id=?");
        $stmt->bind_param("sii", $status, $user_id, $event_id);
        $stmt->execute();
        $msg = "✅ Attendance updated successfully!";
    } else {
        // Insert new attendance
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, event_id, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $event_id, $status);
        $stmt->execute();
        $msg = "✅ Attendance marked successfully!";
    }

    // --- Add notification for the user ---
    $notification = "Your attendance for event ID $event_id has been marked as $status.";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $notif_stmt->bind_param("is", $user_id, $notification);
    $notif_stmt->execute();

    echo $msg;
}
?>
