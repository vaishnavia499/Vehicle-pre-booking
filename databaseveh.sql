-- Step 1: Use the existing database or create a new one
CREATE DATABASE IF NOT EXISTS VehicleBooking;
USE VehicleBooking;

-- Step 2: Drop the table if it already exists to avoid conflicts
DROP TABLE IF EXISTS VehicleDetails;

-- Step 3: Create the table with Vehicle as the primary key
CREATE TABLE VehicleDetails (
    SNo INT,
    Vehicle VARCHAR(50),
    NoOfVehicles INT,
    AmountPerKm INT,
    VehicleOnPeakTime INT, 
    VehicleOnNonPeakTime INT, 
    RemainingVehicle INT,
    PRIMARY KEY (Vehicle)  -- Ensures no duplicate Vehicles
);

-- Step 4: Insert data without duplicates (7 values per row)
INSERT IGNORE INTO VehicleDetails (SNo, Vehicle, NoOfVehicles, AmountPerKm, VehicleOnPeakTime, VehicleOnNonPeakTime, RemainingVehicle)
VALUES
    (1, 'Car', 30, 20, 15, 15, 0),
    (2, 'Bus', 16, 40, 6, 10, 0),
    (3, 'Van', 10, 35, 3, 7, 0),
    (4, 'Bike', 20, 15, 10, 10, 0);

-- Step 5: Display the table data to confirm insertion
SELECT * FROM VehicleDetails;
