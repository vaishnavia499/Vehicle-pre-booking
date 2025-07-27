<?php
session_start();

// Connect to database
$conn = new mysqli("localhost:3307", "root", "", "user_data");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default page
$page = $_GET['page'] ?? 'login';
$error = "";

// Handle signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $aadhaar = $_POST['aadhaar'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("INSERT INTO users (email, fullname, aadhaar, phone, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $email, $fullname, $aadhaar, $phone, $gender, $password);

    if ($stmt->execute()) {
        header("Location: ?page=login");
        exit();
    } else {
        $error = "Signup failed. Email may already exist.";
        $page = 'signup';
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        if ($user['password'] === $password) {
            $_SESSION['user'] = $user;
            header("Location: ?page=profile");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
    $page = 'login';
}

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header("Location: ?page=login");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Portal</title>
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
        .container {
            background: white;
            padding: 20px;
            width: 320px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: #0d6efd;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
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
        .link {
            margin-top: 10px;
            display: block;
            color: #0d6efd;
            text-decoration: none;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
<?php if ($page === 'login'): ?>
    <h2>Login</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <a class="link" href="?page=signup">Don't have an account? Sign Up</a>

<?php elseif ($page === 'signup'): ?>
    <h2>Sign Up</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email ID" required>
        <input type="text" name="aadhaar" placeholder="Aadhaar Number" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="signup">Sign Up</button>
    </form>
    <a class="link" href="?page=login">Already have an account? Login</a>

<?php elseif ($page === 'profile' && isset($_SESSION['user'])): ?>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['fullname']) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?></p>
    <p><strong>Aadhaar:</strong> <?= htmlspecialchars($_SESSION['user']['aadhaar']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($_SESSION['user']['phone']) ?></p>
    <p><strong>Gender:</strong> <?= htmlspecialchars($_SESSION['user']['gender']) ?></p>
    <a class="link" href="?page=logout">Logout</a>

<?php else: ?>
    <p>Please <a class="link" href="?page=login">login</a>.</p>
<?php endif; ?>
</div>

</body>
</html>
