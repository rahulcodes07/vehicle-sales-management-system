<?php
$pageTitle = "Register Customer";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
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
    <div class="page-header-content">
        <h1>Register New Dealership Client</h1>
        <p class="subtitle">Enter customer identification details, mobile phone number, and postal address for billing and contract registration.</p>
    </div>
    <a href="customers.php" class="btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        <span>Back to Clients</span>
    </a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success">
        <div class="alert-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span><?php echo htmlspecialchars($successMsg); ?></span>
        </div>
        <button type="button" data-dismiss aria-label="Dismiss">&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger">
        <div class="alert-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span><?php echo htmlspecialchars($errorMsg); ?></span>
        </div>
        <button type="button" data-dismiss aria-label="Dismiss">&times;</button>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_customer.php">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        
        <div class="form-group">
            <label for="name">Client Full Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g. Vikram Malhotra, Ananya Gupta" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="phone">Primary Mobile Number (Unique) *</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g. 9876543210" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="e.g. client@example.com">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Residential / Delivery Address *</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter complete billing/postal address with pincode..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Register Client in System</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>
