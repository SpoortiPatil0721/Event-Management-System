<?php
include 'db_connect.php';
$id = $_POST['event_id'] ?? '';
$name = $_POST['event_name'];
$date = $_POST['event_date'];
$loc = $_POST['location'];
$speakers = $_POST['speakers'];
$contact = $_POST['contact'];
$payment = $_POST['payment'];

if($id){ // edit
    $stmt = $conn->prepare("UPDATE events SET event_name=?, event_date=?, location=?, speakers=?, contact=?, payment=? WHERE id=?");
    $stmt->bind_param("ssssssi",$name,$date,$loc,$speakers,$contact,$payment,$id);
    echo $stmt->execute() ? "✅ Event updated!" : "❌ Update failed!";
}else{ // add new
    $stmt = $conn->prepare("INSERT INTO events (event_name,event_date,location,speakers,contact,payment) VALUES(?,?,?,?,?,?)");
    $stmt->bind_param("sssssi",$name,$date,$loc,$speakers,$contact,$payment);
    echo $stmt->execute() ? "✅ Event added!" : "❌ Add failed!";
}
?>
