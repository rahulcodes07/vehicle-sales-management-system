<?php
$pageTitle = "Add Vehicle";
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
        $errorMsg = "Please provide valid vehicle details (Year >= 1990 and Price > 0).";
    } else {
        // SECURE: Using MySQLi Prepared Statement to prevent SQL Injection
        $stmt = mysqli_prepare($conn, "INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sisssds", $model, $yr, $type, $fuel, $color, $price, $status);

        if (mysqli_stmt_execute($stmt)) {
            $successMsg = "Vehicle '{$model}' added successfully to inventory!";
        } else {
            $errorMsg = "Database Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Onboard New Vehicle to Fleet</h1>
        <p class="subtitle">Enter specifications, powertrain category, and showroom price to register inventory stock.</p>
    </div>
    <a href="vehicles.php" class="btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        <span>Back to Inventory</span>
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
    <form method="POST" action="add_vehicle.php">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        
        <div class="form-group">
            <label for="model">Vehicle Model & Trim *</label>
            <input type="text" id="model" name="model" placeholder="e.g. Tata Harrier Fearless Plus, Mahindra XUV700" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="manufacturing_yr">Manufacturing Model Year *</label>
                <input type="number" id="manufacturing_yr" name="manufacturing_yr" min="1990" max="<?php echo date('Y') + 1; ?>" value="<?php echo date('Y'); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Ex-Showroom Base Price (₹) *</label>
                <input type="number" id="price" name="price" step="0.01" min="1" placeholder="e.g. 1850000" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="type">Vehicle Category</label>
                <select id="type" name="type">
                    <option value="Car">Passenger Car</option>
                    <option value="Bike">Motorcycle / Bike</option>
                    <option value="SUV">SUV / Crossover</option>
                    <option value="Truck">Commercial Truck</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fuel_type">Powertrain / Fuel Variant</label>
                <select id="fuel_type" name="fuel_type">
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric (EV)</option>
                    <option value="CNG">CNG</option>
                    <option value="Hybrid">Strong Hybrid</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="color">Exterior Body Paint / Color *</label>
            <input type="text" id="color" name="color" placeholder="e.g. Daytona Grey, Napoli Black" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Save Vehicle to Stock</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>