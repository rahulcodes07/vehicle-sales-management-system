<?php
$pageTitle = "Issue policy";
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
        $errorMsg = "Fill in every field and enter a premium above zero.";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $errorMsg = "The end date must be after the start date.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissssd", $vehicle_id, $customer_id, $provider, $policy_no, $start_date, $end_date, $premium);

        try {
            mysqli_stmt_execute($stmt);
            $successMsg = "Policy {$policy_no} was issued.";
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "The policy could not be saved. The policy number may already exist.";
        }
        mysqli_stmt_close($stmt);
    }
}

$vehicles  = mysqli_query($conn, "SELECT vehicle_id, model, color, status FROM Vehicle ORDER BY model ASC");
$customers = mysqli_query($conn, "SELECT customer_id, name, phone FROM Customer ORDER BY name ASC");

$form = $errorMsg !== '' ? $_POST : [];
$defaultPolicyNo = 'POL-' . strtoupper(bin2hex(random_bytes(4)));

page_head(
    'Issue an insurance policy',
    'Link a policy to a vehicle and the person insured.',
    '<a href="insurance.php" class="btn">' . icon('arrow-left', 16) . 'Back to insurance</a>'
);

if ($successMsg !== '') echo alert_box('success', $successMsg);
if ($errorMsg !== '') echo alert_box('danger', $errorMsg);
?>

<form method="POST" action="add_insurance.php" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-section">
        <h2>Who and what is covered</h2>
        <p>Fields marked <span class="req">*</span> are required.</p>

        <div class="field">
            <label for="vehicle_id">Vehicle <span class="req">*</span></label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">Choose a vehicle</option>
                <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                    <option value="<?= (int) $v['vehicle_id'] ?>" <?= selected($form['vehicle_id'] ?? '', $v['vehicle_id']) ?>>
                        <?= e($v['model'] . ' (' . $v['color'] . '), ' . strtolower((string) $v['status'])) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="field">
            <label for="customer_id">Policyholder <span class="req">*</span></label>
            <select id="customer_id" name="customer_id" required>
                <option value="">Choose a customer</option>
                <?php while ($c = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?= (int) $c['customer_id'] ?>" <?= selected($form['customer_id'] ?? '', $c['customer_id']) ?>>
                        <?= e($c['name'] . ' (' . $c['phone'] . ')') ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="form-section">
        <h2>Policy details</h2>
        <p>The insurer, the policy number and the cover dates.</p>

        <div class="field-grid">
            <div class="field">
                <label for="provider">Insurer <span class="req">*</span></label>
                <select id="provider" name="provider">
                    <?php foreach (['HDFC ERGO General Insurance', 'ICICI Lombard General Insurance', 'Bajaj Allianz General Insurance', 'Tata AIG General Insurance', 'New India Assurance'] as $opt): ?>
                        <option value="<?= e($opt) ?>" <?= selected($form['provider'] ?? '', $opt) ?>><?= e($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="policy_no">Policy number <span class="req">*</span></label>
                <input type="text" id="policy_no" name="policy_no" value="<?= e($form['policy_no'] ?? $defaultPolicyNo) ?>" required>
                <span class="hint">Must not match an existing policy.</span>
            </div>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="start_date">Cover starts <span class="req">*</span></label>
                <input type="date" id="start_date" name="start_date" value="<?= e($form['start_date'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="field">
                <label for="end_date">Cover ends <span class="req">*</span></label>
                <input type="date" id="end_date" name="end_date" value="<?= e($form['end_date'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" required>
            </div>
        </div>
        <div class="field">
            <label for="premium">Yearly premium (₹) <span class="req">*</span></label>
            <input type="number" id="premium" name="premium" step="100" min="500" value="<?= e($form['premium'] ?? '') ?>" placeholder="For example, 34000" required>
        </div>
    </div>

    <div class="form-actions">
        <a href="insurance.php" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Issue policy</button>
    </div>
</form>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>