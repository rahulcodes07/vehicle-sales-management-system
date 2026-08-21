<?php
$pageTitle = "Add Vehicle";
include 'header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <div>
        <h1>Add New Vehicle</h1>
        <p class="subtitle">Enter specifications to register new inventory stock.</p>
    </div>
    <a href="vehicles.php" class="btn" style="background:#64748b;">&larr; Back to Vehicles</a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_vehicle.php">
        <div class="form-group">
            <label for="model">Vehicle Model / Name *</label>
            <input type="text" id="model" name="model" placeholder="e.g. Honda City ZX, Hyundai Creta" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="manufacturing_yr">Manufacturing Year *</label>
                <input type="number" id="manufacturing_yr" name="manufacturing_yr" min="1990" max="<?php echo date('Y') + 1; ?>" value="<?php echo date('Y'); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Base Showroom Price (₹) *</label>
                <input type="number" id="price" name="price" step="0.01" min="1" placeholder="e.g. 1500000" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="type">Vehicle Type</label>
                <select id="type" name="type">
                    <option value="Car">Car</option>
                    <option value="Bike">Bike</option>
                    <option value="SUV">SUV</option>
                    <option value="Truck">Truck</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fuel_type">Fuel Type</label>
                <select id="fuel_type" name="fuel_type">
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="CNG">CNG</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="color">Body Color *</label>
            <input type="text" id="color" name="color" placeholder="e.g. Pearl White, Jet Black" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Save Vehicle to Stock</button>
    </form>
</div>

<?php include 'footer.php'; ?>