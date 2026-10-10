<?php
$pageTitle = "Customers";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Search support with Prepared Statement (Preserved exact PHP logic)
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
    <div class="page-header-content">
        <h1>Client & Customer Directory</h1>
        <p class="subtitle">Directory of registered vehicle buyers, verified phone contacts, and delivery addresses.</p>
    </div>
    <a href="add_customer.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Register New Customer</span>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<!-- Search Toolbar -->
<div class="filter-card">
    <form method="GET" style="display:flex; gap:12px; align-items:center;">
        <div style="flex:1; position:relative;">
            <input type="text" name="search" placeholder="Search by client name, mobile phone number, or email address..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span>Search</span>
        </button>
        <?php if (!empty($search)): ?>
            <a href="customers.php" class="btn">
                <span>Clear</span>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Customer Code</th>
                <th>Client Name</th>
                <th>Verified Phone</th>
                <th>Email Address</th>
                <th>Residential / Billing Address</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $initial = strtoupper(substr($row['name'], 0, 1));
                ?>
                    <tr>
                        <td>
                            <span class="code-id">CUST-<?php echo sprintf('%03d', (int)$row['customer_id']); ?></span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="avatar" style="width:32px; height:32px; font-size:13px;"><?php echo $initial; ?></div>
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($row['phone']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo isset($row['created_at']) ? date_label($row['created_at']) : '—'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <h3>No customers found</h3>
                            <p><?php echo !empty($search) ? 'No results matched your search query. Try clearing the search.' : 'No customers are registered in the system yet.'; ?></p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>