<?php
$pageTitle = "Vehicle Inventory";
include 'header.php';

// Handle filter if provided
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
    <div>
        <h1>Vehicle Inventory Management</h1>
        <p class="subtitle">Real-time stock of cars, bikes, SUVs, and commercial vehicles.</p>
    </div>
    <a href="add_vehicle.php" class="btn btn-primary">+ Add New Vehicle</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<!-- Filter Bar -->
<div style="background:white; padding:15px 20px; border-radius:10px; margin-bottom:20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
        <label style="margin-bottom:0;">Filter by Type:</label>
        <select name="type" style="width:auto; margin-bottom:0;">
            <option value="All" <?php echo $filterType == 'All' ? 'selected' : ''; ?>>All Types</option>
            <option value="Car" <?php echo $filterType == 'Car' ? 'selected' : ''; ?>>Car</option>
            <option value="Bike" <?php echo $filterType == 'Bike' ? 'selected' : ''; ?>>Bike</option>
            <option value="SUV" <?php echo $filterType == 'SUV' ? 'selected' : ''; ?>>SUV</option>
            <option value="Truck" <?php echo $filterType == 'Truck' ? 'selected' : ''; ?>>Truck</option>
        </select>

        <label style="margin-bottom:0;">Status:</label>
        <select name="status" style="width:auto; margin-bottom:0;">
            <option value="All" <?php echo $filterStatus == 'All' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="Available" <?php echo $filterStatus == 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="Sold" <?php echo $filterStatus == 'Sold' ? 'selected' : ''; ?>>Sold</option>
        </select>

        <button type="submit" class="btn btn-sm">Apply Filter</button>
        <a href="vehicles.php" class="btn btn-sm" style="background:#64748b;">Reset</a>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Model</th>
                <th>Year</th>
                <th>Type</th>
                <th>Fuel</th>
                <th>Color</th>
                <th>Base Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['vehicle_id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($row['model']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['manufacturing_yr']); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['fuel_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['color']); ?></td>
                        <td>₹<?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $row['status'] == 'Available' ? 'status-available' : 'status-sold'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'Available'): ?>
                                <a href="add_sale.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn btn-sm btn-primary">Sell</a>
                            <?php endif; ?>
                            <a href="delete_vehicle.php?id=<?php echo $row['vehicle_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this vehicle record?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px; color:#64748b;">No vehicles found matching criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>