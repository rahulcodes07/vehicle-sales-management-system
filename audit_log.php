<?php
$pageTitle = "Trigger Audit Logs";
include 'header.php';

$query = "SELECT * FROM Sales_Audit_Log ORDER BY log_id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="page-header">
    <div>
        <h1>Database Trigger Audit Logs</h1>
        <p class="subtitle">Real-time audit trail maintained automatically by MySQL triggers (<code>trg_after_sale_insert</code> &amp; <code>trg_after_sale_delete</code>).</p>
    </div>
</div>

<div class="concept-box">
    <strong>💡 How Triggers Work in ADBMS:</strong> These log records are <em>NEVER</em> written by PHP code. Instead, MySQL Triggers intercept the <code>AFTER INSERT</code> and <code>AFTER DELETE</code> events on the <code>Sales</code> table, update vehicle availability status, and autonomously insert an audit snapshot here.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Sale ID</th>
                <th>Vehicle ID</th>
                <th>Operation Type</th>
                <th>Audit Action Description</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['log_id']; ?></strong></td>
                        <td><?php echo $row['sale_id'] ? '#' . $row['sale_id'] : 'N/A'; ?></td>
                        <td><?php echo $row['vehicle_id'] ? '#' . $row['vehicle_id'] : 'N/A'; ?></td>
                        <td>
                            <span class="badge <?php echo $row['action_type'] == 'INSERT' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($row['action_type']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['action_description']); ?></td>
                        <td><?php echo date('M d, Y h:i:s A', strtotime($row['action_timestamp'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#64748b;">No trigger logs found. Try recording or deleting a sale to see triggers fire automatically!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
