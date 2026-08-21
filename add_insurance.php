<?php
$pageTitle = "Issue Insurance";
include 'header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $errorMsg = "Policy End Date must be after the Start Date (Enforcing Table Check Constraint).";
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

// Fetch all vehicles and customers
$vehicles  = mysqli_query($conn, "SELECT vehicle_id, model, color, status FROM Vehicle ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT customer_id, name, phone FROM Customer ORDER BY name ASC");
?>

<div class="page-header">
    <div>
        <h1>Issue Insurance Policy</h1>
        <p class="subtitle">Link comprehensive insurance cover to vehicles and owners.</p>
    </div>
    <a href="insurance.php" class="btn" style="background:#64748b;">&larr; Back to Policies</a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_insurance.php">
        <div class="form-group">
            <label for="vehicle_id">Insured Vehicle *</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">-- Select Vehicle --</option>
                <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                    <option value="<?php echo $v['vehicle_id']; ?>">
                        <?php echo htmlspecialchars($v['model'] . ' (' . $v['color'] . ') - ' . $v['status']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="customer_id">Insured Customer / Policyholder *</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">-- Select Customer --</option>
                <?php while($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $c['customer_id']; ?>">
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['phone'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="provider">Insurance Company *</label>
                <select id="provider" name="provider">
                    <option value="HDFC ERGO General Insurance">HDFC ERGO</option>
                    <option value="ICICI Lombard">ICICI Lombard</option>
                    <option value="Bajaj Allianz">Bajaj Allianz</option>
                    <option value="Tata AIG">Tata AIG</option>
                    <option value="New India Assurance">New India Assurance</option>
                </select>
            </div>

            <div class="form-group">
                <label for="policy_no">Policy Number *</label>
                <input type="text" id="policy_no" name="policy_no" value="POL-<?php echo strtoupper(bin2hex(random_bytes(4))); ?>" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="start_date">Policy Start Date *</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">Policy End Date *</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="premium">Annual Premium Amount (₹) *</label>
            <input type="number" id="premium" name="premium" step="100" min="500" placeholder="e.g. 28000" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Issue Policy</button>
    </form>
</div>

<?php include 'footer.php'; ?>
