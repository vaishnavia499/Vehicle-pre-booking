<?php
// Database connection
$servername = "localhost:3307";
$username = "your_username";
$password = "";
$dbname = "vehicle_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_type = $_POST['vehicleType'];
    $pickup_time = $_POST['pickupTime'];
    $drop_time = $_POST['dropTime'];
    $pickup_location = $_POST['location'];
    $destination = $_POST['destination'];
    $payment_type = $_POST['paymentType'];
    $is_peak = 1; // Since this is the peak time booking page
    $prepayment_accepted = isset($_POST['prepay']) ? 1 : 0;
    $user_id = 1; // You should get this from session or authentication

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_type, pickup_time, drop_time, pickup_location, destination, payment_type, is_peak, prepayment_accepted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssii", $user_id, $vehicle_type, $pickup_time, $drop_time, $pickup_location, $destination, $payment_type, $is_peak, $prepayment_accepted);

    if ($stmt->execute()) {
        header("Location: bookingsucc.html");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Peak Time Booking</title>
  <style>
    /* ----------  Base layout & colours ---------- */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #3b82f6, #10b981);
      color: #333;
    }
    header {
      background: #0d6efd;
      color: #fff;
      padding: 15px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
    }
    .container {
      background: #fff;
      margin: 30px auto;
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .error {
      color: #dc3545;
      margin-bottom: 15px;
      text-align: center;
    }

    /* ----------  Form elements ---------- */
    label {
      display: block;
      margin: 15px 0 5px;
    }
    input[type="text"],
    input[type="datetime-local"],
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    /* ----------  Checkbox row ---------- */
    .checkbox-inline {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 15px;
      font-size: 16px;
    }
    .checkbox-inline input[type="checkbox"] {
      margin: 0;
    }

    /* ----------  Button styles ---------- */
    button[type="submit"] {
      display: block;
      width: 100%;
      text-align: center;
      background: #0d6efd;
      color: #fff;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 18px;
      margin-top: 20px;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    button[type="submit"]:hover {
      background: #0b5ed7;
    }
    button[type="submit"]:disabled {
      background: #cccccc;
      cursor: not-allowed;
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <header>Peak Time Vehicle Booking</header>

  <div class="container">
    <?php if (isset($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <label for="vehicleType">Vehicle Type</label>
      <select id="vehicleType" name="vehicleType" required>
        <option value="">Select Vehicle Type</option>
        <option value="car">Car</option>
        <option value="bike">Bike</option>
        <option value="van">Van</option>
        <option value="bus">Bus</option>
      </select>

      <label for="pickupTime">Pick Up Time</label>
      <input type="datetime-local" id="pickupTime" name="pickupTime" required>

      <label for="dropTime">Drop Time</label>
      <input type="datetime-local" id="dropTime" name="dropTime" required>

      <label for="location">Pick Up Location</label>
      <input type="text" id="location" name="location" required>

      <label for="destination">Destination</label>
      <input type="text" id="destination" name="destination" required>

      <label for="paymentType">Payment Type</label>
      <select id="paymentType" name="paymentType" required>
        <option value="">Select Payment Type</option>
        <option value="credit">Credit Card</option>
        <option value="debit">Debit Card</option>
        <option value="upi">UPI</option>
        <option value="cash">Cash</option>
      </select>

      <div class="checkbox-inline">
        <input type="checkbox" id="prepay" name="prepay" onchange="toggleButton()" />
        <label for="prepay">I accept 50% pre-payment for my booking</label>
      </div>

      <button type="submit" id="bookBtn" disabled>Book Now</button>
    </form>
  </div>

  <script>
    function toggleButton() {
      const checkbox = document.getElementById('prepay');
      const btn = document.getElementById('bookBtn');

      // Enable/disable button based on checkbox
      btn.disabled = !checkbox.checked;
    }
  </script>
</body>
</html>