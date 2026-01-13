<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch image to delete from server
    $res = $conn->query("SELECT event_image FROM events WHERE id = $id");
    if ($res->num_rows) {
        $img = $res->fetch_assoc()['event_image'];
        if ($img && file_exists("uploads/$img")) {
            unlink("uploads/$img"); // delete image file
        }
    }

    // Delete event
    $conn->query("DELETE FROM events WHERE id = $id");

    // Redirect back to admin dashboard
    header("Location: admindashboard.php?msg=deleted");
    exit;
} else {
    echo "Error: Event ID missing";
}

$conn->close();
?>
