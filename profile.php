<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost:3307";
$username = "root";
$password = "";
$database = "user_data";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['user_email'];

$stmt = $conn->prepare("SELECT fullname, email, aadhaar, phone, gender FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($fullname, $email, $aadhaar, $phone, $gender);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            color: #333;
            padding: 40px;
        }
        .profile-container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #0d6efd;
        }
        .profile-data {
            margin: 20px 0;
        }
        .profile-data label {
            font-weight: bold;
        }
        .profile-data div {
            margin-bottom: 10px;
        }
        .logout-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .logout-btn a {
            text-decoration: none;
            background: #dc3545;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .logout-btn a:hover {
            background: #c82333;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>Your Profile</h2>
    <div class="profile-data">
        <div><label>Full Name:</label> <?= htmlspecialchars($fullname) ?></div>
        <div><label>Email:</label> <?= htmlspecialchars($email) ?></div>
        <div><label>Aadhaar:</label> <?= htmlspecialchars($aadhaar) ?></div>
        <div><label>Phone:</label> <?= htmlspecialchars($phone) ?></div>
        <div><label>Gender:</label> <?= htmlspecialchars($gender) ?></div>
    </div>
    <div class="logout-btn">
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
