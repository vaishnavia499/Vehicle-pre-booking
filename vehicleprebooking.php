<?php
// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vehicle_booking');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = '';
$success = '';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($current_page === 'login') {
        // Login processing
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // In real app, use password_verify() with hashed passwords
            if ($password === 'password123') { // Demo only - replace with proper verification
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: ?page=dashboard");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    } 
    elseif ($current_page === 'register') {
        // Registration processing
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password']; // In real app, hash this
        $phone = $conn->real_escape_string($_POST['phone']);
        
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // In real app, use password_hash()
            $hashed_password = $password; // Replace with password_hash($password, PASSWORD_DEFAULT)
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
                $current_page = 'login';
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
    elseif ($current_page === 'profile') {
        // Profile update processing
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $gender = $conn->real_escape_string($_POST['gender']);
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $phone, $address, $gender, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Profile update failed: " . $conn->error;
        }
    }
    elseif ($current_page === 'book') {
        // Booking processing
        $vehicle_type = $conn->real_escape_string($_POST['vehicle_type']);
        $pickup_location = $conn->real_escape_string($_POST['pickup_location']);
        $destination = $conn->real_escape_string($_POST['destination']);
        $booking_time = $conn->real_escape_string($_POST['booking_time']);
        $booking_date = $conn->real_escape_string($_POST['booking_date']);
        $is_peak = isset($_POST['is_peak']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_type, pickup_location, destination, booking_time, booking_date, is_peak, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssssi", $_SESSION['user_id'], $vehicle_type, $pickup_location, $destination, $booking_time, $booking_date, $is_peak);
        
        if ($stmt->execute()) {
            $success = "Booking successful! Your booking ID is " . $conn->insert_id;
        } else {
            $error = "Booking failed: " . $conn->error;
        }
    }
}

// Get user data for profile page
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    $user = $user_result->fetch_assoc();
    
    // Get bookings for history
    $bookings = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id ORDER BY booking_date DESC, booking_time DESC");
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ?page=login");
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Booking System</title>
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav-links a:hover {
            background-color: #34495e;
        }
        
        /* Main Content Styles */
        main {
            padding: 2rem 0;
            min-height: calc(100vh - 120px);
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        h1, h2, h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button, .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Footer Styles */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
        
        /* Home Page Specific Styles */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://via.placeholder.com/1200x400');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 5rem 2rem;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .hero h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 2rem;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .feature-card i {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        
        /* Contact Page Specific Styles */
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .contact-info {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .info-item i {
            font-size: 1.5rem;
            color: #3498db;
            margin-right: 1rem;
        }
        .map-container {
            margin-top: 2rem;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-links {
                flex-direction: column;
                width: 100%;
            }
            .nav-links a {
                text-align: center;
            }
            .contact-container {
                grid-template-columns: 1fr;
            }
            .hero {
                padding: 3rem 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">Varshkalam</div>
            <nav class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?page=dashboard">Dashboard</a>
                    <a href="?page=profile">Profile</a>
                    <a href="?page=book">New Booking</a>
                    <a href="?action=logout">Logout</a>
                <?php else: ?>
                    <a href="?page=home">Home</a>
                    <a href="?page=about">About</a>
                    <a href="?page=contact">Contact</a>
                    <a href="?page=login">Login</a>
                    <a href="?page=register">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php
        // Page routing
        if (!isset($_SESSION['user_id']) && !in_array($current_page, ['login', 'register', 'home', 'about', 'contact'])) {
            $current_page = 'login';
        }
        
        switch ($current_page) {
            case 'home':
                includeHomePage();
                break;
            case 'about':
                includeAboutPage();
                break;
            case 'contact':
                includeContactPage();
                break;
            case 'login':
                includeLoginPage();
                break;
            case 'register':
                includeRegisterPage();
                break;
            case 'dashboard':
                includeDashboardPage();
                break;
            case 'profile':
                includeProfilePage();
                break;
            case 'book':
                includeBookingPage();
                break;
            default:
                includeHomePage();
        }
        
        function includeHomePage() {
            ?>
            <div class="hero">
                <h1>Welcome to Varshkalam Vehicle Booking</h1>
                <p>Book your perfect ride with just a few clicks. Experience seamless transportation services tailored to your needs.</p>
                <div>
                    <a href="?page=register" class="btn btn-success">Get Started</a>
                    <a href="?page=login" class="btn" style="margin-left: 1rem;">Login</a>
                </div>
            </div>
            
            <div class="features">
                <div class="feature-card">
                    <i class="fas fa-car"></i>
                    <h3>Wide Vehicle Selection</h3>
                    <p>Choose from sedans, SUVs, vans, and luxury vehicles for every occasion.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>24/7 Availability</h3>
                    <p>Book your ride anytime, anywhere with our round-the-clock service.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safe & Secure</h3>
                    <p>Verified drivers and secure payment options for your peace of mind.</p>
                </div>
            </div>
            
            <div class="card">
                <h2>How It Works</h2>
                <ol style="padding-left: 1.5rem; line-height: 2;">
                    <li><strong>Create an account</strong> or login to your existing account</li>
                    <li><strong>Select your vehicle</strong> type and preferred booking time</li>
                    <li><strong>Confirm your booking</strong> with secure payment options</li>
                    <li><strong>Enjoy your ride</strong> with our professional drivers</li>
                </ol>
            </div>
            <?php
        }
        
        function includeAboutPage() {
            ?>
            <div class="card">
                <h1>About Varshkalam</h1>
                <p>Varshkalam is a premier vehicle booking platform dedicated to providing reliable and comfortable transportation services. Founded in 2023, we have grown to become a trusted name in the industry, serving thousands of satisfied customers.</p>
                
                <h2 style="margin-top: 2rem;">Our Mission</h2>
                <p>To revolutionize the transportation industry by providing affordable, reliable, and convenient booking solutions that cater to all your travel needs.</p>
                
                <h2 style="margin-top: 2rem;">Our Team</h2>
                <p>We are a team of passionate professionals committed to delivering exceptional service. Our drivers are carefully vetted and trained to ensure your safety and comfort.</p>
            </div>
            <?php
        }
        
        function includeContactPage() {
            ?>
            <div class="card">
                <h1>Contact Us</h1>
                <div class="contact-container">
                    <div class="contact-info">
                        <h2>Get in Touch</h2>
                        
                        <div class="info-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <h3>Phone</h3>
                                <p>+91 9876543210<br>+91 1234567890</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>Email</h3>
                                <p>info@varshkalam.com<br>support@varshkalam.com</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h3>Address</h3>
                                <p>123 Business Street<br>Bangalore, Karnataka 560001</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form">
                        <h2>Send Us a Message</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Your Message</label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn">Send Message</button>
                        </form>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.003073604668!2d77.59451431482194!3d12.9719639908566!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1670c9b44e6d%3A0xf5df5a3c9c6d4b1e!2sBangalore%2C%20Karnataka!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                    </iframe>
                </div>
            </div>
            <?php
        }
        
        function includeLoginPage() {
            ?>
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <h1>Login</h1>
                <form method="POST" action="?page=login">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                    
                    <p style="margin-top: 1.5rem; text-align: center;">
                        Don't have an account? <a href="?page=register">Register here</a>
                    </p>
                </form>
            </div>
            <?php
        }
        
        function includeRegisterPage() {
            ?>
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <h1>Create Account</h1>
                <form method="POST" action="?page=register">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Register</button>
                    
                    <p style="margin-top: 1.5rem; text-align: center;">
                        Already have an account? <a href="?page=login">Login here</a>
                    </p>
                </form>
            </div>
            <?php
        }
        
        function includeDashboardPage() {
            global $user, $bookings;
            ?>
            <div class="card">
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
                <p>Manage your vehicle bookings and profile information.</p>
                
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <a href="?page=book" class="btn btn-success">New Booking</a>
                    <a href="?page=profile" class="btn">View Profile</a>
                </div>
            </div>
            
            <div class="card">
                <h2>Your Recent Bookings</h2>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Vehicle Type</th>
                                <th>Pickup Location</th>
                                <th>Destination</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['vehicle_type']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['booking_date'])) . ' at ' . date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                    <td><?php echo ucfirst($booking['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You don't have any bookings yet. <a href="?page=book">Book a vehicle now</a>.</p>
                <?php endif; ?>
            </div>
            <?php
        }
        
        function includeProfilePage() {
            global $user;
            ?>
            <div class="card">
                <h1>Your Profile</h1>
                
                <form method="POST" action="?page=profile">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo (isset($user['gender']) && $user['gender'] === 'male' ? 'selected' : ''); ?>>Male</option>
                            <option value="female" <?php echo (isset($user['gender']) && $user['gender'] === 'female' ? 'selected' : ''); ?>>Female</option>
                            <option value="other" <?php echo (isset($user['gender']) && $user['gender'] === 'other' ? 'selected' : ''); ?>>Other</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </div>
            <?php
        }
        
        function includeBookingPage() {
            ?>
            <div class="card">
                <h1>New Vehicle Booking</h1>
                
                <form method="POST" action="?page=book">
                    <div class="form-group">
                        <label for="vehicle_type">Vehicle Type</label>
                        <select id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Hatchback">Hatchback</option>
                            <option value="Luxury">Luxury</option>
                            <option value="Van">Van</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pickup_location">Pickup Location</label>
                        <input type="text" id="pickup_location" name="pickup_location" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_date">Booking Date</label>
                        <input type="date" id="booking_date" name="booking_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_time">Booking Time</label>
                        <input type="time" id="booking_time" name="booking_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_peak"> Peak Time Booking (7-10 AM or 6-10 PM)
                        </label>
                        <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                            Note: Peak time bookings require 50% advance payment and have limited availability.
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                    <a href="?page=dashboard" class="btn" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>
            <?php
        }
        ?>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Varshkalam Vehicle Booking System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>