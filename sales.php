<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales</title>
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
        <h1>Sales Records</h1>
        <a href="add_sale.php" class="btn">+ Record Sale</a>
        <table>
            <tr>
                <th>Sale ID</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Price</th>
                <th>Payment</th>
            </tr>
            <?php
            $result = mysqli_query($conn, "
                SELECT s.sale_id, v.model, c.name as customer_name, 
                       e.name as employee_name, s.sale_date, 
                       s.sale_price, s.payment_mode
                FROM Sales s
                INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
                INNER JOIN Customer c ON s.customer_id = c.customer_id
                INNER JOIN Employee e ON s.employee_id = e.employee_id
            ");
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['sale_id']}</td>
                    <td>{$row['model']}</td>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['employee_name']}</td>
                    <td>{$row['sale_date']}</td>
                    <td>₹{$row['sale_price']}</td>
                    <td>{$row['payment_mode']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>