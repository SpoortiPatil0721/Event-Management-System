<?php
include 'db_connect.php';
$id = intval($_GET['id']);
$sql = "SELECT * FROM events WHERE id=$id";
$res = $conn->query($sql);
echo json_encode($res->fetch_assoc());
?>
