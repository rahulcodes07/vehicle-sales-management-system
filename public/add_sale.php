<?php
$pageTitle = "Record sale";
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
        $errorMsg = "Choose a vehicle, a customer and a consultant, and enter a price above zero.";
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
                $successMsg = 'The sale was recorded and the vehicle is now marked as sold.';
            } else {
                $errorMsg = match ($result['status_code']) {
                    404 => 'That vehicle could not be found. Choose a vehicle from the list.',
                    409 => 'This vehicle has already been sold. Choose another one.',
                    default => 'The sale could not be recorded. Check the customer and consultant and try again.',
                };
            }
        } catch (mysqli_sql_exception $exception) {
            $errorMsg = 'The sale could not be recorded right now. Please try again.';
        }
    }
}

$vehicles  = mysqli_query($conn, "SELECT * FROM view_available_vehicles ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT * FROM Customer ORDER BY name ASC");
$employees = mysqli_query($conn, "SELECT e.*, b.name as branch_name FROM Employee e INNER JOIN Branch b ON e.branch_id = b.branch_id ORDER BY e.name ASC");

// Keep the choices when there is an error
$form = $errorMsg !== '' ? $_POST : [];
$preselect = (int) ($form['vehicle_id'] ?? $selected_vehicle_id);

page_head(
    'Record a sale',
    'Choose the vehicle, the customer and the consultant, then enter the agreed price.',
    '<a href="sales.php" class="btn">' . icon('arrow-left', 16) . 'Back to sales</a>'
);

if ($errorMsg !== '') echo alert_box('danger', $errorMsg);
?>

<?php if ($successMsg !== ''): ?>
    <div class="alert alert-success" role="status">
        <?= icon('check-circle', 18) ?>
        <p><?= e($successMsg) ?></p>
        <a href="sales.php" class="btn btn-sm">View sales</a>
        <button type="button" class="alert-close" data-dismiss aria-label="Dismiss"><?= icon('x', 16) ?></button>
    </div>
<?php endif; ?>

<form method="POST" action="add_sale.php" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-section">
        <h2>Who and what</h2>
        <p>Only vehicles that are still available can be sold.</p>

        <div class="field">
            <label for="vehicle_id">Vehicle <span class="req">*</span></label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">Choose a vehicle</option>
                <?php if (mysqli_num_rows($vehicles) > 0): ?>
                    <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                        <option value="<?= (int) $v['vehicle_id'] ?>" <?= $preselect === (int) $v['vehicle_id'] ? 'selected' : '' ?>>
                            <?= e($v['model'] . ' (' . $v['color'] . ', ' . $v['manufacturing_yr'] . '), listed at ' . money((float) $v['price'])) ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>No vehicles are available right now</option>
                <?php endif; ?>
            </select>
        </div>

        <div class="field">
            <label for="customer_id">Customer <span class="req">*</span></label>
            <select id="customer_id" name="customer_id" required>
                <option value="">Choose a customer</option>
                <?php while ($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?= (int) $c['customer_id'] ?>" <?= selected($form['customer_id'] ?? '', $c['customer_id']) ?>>
                        <?= e($c['name'] . ' (' . $c['phone'] . ')') ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <span class="hint">Not registered yet? <a href="add_customer.php" target="_blank" rel="noopener">Register a new customer</a></span>
        </div>

        <div class="field">
            <label for="employee_id">Sales consultant <span class="req">*</span></label>
            <select id="employee_id" name="employee_id" required>
                <option value="">Choose a consultant</option>
                <?php while ($e = mysqli_fetch_assoc($employees)): ?>
                    <option value="<?= (int) $e['employee_id'] ?>" <?= selected($form['employee_id'] ?? '', $e['employee_id']) ?>>
                        <?= htmlspecialchars($e['name'] . ', ' . $e['role'] . ' (' . $e['branch_name'] . ')') ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="form-section">
        <h2>Payment</h2>
        <p>The price you and the customer agreed on.</p>

        <div class="field-grid">
            <div class="field">
                <label for="sale_price">Agreed price (₹) <span class="req">*</span></label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="1" value="<?= e($form['sale_price'] ?? '') ?>" placeholder="For example, 1800000" required>
            </div>
            <div class="field">
                <label for="payment_mode">Payment method <span class="req">*</span></label>
                <select id="payment_mode" name="payment_mode">
                    <?php foreach (['Cash' => 'Cash', 'Loan' => 'Loan or finance', 'Card' => 'Card', 'UPI' => 'UPI', 'Net Banking' => 'Net banking'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= selected($form['payment_mode'] ?? 'Cash', $value) ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="sales.php" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Record sale</button>
    </div>
</form>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>