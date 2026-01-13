<?php
include('db_connect.php'); // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 1️⃣ Validate empty fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill all fields'); window.history.back();</script>";
        exit();
    }

    // 2️⃣ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit();
    }

    // 3️⃣ Validate password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }

    // 4️⃣ Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Email already registered. Please sign in.'); window.location.href='signin.html';</script>";
        exit();
    }

    // 5️⃣ Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 6️⃣ Insert new user
    $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Registration successful! Please sign in.'); window.location.href='signin.html';</script>";
    } else {
        echo "<script>alert('Error occurred while creating account.'); window.history.back();</script>";
    }

    // 7️⃣ Close statements and connection
    $insert_stmt->close();
    $conn->close();
}
?>
