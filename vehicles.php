<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Vehicles</title>
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
        <h1>Vehicle Inventory</h1>
        <a href="add_vehicle.php" class="btn">+ Add Vehicle</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Model</th>
                <th>Year</th>
                <th>Type</th>
                <th>Fuel Type</th>
                <th>Color</th>
                <th>Status</th>
            </tr>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM Vehicle");
            while($row = mysqli_fetch_assoc($result)) {
                $statusClass = $row['status'] == 'Available' ? 'status-available' : 'status-sold';
                echo "<tr>
                    <td>{$row['vehicle_id']}</td>
                    <td>{$row['model']}</td>
                    <td>{$row['manufacturing_yr']}</td>
                    <td>{$row['type']}</td>
                    <td>{$row['fuel_type']}</td>
                    <td>{$row['color']}</td>
                    <td class='{$statusClass}'>{$row['status']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>