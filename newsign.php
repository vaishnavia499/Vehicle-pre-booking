<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database configuration
    $host = "localhost";
    $user = "root";
    $pass = ""; // Use your DB password if set
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

    // Prepared statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO users (email, fullname, aadhaar, phone, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $email, $fullname, $aadhaar, $phone, $gender, $password);

    if ($stmt->execute()) {
        $message = "Sign-up successful!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign-Up Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #3b82f6, #10b981, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        h2 {
            color: #0d6efd;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #10b981;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background: #0f9d58;
        }
        .toggle-link {
            color: #0d6efd;
            cursor: pointer;
            margin-top: 10px;
            display: block;
            font-size: 14px;
        }
        .message {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Sign Up</h2>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>
    <form method="POST" action="">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email ID" required>
        <input type="text" name="aadhaar" placeholder="Aadhaar Number" required>
        <input type="tel" name="phone" placeholder="Phone Number" required>
        <select name="gender" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
    <span class="toggle-link">Already have an account? Login</span>
</div>

</body>
</html>
