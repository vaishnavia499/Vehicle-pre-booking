<?php
// Database configuration
$db_host = "localhost";
$db_user = "your_username";
$db_pass = "your_password";
$db_name = "vehicle_booking";

// Check if this is an API request
if (isset($_GET['api'])) {
    header("Content-Type: application/json");
    
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        if ($user_id <= 0) {
            throw new Exception("Invalid user ID");
        }

        $stmt = $conn->prepare("
            SELECT 
                id, user_id, vehicle_type, pickup_time, drop_time, 
                pickup_location, destination, payment_type, 
                is_peak, prepayment_accepted, created_at 
            FROM bookings 
            WHERE user_id = ? 
            ORDER BY pickup_time DESC
        ");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        
        $bookings = array();
        while ($row = $result->fetch_assoc()) {
            $row['is_peak'] = (bool)$row['is_peak'];
            $row['prepayment_accepted'] = (bool)$row['prepayment_accepted'];
            $bookings[] = $row;
        }
        
        echo json_encode($bookings);
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("error" => $e->getMessage()));
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #3b82f6, #10b981, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .history-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 1200px;
            text-align: center;
        }
        h2 {
            color: #0d6efd;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #10b981;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .peak-time {
            background-color: #ffebee;
        }
        .filter-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        select, button {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #10b981;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0f9d58;
        }
        .back-link {
            color: #0d6efd;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .loading {
            color: #666;
            font-style: italic;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="history-container">
    <h2>Booking History</h2>
    
    <div class="filter-container">
        <select id="timeFilter">
            <option value="all">All Bookings</option>
            <option value="peak">Peak Time Only</option>
            <option value="non-peak">Non-Peak Time Only</option>
        </select>
        <button onclick="filterBookings()">Filter</button>
    </div>
    
    <table id="bookingTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle Type</th>
                <th>Pickup Time</th>
                <th>Drop Time</th>
                <th>Pickup Location</th>
                <th>Destination</th>
                <th>Payment Type</th>
                <th>Peak Time</th>
                <th>Prepayment</th>
            </tr>
        </thead>
        <tbody id="bookingData">
            <tr>
                <td colspan="9" class="loading">Loading booking history...</td>
            </tr>
        </tbody>
    </table>
    
    <a href="signup.html" class="back-link">Back to Sign Up</a>
</div>

<script>
    // Function to format date and time
    function formatDateTime(datetimeString) {
        if (!datetimeString) return 'N/A';
        const date = new Date(datetimeString);
        return date.toLocaleString();
    }

    // Function to display bookings
    function displayBookings(data, filter = 'all') {
        const tableBody = document.getElementById('bookingData');
        
        if (data.error) {
            tableBody.innerHTML = '<tr><td colspan="9" class="error">' + data.error + '</td></tr>';
            return;
        }
        
        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="9">No bookings found</td></tr>';
            return;
        }
        
        tableBody.innerHTML = '';
        
        const filteredData = filter === 'all' 
            ? data 
            : data.filter(function(booking) {
                return filter === 'peak' ? booking.is_peak : !booking.is_peak;
            });
        
        filteredData.forEach(function(booking) {
            const row = document.createElement('tr');
            if (booking.is_peak) {
                row.classList.add('peak-time');
            }
            
            row.innerHTML = `
                <td>${booking.id}</td>
                <td>${booking.vehicle_type}</td>
                <td>${formatDateTime(booking.pickup_time)}</td>
                <td>${formatDateTime(booking.drop_time)}</td>
                <td>${booking.pickup_location}</td>
                <td>${booking.destination}</td>
                <td>${booking.payment_type}</td>
                <td>${booking.is_peak ? 'Yes' : 'No'}</td>
                <td>${booking.prepayment_accepted ? 'Yes' : 'No'}</td>
            `;
            
            tableBody.appendChild(row);
        });
    }

    // Function to filter bookings
    function filterBookings() {
        const filterValue = document.getElementById('timeFilter').value;
        fetchBookings(filterValue);
    }

    // Function to fetch bookings
    function fetchBookings(filter = 'all') {
        const urlParams = new URLSearchParams(window.location.search);
        const user_id = urlParams.get('user_id') || 1;
        
        fetch(window.location.href.split('?')[0] + '?api=1&user_id=' + user_id)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function(data) {
                displayBookings(data, filter);
            })
            .catch(function(error) {
                console.error('Error:', error);
                displayBookings({ error: '' });
            });
    }

    // Initialize on page load
    window.onload = fetchBookings;
</script>

</body>
</html>