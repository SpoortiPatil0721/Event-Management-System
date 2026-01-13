<?php
session_start();
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $admin_key = trim($_POST['admin_key'] ?? ''); // optional for admin

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill all fields'); window.history.back();</script>";
        exit();
    }

    // Fetch user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {

            // Admin login
            if ($row['role'] === 'admin') {
                if ($admin_key === $row['admin_key']) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_name'] = $row['name'];
                    $_SESSION['role'] = 'admin';
                    header("Location: admindashboard.php");
                    exit();
                } else {
                    echo "<script>alert('Invalid Admin Key'); window.history.back();</script>";
                    exit();
                }
            } 
            // Normal user login
            else {
                // Assign unique application number if not set
                if (empty($row['application_no'])) {
                    $uniqueNo = 'CB' . date('Y') . strtoupper(substr(uniqid(), -5));
                    $update = $conn->prepare("UPDATE users SET application_no = ? WHERE id = ?");
                    $update->bind_param("si", $uniqueNo, $row['id']);
                    $update->execute();
                    $row['application_no'] = $uniqueNo;
                }

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
$_SESSION['user_email'] = $row['email'];
                $_SESSION['role'] = 'user';
                $_SESSION['application_no'] = $row['application_no'];

$_SESSION['user_email'] = $row['email'];  // <- this line is critical

                header("Location: userdashboard.php");
                exit();
            }

        } else {
            echo "<script>alert('Invalid password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email'); window.history.back();</script>";
    }
}
?>
