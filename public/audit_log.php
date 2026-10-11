<?php
$pageTitle = "Trigger Audit Logs";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Preserved exact query logic
$query = "SELECT * FROM Sales_Audit_Log ORDER BY log_id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Autonomous Database Trigger Audit Trail</h1>
        <p class="subtitle">Immutable transaction history maintained exclusively by MySQL database triggers (<code>trg_after_sale_insert</code> &amp; <code>trg_after_sale_delete</code>).</p>
    </div>
</div>


<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Audit ID</th>
                <th>Target Sale</th>
                <th>Vehicle Affected</th>
                <th>Event Type</th>
                <th>Autonomous Action Description</th>
                <th>Engine Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $actionType = htmlspecialchars($row['action_type']);
                    $badgeClass = ($actionType === 'INSERT') ? 'badge-success' : 'badge-danger';
                ?>
                    <tr>
                        <td>
                            <span class="code-id">LOG-<?php echo sprintf('%03d', (int)$row['log_id']); ?></span>
                        </td>
                        <td>
                            <?php if ($row['sale_id']): ?>
                                <span class="code-id">SALE-<?php echo sprintf('%03d', (int)$row['sale_id']); ?></span>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['vehicle_id']): ?>
                                <span class="code-id">VH-<?php echo sprintf('%03d', (int)$row['vehicle_id']); ?></span>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo $actionType; ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['action_description']); ?></strong>
                        </td>
                        <td>
                            <span style="font-family:var(--font-mono); font-size:12px; color:var(--text-secondary);">
                                <?php echo date('M d, Y &bull; h:i:s A', strtotime($row['action_timestamp'])); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <h3>No audit events logged yet</h3>
                            <p>Try recording or cancelling a vehicle sale. The MySQL triggers will fire autonomously and log audit events here.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>
