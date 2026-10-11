<?php
$pageTitle = "Record Sale";
require_once __DIR__ . '/../app/Views/layouts/header.php';
require_once __DIR__ . '/../app/Services/SaleService.php';

$successMsg = '';
$errorMsg = '';
$selected_vehicle_id = intval($_GET['vehicle_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $vehicle_id   = intval($_POST['vehicle_id'] ?? 0);
    $customer_id  = intval($_POST['customer_id'] ?? 0);
    $employee_id  = intval($_POST['employee_id'] ?? 0);
    $sale_price   = floatval($_POST['sale_price'] ?? 0);
    $payment_mode = $_POST['payment_mode'] ?? 'Cash';

    $allowedPaymentModes = ['Cash', 'Loan', 'Card', 'UPI', 'Net Banking'];
    if ($vehicle_id <= 0 || $customer_id <= 0 || $employee_id <= 0 || $sale_price <= 0 || !in_array($payment_mode, $allowedPaymentModes, true)) {
        $errorMsg = "Please fill all fields with valid data (Sale price must be greater than 0).";
    } else {
        try {
            $result = (new SaleService($conn))->record(
                $vehicle_id,
                $customer_id,
                $employee_id,
                $sale_price,
                $payment_mode
            );
            if ($result['status_code'] === 200) {
                $successMsg = $result['status_msg'];
            } else {
                $errorMsg = $result['status_msg'];
            }
        } catch (mysqli_sql_exception $exception) {
            $errorMsg = 'The database sale procedure could not complete. Verify the ADBMS objects are imported.';
        }
    }
}

// Fetch available vehicles using our SQL View (Preserved intact)
$vehicles  = mysqli_query($conn, "SELECT * FROM view_available_vehicles ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT * FROM Customer ORDER BY name ASC");
$employees = mysqli_query($conn, "SELECT e.*, b.name as branch_name FROM Employee e INNER JOIN Branch b ON e.branch_id = b.branch_id ORDER BY e.name ASC");
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Record Vehicle Purchase Contract</h1>
        <p class="subtitle">Execute vehicle sales transaction under database-enforced ACID row-locking isolation.</p>
    </div>
    <a href="sales.php" class="btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        <span>Back to Sales</span>
    </a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success">
        <div class="alert-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <strong>Transaction Committed!</strong>
                <div><?php echo htmlspecialchars($successMsg); ?></div>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="sales.php" class="btn btn-sm btn-primary">View Ledger</a>
            <a href="audit_log.php" class="btn btn-sm">Trigger Log</a>
        </div>
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
    <form method="POST" action="add_sale.php">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        
        <div class="form-group">
            <label for="vehicle_id">Select Available Vehicle (via <code>view_available_vehicles</code>) *</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">-- Choose Available Vehicle From Showroom Stock --</option>
                <?php if (mysqli_num_rows($vehicles) > 0): ?>
                    <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?php echo $v['vehicle_id']; ?>" <?php echo ($selected_vehicle_id == $v['vehicle_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['model'] . ' (' . $v['color'] . ', ' . $v['manufacturing_yr'] . ') — Base: ₹' . number_format((float)$v['price'], 2)); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>No available vehicles in showroom stock</option>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="customer_id">Select Purchasing Client *</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">-- Select Registered Client --</option>
                <?php while($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $c['customer_id']; ?>">
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['phone'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small style="display:block; margin-top:5px; color:var(--text-muted);">
                Need a new client? <a href="add_customer.php" target="_blank" class="text-link">Register new customer &rarr;</a>
            </small>
        </div>

        <div class="form-group">
            <label for="employee_id">Assigned Sales Consultant *</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">-- Select Sales Representative --</option>
                <?php while($e = mysqli_fetch_assoc($employees)): ?>
                    <option value="<?php echo $e['employee_id']; ?>">
                        <?php echo htmlspecialchars($e['name'] . ' &bull; ' . $e['role'] . ' (' . $e['branch_name'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="sale_price">Final Agreed Purchase Price (₹) *</label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="1" placeholder="e.g. 1800000" required>
            </div>

            <div class="form-group">
                <label for="payment_mode">Financing / Payment Mode *</label>
                <select id="payment_mode" name="payment_mode">
                    <option value="Cash">Cash Settlement</option>
                    <option value="Loan">Auto Loan / Financing</option>
                    <option value="Card">Debit / Credit Card</option>
                    <option value="UPI">UPI Payment</option>
                    <option value="Net Banking">Net Banking / NEFT</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Execute &amp; Commit Sale Transaction</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>