<?php
$host = "localhost";
$user = "root";
$pass = ""; // default password for localhost (XAMPP/WAMP)
$db = "user_data";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$aadhaar = $_POST['aadhaar'];
$phone = $_POST['phone'];
$gender = $_POST['gender'];
$password = $_POST['password'];

// Use prepared statement to avoid SQL injection
$stmt = $conn->prepare("INSERT INTO users (email, fullname, aadhaar, phone, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $email, $fullname, $aadhaar, $phone, $gender, $password);

if ($stmt->execute()) {
    echo "Sign-up successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
