<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home - Vehicle Booking</title>
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
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        .header-buttons a {
            background: white;
            color: #0d6efd;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: inline-block;
        }
        .header-buttons a:hover {
            background: #f8f9fa;
        }
        section {
            margin: 20px;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .booking-buttons a {
            background: #28a745;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin: 5px;
            display: inline-block;
        }
        .booking-buttons a:hover {
            background: #218838;
        }
        .slot-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: justify;
        }
        .slot-info h3 {
            margin-top: 0;
            color: #0d6efd;
        }
        .history-content {
            text-align: justify;
            line-height: 1.6;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        input[type="text"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">User Dashboard</div>
        <div class="header-buttons">
            <a href="profile.php">Profile</a>
            <a href="submit_booking.php">History</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <section>
        <h2>Vehicle Pre-Booking Slots</h2>

        <div class="slot-info">
            <h3>Peak Time Booking (7:00 AM – 10:00 AM & 6:00 PM – 10:00 PM)</h3>
            <p>
                During peak hours, only <strong>50% of vehicles</strong> are available for booking to ensure availability.
                To confirm your booking during these hours, you are required to <strong>pay 50% of the total amount in advance</strong>.
                The remaining amount can be <strong>paid after the ride</strong>. Booking early is recommended due to limited availability.
            </p>
            <div class="booking-buttons">
                <a href="peakcheckbox.html">Book Peak Time Slot</a>
            </div>
        </div>

        <div class="slot-info">
            <h3>Non-Peak Time Booking</h3>
            <p>
                During non-peak hours, all vehicle types are available without any advance payment.
                You can freely select the <strong>vehicle type, pickup location, and destination</strong>, and book your ride conveniently.
                Payment can be completed at the end of the ride or as per your chosen method.
            </p>
            <div class="booking-buttons">
                <a href="nonpeak.html">Book Non-Peak Time Slot</a>
            </div>
        </div>
    </section>

    <section>
        <h2>Booking History</h2>
        <div class="history-content">
            <p>Your booking history provides a comprehensive record of all your past vehicle reservations. Here you can view details such as booking dates, times, vehicle types, payment status, and trip completion status. Each entry includes the booking reference number which you can use for tracking or future reference. You can also download receipts and invoices for completed bookings from this section.</p>
            <p>The system maintains your booking history for 12 months from the date of each reservation. For any disputes or clarifications regarding past bookings, please contact our customer support team with your booking reference number ready.</p>
        </div>
    </section>

    <section>
        <h2>Track Your Vehicle</h2>
        <input type="text" placeholder="Enter Booking ID">
        <button>Track</button>
    </section>
</body>
</html>