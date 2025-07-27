<?php
// Database connection settings
$servername = "localhost";
$username = "root";         // Change if needed
$password = "";             // Change if needed
$dbname = "your_database";  // Replace with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data
$sql = "SELECT * FROM VehicleInfo";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicle Pre-Booking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            color: #333;
        }
        header {
            background: #0d6efd;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .auth-buttons a {
            background: white;
            color: #0d6efd;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: bold;
        }
        .welcome-message {
            text-align: center;
            padding: 15px;
            background: white;
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin: 10px;
            border-radius: 5px;
        }
        section {
            margin: 20px;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Vehicle Booking</div>
        <nav>
            <ul>
                <li><a href="#about">About</a></li>
                <li><a href="#offers">Offers</a></li>
                <li><a href="#availability">Availability</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <a href="login.html">Login</a>
            <a href="signup.html">Signup</a>
        </div>
    </header>

    <div class="welcome-message">Welcome to the Vehicle Pre-Booking System! Book your ride with ease.</div>

    <section id="about">
        <h2>About the Application</h2>
        <p>This application allows users to pre-book vehicles, track their rides, and manage bookings efficiently.</p>
    </section>

    <section id="offers">
        <h2>Special Offers</h2>
        <p>Get exclusive discounts on your first ride! Limited-time offers available.</p>
    </section>

    <section id="availability">
        <h2>Vehicle Availability</h2>
        <p>Check real-time availability of vehicles in your area.</p>
        <?php
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>S.No</th>
                        <th>Vehicle Type</th>
                        <th>Total Vehicles</th>
                        <th>Amount/km</th>
                        <th>Peak Time Available</th>
                        <th>Non-Peak Time Available</th>
                        <th>Remaining Vehicles</th>
                    </tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['s_no']}</td>
                        <td>{$row['vehicle_type']}</td>
                        <td>{$row['total_vehicles']}</td>
                        <td>{$row['amount_per_km']}</td>
                        <td>{$row['available_peak']}</td>
                        <td>{$row['available_non_peak']}</td>
                        <td>{$row['remaining_vehicles']}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data available.</p>";
        }
        $conn->close();
        ?>
    </section>
</body>
</html>
