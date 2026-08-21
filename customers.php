<?php
$pageTitle = "Customers";
include 'header.php';

// Search support with Prepared Statement
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Customer WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY customer_id DESC");
    $searchTerm = "%{$search}%";
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM Customer ORDER BY customer_id DESC");
}
?>

<div class="page-header">
    <div>
        <h1>Customer Directory</h1>
        <p class="subtitle">Registered buyers, prospects, and vehicle owners.</p>
    </div>
    <a href="add_customer.php" class="btn btn-primary">+ Register New Customer</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<!-- Search Bar -->
<div style="background:white; padding:15px 20px; border-radius:10px; margin-bottom:20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" placeholder="Search by customer name, phone number, or email..." value="<?php echo htmlspecialchars($search); ?>" style="margin-bottom:0;">
        <button type="submit" class="btn btn-sm">Search</button>
        <?php if (!empty($search)): ?>
            <a href="customers.php" class="btn btn-sm" style="background:#64748b;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Phone Number</th>
                <th>Email Address</th>
                <th>Residential Address</th>
                <th>Registered On</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['customer_id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '-'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#64748b;">No customers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>