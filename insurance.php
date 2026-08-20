<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Insurance</title>
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
        <h1>Insurance Records</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Provider</th>
                <th>Policy No</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Premium</th>
            </tr>
            <?php
            $result = mysqli_query($conn, "
                SELECT i.*, v.model, c.name as customer_name
                FROM Insurance i
                INNER JOIN Vehicle v ON i.vehicle_id = v.vehicle_id
                INNER JOIN Customer c ON i.customer_id = c.customer_id
            ");
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['insurance_id']}</td>
                    <td>{$row['model']}</td>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['provider']}</td>
                    <td>{$row['policy_no']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>₹{$row['premium']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>