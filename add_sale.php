<?php
$pageTitle = "Record Sale";
include 'header.php';

$successMsg = '';
$errorMsg = '';
$selected_vehicle_id = intval($_GET['vehicle_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id   = intval($_POST['vehicle_id'] ?? 0);
    $customer_id  = intval($_POST['customer_id'] ?? 0);
    $employee_id  = intval($_POST['employee_id'] ?? 0);
    $sale_price   = floatval($_POST['sale_price'] ?? 0);
    $payment_mode = $_POST['payment_mode'] ?? 'Cash';

    if ($vehicle_id <= 0 || $customer_id <= 0 || $employee_id <= 0 || $sale_price <= 0) {
        $errorMsg = "Please fill all fields with valid data (Sale price must be greater than 0).";
    } else {
        // =========================================================================
        // ADBMS Concept: Executing Database Stored Procedure (sp_RecordSale)
        // Encapsulating ACID Transaction & Database Integrity on the MySQL Server
        // =========================================================================
        
        // Prepare call to Stored Procedure
        $stmt = mysqli_prepare($conn, "CALL sp_RecordSale(?, ?, ?, ?, ?, @status_code, @status_msg)");
        mysqli_stmt_bind_param($stmt, "iiids", $vehicle_id, $customer_id, $employee_id, $sale_price, $payment_mode);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            
            // Retrieve OUT parameters from Stored Procedure
            $out_res = mysqli_query($conn, "SELECT @status_code AS status_code, @status_msg AS status_msg");
            $out_row = mysqli_fetch_assoc($out_res);
            
            if ($out_row && $out_row['status_code'] == 200) {
                $successMsg = $out_row['status_msg'];
            } else {
                $errorMsg = $out_row['status_msg'] ?? "Transaction failed.";
            }
        } else {
            // Fallback to explicit PHP-level ACID Transaction if Stored Procedure isn't imported yet
            mysqli_begin_transaction($conn);
            try {
                $insert_stmt = mysqli_prepare($conn, "INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                mysqli_stmt_bind_param($insert_stmt, "iiids", $vehicle_id, $customer_id, $employee_id, $sale_price, $payment_mode);
                mysqli_stmt_execute($insert_stmt);

                // Update vehicle status
                $update_stmt = mysqli_prepare($conn, "UPDATE Vehicle SET status = 'Sold' WHERE vehicle_id = ?");
                mysqli_stmt_bind_param($update_stmt, "i", $vehicle_id);
                mysqli_stmt_execute($update_stmt);

                mysqli_commit($conn); // Atomicity: Both queries committed together
                $successMsg = "Sale recorded successfully and inventory status updated (ACID Transaction Committed)!";
            } catch (Exception $e) {
                mysqli_rollback($conn); // Rollback on error to maintain consistency
                $errorMsg = "Transaction Rolled Back: " . $e->getMessage();
            }
        }
    }
}

// Fetch available vehicles using our SQL View
$vehicles  = mysqli_query($conn, "SELECT * FROM view_available_vehicles ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT * FROM Customer ORDER BY name ASC");
$employees = mysqli_query($conn, "SELECT e.*, b.name as branch_name FROM Employee e INNER JOIN Branch b ON e.branch_id = b.branch_id ORDER BY e.name ASC");
?>

<div class="page-header">
    <div>
        <h1>Record Vehicle Sale Transaction</h1>
        <p class="subtitle">Process customer vehicle purchases with ACID transaction safety.</p>
    </div>
    <a href="sales.php" class="btn" style="background:#64748b;">&larr; Back to Sales</a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($successMsg); ?>
        <div style="margin-top:10px;">
            <a href="sales.php" class="btn btn-sm">View Sales Records</a>
            <a href="audit_log.php" class="btn btn-sm" style="background:#10b981;">View Trigger Audit Log</a>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS Transaction Safety:</strong> When this form is submitted, MySQL executes the <code>sp_RecordSale</code> Stored Procedure. If any part of the sale fails, a <code>ROLLBACK</code> is triggered. When successful, the MySQL Trigger (<code>trg_after_sale_insert</code>) automatically updates the vehicle status and records an audit log.
</div>

<div class="form-card">
    <form method="POST" action="add_sale.php">
        <div class="form-group">
            <label for="vehicle_id">Select Available Vehicle (from <code>view_available_vehicles</code>) *</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">-- Choose Vehicle --</option>
                <?php if (mysqli_num_rows($vehicles) > 0): ?>
                    <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?php echo $v['vehicle_id']; ?>" <?php echo ($selected_vehicle_id == $v['vehicle_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['model'] . ' (' . $v['color'] . ', ' . $v['manufacturing_yr'] . ') - Base Price: ₹' . number_format($v['price'], 2)); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>No available vehicles in stock</option>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="customer_id">Select Registered Customer *</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">-- Choose Customer --</option>
                <?php while($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $c['customer_id']; ?>">
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['phone'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div style="font-size:12px; margin-top:4px;">Need a new customer? <a href="add_customer.php" target="_blank">+ Register new customer</a></div>
        </div>

        <div class="form-group">
            <label for="employee_id">Sales Consultant (Employee) *</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">-- Choose Sales Rep --</option>
                <?php while($e = mysqli_fetch_assoc($employees)): ?>
                    <option value="<?php echo $e['employee_id']; ?>">
                        <?php echo htmlspecialchars($e['name'] . ' - ' . $e['role'] . ' (' . $e['branch_name'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="sale_price">Final Negotiated Sale Price (₹) *</label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="1" placeholder="e.g. 1450000" required>
            </div>

            <div class="form-group">
                <label for="payment_mode">Payment Mode *</label>
                <select id="payment_mode" name="payment_mode">
                    <option value="Cash">Cash</option>
                    <option value="Loan">Loan</option>
                    <option value="Card">Credit/Debit Card</option>
                    <option value="UPI">UPI</option>
                    <option value="Net Banking">Net Banking</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Complete Sale Transaction</button>
    </form>
</div>

<?php include 'footer.php'; ?>