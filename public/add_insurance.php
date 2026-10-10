<?php
$pageTitle = "Issue Insurance";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $vehicle_id  = intval($_POST['vehicle_id'] ?? 0);
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $provider    = trim($_POST['provider'] ?? '');
    $policy_no   = trim($_POST['policy_no'] ?? '');
    $start_date  = $_POST['start_date'] ?? '';
    $end_date    = $_POST['end_date'] ?? '';
    $premium     = floatval($_POST['premium'] ?? 0);

    if ($vehicle_id <= 0 || $customer_id <= 0 || empty($provider) || empty($policy_no) || empty($start_date) || empty($end_date) || $premium <= 0) {
        $errorMsg = "Please fill in all mandatory fields with valid values.";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $errorMsg = "Policy End Date must be after the Start Date (Enforcing Table Check Constraint: end_date > start_date).";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissssd", $vehicle_id, $customer_id, $provider, $policy_no, $start_date, $end_date, $premium);

        try {
            if (mysqli_stmt_execute($stmt)) {
                $successMsg = "Insurance Policy #{$policy_no} issued successfully!";
            } else {
                $errorMsg = "Database Error: " . mysqli_error($conn);
            }
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "Error: Policy Number must be unique, or Check Constraint failed.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all vehicles and customers (Preserved intact)
$vehicles  = mysqli_query($conn, "SELECT vehicle_id, model, color, status FROM Vehicle ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT customer_id, name, phone FROM Customer ORDER BY name ASC");
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Issue Vehicle Insurance Policy</h1>
        <p class="subtitle">Bind comprehensive vehicle coverage policy to vehicle inventory and verified client policyholders.</p>
    </div>
    <a href="insurance.php" class="btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        <span>Back to Policies</span>
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

<div class="concept-box">
    <strong>💡 Table Check Constraint (Proposal Phase 2 &amp; 3):</strong> The Insurance entity enforces the constraint <code>CHECK (end_date &gt; start_date)</code> directly at the database engine level. Entering an invalid expiration date triggers an integrity exception.
</div>

<div class="form-card">
    <form method="POST" action="add_insurance.php">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        
        <div class="form-group">
            <label for="vehicle_id">Covered Vehicle *</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">-- Choose Vehicle For Policy --</option>
                <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                    <option value="<?php echo $v['vehicle_id']; ?>">
                        <?php echo htmlspecialchars($v['model'] . ' (' . $v['color'] . ') &bull; ' . $v['status']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="customer_id">Insured Policyholder / Client *</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">-- Choose Client Policyholder --</option>
                <?php while($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $c['customer_id']; ?>">
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['phone'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="provider">Underwriting Insurance Company *</label>
                <select id="provider" name="provider">
                    <option value="HDFC ERGO General Insurance">HDFC ERGO General Insurance</option>
                    <option value="ICICI Lombard General Insurance">ICICI Lombard General Insurance</option>
                    <option value="Bajaj Allianz General Insurance">Bajaj Allianz General Insurance</option>
                    <option value="Tata AIG General Insurance">Tata AIG General Insurance</option>
                    <option value="New India Assurance">New India Assurance</option>
                </select>
            </div>

            <div class="form-group">
                <label for="policy_no">Policy Certificate Number (Unique) *</label>
                <input type="text" id="policy_no" name="policy_no" value="POL-<?php echo strtoupper(bin2hex(random_bytes(4))); ?>" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="start_date">Policy Effective Date *</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">Policy Expiration Date (Must be &gt; Start) *</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="premium">Annual Gross Premium (₹) *</label>
            <input type="number" id="premium" name="premium" step="100" min="500" placeholder="e.g. 34000" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Bind &amp; Issue Insurance Certificate</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>
