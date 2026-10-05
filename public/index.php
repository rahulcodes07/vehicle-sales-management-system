<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Fetch summary metrics using SQL aggregate queries
$v_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle"))['total'] ?? 0;
$v_avail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle WHERE status='Available'"))['total'] ?? 0;
$v_sold  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle WHERE status='Sold'"))['total'] ?? 0;
$c_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Customer"))['total'] ?? 0;
$e_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Employee"))['total'] ?? 0;
$i_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Insurance"))['total'] ?? 0;
$s_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(sale_price), 0) as revenue FROM Sales"));
$sales_count = $s_total['total'] ?? 0;
$total_revenue = $s_total['revenue'] ?? 0;
$inventory_mix = mysqli_query($conn, "SELECT * FROM view_inventory_summary ORDER BY total_count DESC");
$top_rep = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM view_employee_performance ORDER BY total_revenue_generated DESC LIMIT 1"));
?>

<div class="page-header">
    <div>
        <h1>Vehicle Sales Management Dashboard</h1>
        <p class="subtitle">Overview of inventory, customers, dealership staff, and revenue transactions.</p>
    </div>
    <div class="topbar-actions">
        <a href="add_sale.php" class="btn btn-primary">＋ Record New Sale</a>
        <a href="add_vehicle.php" class="btn">＋ Add Vehicle</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Total Vehicles</div>
        <div class="stat-value"><?php echo number_format($v_total); ?></div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Available Stock</div>
        <div class="stat-value"><?php echo number_format($v_avail); ?></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Vehicles Sold</div>
        <div class="stat-value"><?php echo number_format($v_sold); ?></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-title">Registered Customers</div>
        <div class="stat-value"><?php echo number_format($c_total); ?></div>
    </div>
    <div class="stat-card primary">
        <div class="stat-title">Team Members</div>
        <div class="stat-value"><?php echo number_format($e_total); ?></div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Insurance Policies</div>
        <div class="stat-value"><?php echo number_format($i_total); ?></div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Total Revenue</div>
        <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
    </div>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Inventory intelligence</span><h2>Stock by vehicle type</h2></div>
            <a href="vehicles.php" class="text-link">Open inventory →</a>
        </div>
        <?php while ($mix = mysqli_fetch_assoc($inventory_mix)): ?>
            <?php $share = $v_total > 0 ? min(100, ((int) $mix['total_count'] / $v_total) * 100) : 0; ?>
            <div class="metric-row">
                <div class="metric-label"><strong><?php echo htmlspecialchars($mix['type']); ?></strong><span><?php echo (int) $mix['available_count']; ?> available · <?php echo (int) $mix['sold_count']; ?> sold</span></div>
                <div class="metric-track"><span style="width: <?php echo $share; ?>%"></span></div>
            </div>
        <?php endwhile; ?>
    </section>
    <section class="panel highlight-panel">
        <span class="eyebrow">Performance snapshot</span>
        <h2>Top sales representative</h2>
        <?php if ($top_rep): ?>
            <div class="profile-chip"><div class="avatar"><?php echo strtoupper(substr($top_rep['employee_name'], 0, 1)); ?></div><div><strong><?php echo htmlspecialchars($top_rep['employee_name']); ?></strong><span><?php echo htmlspecialchars($top_rep['branch_name']); ?></span></div></div>
            <div class="rep-number"><?php echo number_format((int) $top_rep['total_vehicles_sold']); ?><small> vehicles sold</small></div>
            <p class="muted">Revenue generated: <strong><?php echo money($top_rep['total_revenue_generated']); ?></strong></p>
        <?php else: ?>
            <p class="muted">Performance data will appear after the first transaction.</p>
        <?php endif; ?>
        <a href="reports.php" class="btn btn-sm btn-primary">View analytics</a>
    </section>
</div>

<div class="concept-box">
    <strong>💡 ADBMS Concept Highlight:</strong> The stats above use SQL aggregate functions (<code>COUNT()</code>, <code>SUM()</code>, <code>COALESCE()</code>). The sales table below is queried using our SQL View (<code>view_sales_details</code>), which encapsulates a 4-table join on the database server.
</div>

<h2>Recent Sales Transactions (via <code>view_sales_details</code>)</h2>
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Vehicle Model</th>
                <th>Customer</th>
                <th>Sales Rep</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Payment Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Querying through the SQL View created in database/install.sql
            $recent_sales = mysqli_query($conn, "SELECT * FROM view_sales_details ORDER BY sale_id DESC LIMIT 5");
            if (mysqli_num_rows($recent_sales) > 0):
                while($row = mysqli_fetch_assoc($recent_sales)):
            ?>
                <tr>
                    <td><strong>#<?php echo $row['sale_id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['vehicle_model']); ?> (<?php echo htmlspecialchars($row['vehicle_color']); ?>)</td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['branch_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                    <td><strong>₹<?php echo number_format($row['sale_price'], 2); ?></strong></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row['payment_mode']); ?></span></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr><td colspan="8" style="text-align:center; padding:20px; color:#64748b;">No sales recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>