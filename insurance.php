<?php
$pageTitle = "Insurance Policies";
include 'header.php';

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
    <div>
        <h1>Vehicle Insurance Policies</h1>
        <p class="subtitle">Active policies, coverage providers, and premium records.</p>
    </div>
    <a href="add_insurance.php" class="btn btn-primary">+ Issue New Policy</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS Constraint Concept:</strong> The <code>Insurance</code> table enforces foreign keys to both <code>Vehicle</code> and <code>Customer</code>, and a Table Check Constraint <code>CHECK (end_date > start_date)</code> to ensure valid policy duration dates.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Policy Number</th>
                <th>Insurance Provider</th>
                <th>Vehicle Model</th>
                <th>Insured Customer</th>
                <th>Policy Start</th>
                <th>Policy Expiry</th>
                <th>Annual Premium</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['insurance_id']; ?></strong></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['policy_no']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($row['provider']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['vehicle_model']); ?> (<?php echo htmlspecialchars($row['vehicle_color']); ?>)</td>
                        <td>
                            <?php echo htmlspecialchars($row['customer_name']); ?>
                            <div style="font-size:12px; color:#64748b;"><?php echo htmlspecialchars($row['customer_phone']); ?></div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['end_date'])); ?></td>
                        <td><strong>₹<?php echo number_format($row['premium'], 2); ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#64748b;">No insurance policies recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>