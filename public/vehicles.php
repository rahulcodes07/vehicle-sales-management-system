<?php
$pageTitle = "Vehicle Inventory";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Handle filter if provided (Preserved exact PHP logic)
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

// Using Prepared Statements for secure filtered search
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Vehicle Fleet & Inventory</h1>
        <p class="subtitle">Complete catalog of showroom inventory, fuel variants, manufacturing specifications, and availability states.</p>
    </div>
    <a href="add_vehicle.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add New Vehicle</span>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<!-- Filter & Search Toolbar -->
<div class="filter-card">
    <form method="GET" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin-bottom:0; color:var(--text-secondary);">Vehicle Category:</label>
            <select name="type" style="width:auto; min-width:140px;">
                <option value="All" <?php echo $filterType == 'All' ? 'selected' : ''; ?>>All Categories</option>
                <option value="Car" <?php echo $filterType == 'Car' ? 'selected' : ''; ?>>Passenger Car</option>
                <option value="Bike" <?php echo $filterType == 'Bike' ? 'selected' : ''; ?>>Motorcycle / Bike</option>
                <option value="SUV" <?php echo $filterType == 'SUV' ? 'selected' : ''; ?>>SUV / Crossover</option>
                <option value="Truck" <?php echo $filterType == 'Truck' ? 'selected' : ''; ?>>Commercial Truck</option>
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin-bottom:0; color:var(--text-secondary);">Stock Status:</label>
            <select name="status" style="width:auto; min-width:140px;">
                <option value="All" <?php echo $filterStatus == 'All' ? 'selected' : ''; ?>>All Statuses</option>
                <option value="Available" <?php echo $filterStatus == 'Available' ? 'selected' : ''; ?>>Available Only</option>
                <option value="Sold" <?php echo $filterStatus == 'Sold' ? 'selected' : ''; ?>>Sold Units</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; margin-left:auto;">
            <button type="submit" class="btn btn-sm btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span>Filter Fleet</span>
            </button>
            <a href="vehicles.php" class="btn btn-sm">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                <span>Reset</span>
            </a>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Vehicle Code</th>
                <th>Model Specifications</th>
                <th>Year</th>
                <th>Category</th>
                <th>Powertrain</th>
                <th>Finish / Color</th>
                <th>Base Ex-Showroom</th>
                <th>Availability</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $isAvail = ($row['status'] === 'Available');
                ?>
                    <tr>
                        <td>
                            <span class="code-id">VH-<?php echo sprintf('%03d', (int)$row['vehicle_id']); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['model']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($row['manufacturing_yr']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($row['type']); ?></span>
                        </td>
                        <td>
                            <span class="badge badge-cyan"><?php echo htmlspecialchars($row['fuel_type']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['color']); ?></td>
                        <td>
                            <strong class="inr-price"><?php echo money((float)$row['price']); ?></strong>
                        </td>
                        <td>
                            <span class="badge <?php echo $isAvail ? 'status-available' : 'status-sold'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <?php if ($isAvail): ?>
                                <a href="add_sale.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn btn-sm btn-primary">
                                    <span>Sell</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            <?php endif; ?>
                            <form method="POST" action="delete_vehicle.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $row['vehicle_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to remove this vehicle from inventory? Referential integrity will prevent deletion if sales exist.">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <h3>No vehicles found</h3>
                            <p>No vehicles match your active filter criteria. Try resetting filters or adding new vehicles.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>