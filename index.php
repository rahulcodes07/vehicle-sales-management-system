<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>VSMS</title>
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
        <h1>Vehicle Sales Management System</h1>
        <p>Welcome to VSMS. Use the navigation above to manage vehicles, customers, employees, sales and insurance records.</p>
        <br>
        <?php
        $v = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle"));
        $c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Customer"));
        $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Sales"));
        ?>
        <table style="max-width:400px">
            <tr><th>Summary</th><th>Count</th></tr>
            <tr><td>Total Vehicles</td><td><?php echo $v['total']; ?></td></tr>
            <tr><td>Total Customers</td><td><?php echo $c['total']; ?></td></tr>
            <tr><td>Total Sales</td><td><?php echo $s['total']; ?></td></tr>
        </table>
    </div>
</body>
</html>