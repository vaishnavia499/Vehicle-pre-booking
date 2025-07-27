<?php
session_start();

// Database configuration
$host = "localhost:3307";
$user = "root";
$password = ""; // Default XAMPP has no password
$dbname = "user_data";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create connection
        $conn = new mysqli($host, $user, $password);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if not exists
        $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        $conn->select_db($dbname);

        // Create table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            fullname VARCHAR(255) NOT NULL,
            aadhaar VARCHAR(20),
            phone VARCHAR(20),
            gender VARCHAR(10),
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Get and sanitize input data
        $email = $conn->real_escape_string($_POST['email']);
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $aadhaar = $conn->real_escape_string($_POST['aadhaar'] ?? '');
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $gender = $conn->real_escape_string($_POST['gender'] ?? '');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($email) || empty($fullname) || empty($password)) {
            throw new Exception("Email, full name and password are required!");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match!");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format!");
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            throw new Exception("Email already registered!");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $insert = $conn->prepare("INSERT INTO users (email, fullname, aadhaar, phone, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss", $email, $fullname, $aadhaar, $phone, $gender, $hashed_password);
        
        if ($insert->execute()) {
            $_SESSION['signup_success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            throw new Exception("Registration failed: " . $conn->error);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 350px;
        }
        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #218838;
        }
        .error {
            color: #dc3545;
            margin: 10px 0;
            font-size: 14px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #0d6efd;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="aadhaar" placeholder="Aadhaar Number">
            <input type="tel" name="phone" placeholder="Phone Number">
            <select name="gender">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>