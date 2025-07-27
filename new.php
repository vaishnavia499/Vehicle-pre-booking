<section id="availability">
    <h2>Vehicle Availability</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>Vehicle</th>
            <th>Total Vehicles</th>
            <th>On Peak Time</th>
            <th>On Non-Peak Time</th>
            <th>Remaining</th>
        </tr>
        <?php
        $conn = new mysqli("localhost", "your_username", "your_password", "VehicleBooking");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT Vehicle, NoOfVehicles, VehicleOnPeakTime, VehicleOnNonPeakTime, RemainingVehicle FROM VehicleDetails";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["Vehicle"] . "</td>
                        <td>" . $row["NoOfVehicles"] . "</td>
                        <td>" . $row["VehicleOnPeakTime"] . "</td>
                        <td>" . $row["VehicleOnNonPeakTime"] . "</td>
                        <td>" . $row["RemainingVehicle"] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }

        $conn->close();
        ?>
    </table>
</section>
