<?php
$pageTitle = "Add vehicle";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $model    = trim($_POST['model'] ?? '');
    $yr       = intval($_POST['manufacturing_yr'] ?? 0);
    $type     = $_POST['type'] ?? 'Car';
    $fuel     = $_POST['fuel_type'] ?? 'Petrol';
    $color    = trim($_POST['color'] ?? '');
    $price    = floatval($_POST['price'] ?? 0);
    $status   = 'Available';

    if (empty($model) || empty($color) || $yr < 1990 || $price <= 0) {
        $errorMsg = "Enter a model and colour, a year from 1990 onward, and a price above zero.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sisssds", $model, $yr, $type, $fuel, $color, $price, $status);

        try {
            mysqli_stmt_execute($stmt);
            $successMsg = "{$model} was added to stock.";
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "The vehicle could not be saved. Check the details and try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Keep what was typed when there is an error
$form = $errorMsg !== '' ? $_POST : [];

page_head(
    'Add a vehicle',
    'Enter the vehicle\'s details to add it to stock.',
    '<a href="vehicles.php" class="btn">' . icon('arrow-left', 16) . 'Back to vehicles</a>'
);

if ($successMsg !== '') echo alert_box('success', $successMsg);
if ($errorMsg !== '') echo alert_box('danger', $errorMsg);
?>

<form method="POST" action="add_vehicle.php" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-section">
        <h2>Vehicle details</h2>
        <p>Fields marked <span class="req">*</span> are required.</p>

        <div class="field">
            <label for="model">Model and trim <span class="req">*</span></label>
            <input type="text" id="model" name="model" value="<?= e($form['model'] ?? '') ?>" placeholder="For example, Tata Harrier Fearless Plus" required>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="manufacturing_yr">Model year <span class="req">*</span></label>
                <input type="number" id="manufacturing_yr" name="manufacturing_yr" min="1990" max="<?= date('Y') + 1 ?>" value="<?= e($form['manufacturing_yr'] ?? date('Y')) ?>" required>
            </div>
            <div class="field">
                <label for="color">Colour <span class="req">*</span></label>
                <input type="text" id="color" name="color" value="<?= e($form['color'] ?? '') ?>" placeholder="For example, Daytona Grey" required>
            </div>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="type">Category</label>
                <select id="type" name="type">
                    <?php foreach (['Car', 'Bike', 'SUV', 'Truck'] as $opt): ?>
                        <option value="<?= e($opt) ?>" <?= selected($form['type'] ?? 'Car', $opt) ?>><?= e($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="fuel_type">Fuel</label>
                <select id="fuel_type" name="fuel_type">
                    <?php foreach (['Petrol' => 'Petrol', 'Diesel' => 'Diesel', 'Electric' => 'Electric', 'CNG' => 'CNG', 'Hybrid' => 'Hybrid'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= selected($form['fuel_type'] ?? 'Petrol', $value) ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Price</h2>
        <p>The listed price before any negotiation.</p>
        <div class="field">
            <label for="price">Showroom price (₹) <span class="req">*</span></label>
            <input type="number" id="price" name="price" step="0.01" min="1" value="<?= e($form['price'] ?? '') ?>" placeholder="For example, 1850000" required>
        </div>
    </div>

    <div class="form-actions">
        <a href="vehicles.php" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Save vehicle</button>
    </div>
</form>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>