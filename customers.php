<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
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
        <h1>Customers</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
            </tr>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM Customer");
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['customer_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['phone']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['address']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>