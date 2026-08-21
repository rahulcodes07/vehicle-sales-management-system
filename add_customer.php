<?php
$pageTitle = "Register Customer";
include 'header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($phone) || empty($address)) {
        $errorMsg = "Please fill in all mandatory fields (Name, Phone, Address).";
    } else {
        // Using Prepared Statement to prevent SQL Injection
        $stmt = mysqli_prepare($conn, "INSERT INTO Customer (name, phone, email, address) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $phone, $email, $address);

        try {
            if (mysqli_stmt_execute($stmt)) {
                $successMsg = "Customer '{$name}' registered successfully!";
            } else {
                $errorMsg = "Error: " . mysqli_error($conn);
            }
        } catch (mysqli_sql_exception $e) {
            // Catches UNIQUE constraint violation on phone/email
            $errorMsg = "A customer with this phone number or email already exists in the database.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="page-header">
    <div>
        <h1>Register New Customer</h1>
        <p class="subtitle">Add customer details for sales leads and invoicing.</p>
    </div>
    <a href="customers.php" class="btn" style="background:#64748b;">&larr; Back to Directory</a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_customer.php">
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g. Rohan Kapoor" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g. 9876543210" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="e.g. rohan@gmail.com">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Residential / Delivery Address *</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter complete postal address..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Register Customer</button>
    </form>
</div>

<?php include 'footer.php'; ?>
