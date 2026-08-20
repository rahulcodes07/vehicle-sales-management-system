<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Record Sale</title>
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
        <h1>Record New Sale</h1>
        <?php
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $vehicle_id   = $_POST['vehicle_id'];
            $customer_id  = $_POST['customer_id'];
            $employee_id  = $_POST['employee_id'];
            $sale_price   = $_POST['sale_price'];
            $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode']);
            $sale_date    = date('Y-m-d');

            $sql = "INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
                    VALUES ('$vehicle_id','$customer_id','$employee_id','$sale_date','$sale_price','$payment_mode')";

            if(mysqli_query($conn, $sql)) {
                mysqli_query($conn, "UPDATE Vehicle SET status='Sold' WHERE vehicle_id='$vehicle_id'");
                echo "<div class='success'>Sale recorded successfully!</div>";
            }
        }

        $vehicles  = mysqli_query($conn, "SELECT * FROM Vehicle WHERE status='Available'");
        $customers = mysqli_query($conn, "SELECT * FROM Customer");
        $employees = mysqli_query($conn, "SELECT * FROM Employee");
        ?>
        <form method="POST">
            <label>Vehicle</label>
            <select name="vehicle_id">
                <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                    <option value="<?php echo $v['vehicle_id']; ?>">
                        <?php echo $v['model'] . ' - ' . $v['color']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Customer</label>
            <select name="customer_id">
                <?php while($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $c['customer_id']; ?>">
                        <?php echo $c['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Employee</label>
            <select name="employee_id">
                <?php while($e = mysqli_fetch_assoc($employees)): ?>
                    <option value="<?php echo $e['employee_id']; ?>">
                        <?php echo $e['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Sale Price</label>
            <input type="number" name="sale_price" required>

            <label>Payment Mode</label>
            <select name="payment_mode">
                <option>Cash</option>
                <option>Loan</option>
                <option>Card</option>
            </select>

            <button type="submit" class="btn">Record Sale</button>
        </form>
    </div>
</body>
</html>