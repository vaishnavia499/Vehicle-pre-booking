<?php
$conn = new mysqli("localhost", "root", "Mvais@2005", "vehiclebooking");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine action
$action = $_POST['action'];

if ($action === 'signup') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $aadhaar = $_POST['aadhaar'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, aadhaar, phone, gender, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $fullname, $email, $aadhaar, $phone, $gender, $password);

    if ($stmt->execute()) {
        echo "Signup successful!";
    } else {
        echo "Signup error: " . $conn->error;
    }

    $stmt->close();
}

if ($action === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();
        if (password_verify($password, $hashedPassword)) {
            header("Location: dashboard.html");
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Email not found!";
    }

    $stmt->close();
}

$conn->close();
?>
