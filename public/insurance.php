<?php
$pageTitle = "Insurance Policies";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Preserved exact query logic
$query = "
    SELECT i.*, v.model as vehicle_model, v.color as vehicle_color, c.name as customer_name, c.phone as customer_phone
    FROM Insurance i
    INNER JOIN Vehicle v ON i.vehicle_id = v.vehicle_id
    INNER JOIN Customer c ON i.customer_id = c.customer_id
    ORDER BY i.insurance_id DESC
";
$result = mysqli_query($conn, $query);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Vehicle Insurance & Coverage</h1>
        <p class="subtitle">Active general insurance policies, policy providers, coverage dates, and annual premium ledgers.</p>
    </div>
    <a href="add_insurance.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Issue New Policy</span>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS Join & Constraint Concept (Proposal Phase 3 Item 1):</strong> This ledger demonstrates an <code>INNER JOIN</code> across <code>Insurance</code>, <code>Vehicle</code>, and <code>Customer</code>, validating referential integrity and enforcing the table check constraint <code>CHECK (end_date &gt; start_date)</code>.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Record ID</th>
                <th>Policy Number</th>
                <th>Insurance Underwriter</th>
                <th>Covered Vehicle</th>
                <th>Policyholder / Client</th>
                <th>Coverage Period</th>
                <th>Annual Premium</th>
                <th>Policy Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $isActive = strtotime($row['end_date']) >= time();
                ?>
                    <tr>
                        <td>
                            <span class="code-id">INS-<?php echo sprintf('%03d', (int)$row['insurance_id']); ?></span>
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($row['policy_no']); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['provider']); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['vehicle_model']); ?></strong>
                            <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['vehicle_color']); ?></div>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                            <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['customer_phone']); ?></div>
                        </td>
                        <td>
                            <div><?php echo date_label($row['start_date']); ?> &rarr; <?php echo date_label($row['end_date']); ?></div>
                        </td>
                        <td>
                            <strong class="inr-price">₹<?php echo number_format((float)$row['premium'], 2); ?></strong>
                        </td>
                        <td>
                            <span class="badge <?php echo $isActive ? 'status-available' : 'status-sold'; ?>">
                                <?php echo $isActive ? 'Active Coverage' : 'Policy Expired'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <h3>No insurance policies found</h3>
                            <p>No vehicle insurance policies have been issued yet. Click "+ Issue New Policy" to add coverage.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>