<?php
$pageTitle = "Sales Records";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// We can query the SQL View `view_sales_details` (Preserved exact PHP logic)
$result = mysqli_query($conn, "SELECT * FROM view_sales_details ORDER BY sale_id DESC");
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Sales Transactions Ledger</h1>
        <p class="subtitle">Complete audit of completed vehicle purchase contracts, financing modes, assigned consultants, and showroom locations.</p>
    </div>
    <a href="add_sale.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Record New Sale</span>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS View & Trigger Showcase (Proposal Phase 3 & 4):</strong> This ledger is populated directly from the <code>Sales_Summary</code> / <code>view_sales_details</code> database view. When a sale transaction is committed, MySQL executes the transaction under row-locking isolation, and the database Trigger (<code>trg_after_sale_insert</code>) automatically sets the vehicle to <code>Sold</code> and logs an immutable audit trail into <code>Sales_Audit_Log</code>.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Purchased Vehicle</th>
                <th>Buyer / Client</th>
                <th>Sales Consultant</th>
                <th>Showroom Location</th>
                <th>Contract Date</th>
                <th>Final Agreed Price</th>
                <th>Payment Mode</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $payMode = htmlspecialchars($row['payment_mode']);
                    $badgeClass = match($payMode) {
                        'Cash' => 'badge-success',
                        'Loan' => 'badge-purple',
                        'UPI'  => 'badge-cyan',
                        'Card' => 'badge-info',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td>
                            <span class="code-id">SALE-<?php echo sprintf('%03d', (int)$row['sale_id']); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['vehicle_model']); ?></strong>
                            <div style="font-size:11.5px; color:var(--text-muted);">
                                <?php echo htmlspecialchars($row['vehicle_type']); ?> &bull; <?php echo htmlspecialchars($row['vehicle_color']); ?>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                            <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['customer_phone']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['branch_location']); ?></td>
                        <td><?php echo date_label($row['sale_date']); ?></td>
                        <td>
                            <strong class="inr-price">₹<?php echo number_format((float)$row['sale_price'], 2); ?></strong>
                        </td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $payMode; ?></span>
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" action="delete_sale.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $row['sale_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Cancelling this transaction will trigger the MySQL engine trigger (trg_after_sale_delete) to restore the vehicle back to Available status. Proceed?">
                                    Cancel Sale
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
                            <h3>No sales records found</h3>
                            <p>No vehicle sales have been executed yet. Use "+ Record New Sale" to process transactions.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>