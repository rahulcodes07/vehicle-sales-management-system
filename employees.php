<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Employees</title>
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
        <h1>Employees</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Salary</th>
                <th>Hire Date</th>
                <th>Branch</th>
            </tr>
            <?php
            $result = mysqli_query($conn, "
                SELECT e.*, b.location as branch_location 
                FROM Employee e 
                INNER JOIN Branch b ON e.branch_id = b.branch_id
            ");
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['employee_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['role']}</td>
                    <td>{$row['phone']}</td>
                    <td>₹{$row['salary']}</td>
                    <td>{$row['hire_date']}</td>
                    <td>{$row['branch_location']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>