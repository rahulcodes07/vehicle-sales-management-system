<?php
$pageTitle = "Vehicles";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$filterType = $_GET['type'] ?? 'All';
$filterStatus = $_GET['status'] ?? 'All';

$query = "SELECT * FROM Vehicle WHERE 1=1";
$params = [];
$types = "";

if ($filterType !== 'All') {
    $query .= " AND type = ?";
    $params[] = $filterType;
    $types .= "s";
}
if ($filterStatus !== 'All') {
    $query .= " AND status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}
$query .= " ORDER BY vehicle_id DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicleCount = mysqli_num_rows($result);

page_head(
    'Vehicles',
    'Your full stock, with price and whether each vehicle is still available.',
    '<a href="add_vehicle.php" class="btn btn-primary">' . icon('plus', 16) . 'Add vehicle</a>'
);

if (isset($_GET['msg'])) {
    echo alert_box('success', (string) $_GET['msg']);
}
if (isset($_GET['err'])) {
    echo alert_box('danger', (string) $_GET['err']);
}
?>

<form method="GET" class="toolbar">
    <div class="field">
        <label for="type">Category</label>
        <select id="type" name="type" data-auto-submit>
            <?php foreach (['All' => 'All categories', 'Car' => 'Car', 'Bike' => 'Bike', 'SUV' => 'SUV', 'Truck' => 'Truck'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= selected($filterType, $value) ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="status">Availability</label>
        <select id="status" name="status" data-auto-submit>
            <?php foreach (['All' => 'All vehicles', 'Available' => 'Available', 'Sold' => 'Sold'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= selected($filterStatus, $value) ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="toolbar-end">
        <span><?= number_format($vehicleCount) ?> <?= $vehicleCount === 1 ? 'vehicle' : 'vehicles' ?></span>
        <noscript><button type="submit" class="btn btn-sm btn-primary">Apply</button></noscript>
        <a href="vehicles.php" class="btn btn-sm">Reset</a>
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Category</th>
                <th>Fuel</th>
                <th class="num">Price</th>
                <th>Availability</th>
                <th class="actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($vehicleCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php $isAvail = ($row['status'] === 'Available'); ?>
                    <tr>
                        <td class="id"><?= fmt_code('VH', (int) $row['vehicle_id']) ?></td>
                        <td>
                            <div class="cell-main"><?= e($row['model']) ?></div>
                            <div class="cell-sub"><?= e($row['manufacturing_yr']) ?>, <?= e($row['color']) ?></div>
                        </td>
                        <td><?= tag((string) $row['type']) ?></td>
                        <td><?= tag((string) $row['fuel_type']) ?></td>
                        <td class="num cell-main"><?= e(money((float) $row['price'])) ?></td>
                        <td><?= tag((string) $row['status'], $isAvail ? 'ok' : '', true) ?></td>
                        <td class="actions">
                            <?php if ($isAvail): ?>
                                <a href="add_sale.php?vehicle_id=<?= (int) $row['vehicle_id'] ?>" class="btn btn-sm btn-primary">Sell</a>
                            <?php endif; ?>
                            <form method="POST" action="delete_vehicle.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $row['vehicle_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Remove this vehicle from stock? A vehicle that already has a sale can't be removed.">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(7, 'No vehicles found', 'Nothing matches these filters. Reset them or add a new vehicle.', '<a href="add_vehicle.php" class="btn btn-primary btn-sm">Add vehicle</a>') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>