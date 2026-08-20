<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="customers.php">Customers</a>
        <a href="employees.php">Employees</a>
        <a href="sales.php">Sales</a>
        <a href="insurance.php">Insurance</a>
    </nav>
    <div class="container">
        <h1>Add New Vehicle</h1>
        <?php
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model    = mysqli_real_escape_string($conn, $_POST['model']);
            $yr       = $_POST['manufacturing_yr'];
            $type     = mysqli_real_escape_string($conn, $_POST['type']);
            $fuel     = mysqli_real_escape_string($conn, $_POST['fuel_type']);
            $color    = mysqli_real_escape_string($conn, $_POST['color']);
            $status   = 'Available';

            $sql = "INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, status)
                    VALUES ('$model', '$yr', '$type', '$fuel', '$color', '$status')";

            if(mysqli_query($conn, $sql)) {
                echo "<div class='success'>Vehicle added successfully!</div>";
            }
        }
        ?>
        <form method="POST">
            <label>Model</label>
            <input type="text" name="model" required>

            <label>Manufacturing Year</label>
            <input type="number" name="manufacturing_yr" min="2000" max="2025" required>

            <label>Type</label>
            <select name="type">
                <option>Car</option>
                <option>Bike</option>
                <option>SUV</option>
                <option>Truck</option>
            </select>

            <label>Fuel Type</label>
            <select name="fuel_type">
                <option>Petrol</option>
                <option>Diesel</option>
                <option>Electric</option>
                <option>CNG</option>
            </select>

            <label>Color</label>
            <input type="text" name="color" required>

            <button type="submit" class="btn">Add Vehicle</button>
        </form>
    </div>
</body>
</html>