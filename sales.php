<?php
$pageTitle = "Sales Records";
include 'header.php';

// We can query the SQL View `view_sales_details`
$result = mysqli_query($conn, "SELECT * FROM view_sales_details ORDER BY sale_id DESC");
?>

<div class="page-header">
    <div>
        <h1>Vehicle Sales Records</h1>
        <p class="subtitle">Completed vehicle purchase transactions and invoices.</p>
    </div>
    <a href="add_sale.php" class="btn btn-primary">+ Record New Sale</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS View & Trigger Showcase:</strong> This grid is populated directly from the <code>view_sales_details</code> database view. When you record a sale, MySQL executes an ACID transaction, and our database Trigger (<code>trg_after_sale_insert</code>) automatically updates the vehicle status and records an audit trail in the <code>Sales_Audit_Log</code> table.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Sales Consultant</th>
                <th>Showroom Location</th>
                <th>Sale Date</th>
                <th>Final Price</th>
                <th>Payment Mode</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['sale_id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['vehicle_model']); ?></strong>
                            <div style="font-size:12px; color:#64748b;"><?php echo htmlspecialchars($row['vehicle_type']); ?> &bull; <?php echo htmlspecialchars($row['vehicle_color']); ?></div>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                            <div style="font-size:12px; color:#64748b;"><?php echo htmlspecialchars($row['customer_phone']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['branch_location']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['sale_date'])); ?></td>
                        <td><strong style="color:#0f3460;">₹<?php echo number_format($row['sale_price'], 2); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['payment_mode']); ?></span></td>
                        <td>
                            <a href="delete_sale.php?id=<?php echo $row['sale_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Cancelling this sale will trigger MySQL to restore the vehicle back to Available stock. Proceed?');">
                               Cancel / Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px; color:#64748b;">No sales records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>