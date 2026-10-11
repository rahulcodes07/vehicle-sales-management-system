<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Fetch summary metrics using SQL aggregate queries (Preserved intact)
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
    <div class="page-header-content">
        <h1>Executive Dealership Dashboard</h1>
        <p class="subtitle">Real-time overview of vehicle inventory, sales transactions, staff performance, and revenue analytics.</p>
    </div>
    <div class="topbar-actions">
        <a href="add_sale.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Record New Sale</span>
        </a>
        <a href="add_vehicle.php" class="btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"></path>
                <circle cx="6.5" cy="16.5" r="2.5"></circle>
                <circle cx="16.5" cy="16.5" r="2.5"></circle>
            </svg>
            <span>Add Vehicle</span>
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-header">
            <span class="stat-title">Total Fleet</span>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($v_total); ?></div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">Available Stock</span>
            <div class="stat-icon" style="color:var(--emerald-solid);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($v_avail); ?></div>
    </div>

    <div class="stat-card danger">
        <div class="stat-header">
            <span class="stat-title">Vehicles Sold</span>
            <div class="stat-icon" style="color:var(--rose-solid);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($v_sold); ?></div>
    </div>

    <div class="stat-card purple">
        <div class="stat-header">
            <span class="stat-title">Clients Registered</span>
            <div class="stat-icon" style="color:var(--purple-solid);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($c_total); ?></div>
    </div>

    <div class="stat-card cyan">
        <div class="stat-header">
            <span class="stat-title">Dealership Team</span>
            <div class="stat-icon" style="color:var(--cyan-solid);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($e_total); ?></div>
    </div>

    <div class="stat-card warning">
        <div class="stat-header">
            <span class="stat-title">Gross Revenue</span>
            <div class="stat-icon" style="color:var(--amber-solid);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
        </div>
        <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
    </div>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <span class="eyebrow">Inventory Intelligence</span>
                <h2>Stock Distribution by Vehicle Type</h2>
            </div>
            <a href="vehicles.php" class="text-link">
                <span>View Full Inventory</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
        <?php while ($mix = mysqli_fetch_assoc($inventory_mix)): ?>
            <?php $share = $v_total > 0 ? min(100, ((int) $mix['total_count'] / $v_total) * 100) : 0; ?>
            <div class="metric-row">
                <div class="metric-label">
                    <strong><?php echo htmlspecialchars($mix['type']); ?></strong>
                    <span><?php echo (int) $mix['available_count']; ?> Available &bull; <?php echo (int) $mix['sold_count']; ?> Sold</span>
                </div>
                <div class="metric-track">
                    <span style="width: <?php echo $share; ?>%"></span>
                </div>
            </div>
        <?php endwhile; ?>
    </section>

    <section class="panel highlight-panel">
        <span class="eyebrow">Top Sales Performer</span>
        <h2>Showroom Leaderboard</h2>
        <?php if ($top_rep): ?>
            <div class="profile-chip">
                <div class="avatar"><?php echo strtoupper(substr($top_rep['employee_name'], 0, 1)); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($top_rep['employee_name']); ?></strong>
                    <span><?php echo htmlspecialchars($top_rep['branch_name']); ?> &bull; <?php echo htmlspecialchars($top_rep['role']); ?></span>
                </div>
            </div>
            <div class="rep-number">
                <?php echo number_format((int) $top_rep['total_vehicles_sold']); ?>
                <small>vehicles sold</small>
            </div>
            <p class="muted">
                Cumulative Revenue: <strong class="inr-price"><?php echo money($top_rep['total_revenue_generated']); ?></strong>
            </p>
        <?php else: ?>
            <div class="empty-state" style="padding: 20px 0;">
                <p class="muted">Performance data will appear after sales transactions are recorded.</p>
            </div>
        <?php endif; ?>
        <a href="reports.php" class="btn btn-sm btn-primary" style="margin-top:auto; width:fit-content;">
            <span>Explore Analytics</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </section>
</div>

<div class="concept-box">
    <strong>💡 ADBMS Concept Highlight (Proposal Phase 3):</strong> The aggregate metrics above use SQL aggregate functions (<code>COUNT()</code>, <code>SUM()</code>, <code>COALESCE()</code>). The sales table below is queried dynamically through our database view (<code>view_sales_details</code> / <code>Sales_Summary</code>), encapsulating a 4-table relational join directly on the MySQL server.
</div>

<div class="page-header" style="margin-bottom: 16px;">
    <h2>Recent Sales Transactions (via <code>Sales_Summary</code> View)</h2>
    <a href="sales.php" class="text-link">View All Sales &rarr;</a>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Vehicle Model</th>
                <th>Customer</th>
                <th>Sales Consultant</th>
                <th>Branch Location</th>
                <th>Sale Date</th>
                <th>Final Amount</th>
                <th>Payment Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $recent_sales = mysqli_query($conn, "SELECT * FROM Sales_Summary ORDER BY sale_id DESC LIMIT 5");
            if (mysqli_num_rows($recent_sales) > 0):
                while($row = mysqli_fetch_assoc($recent_sales)):
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
                        <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['vehicle_color']); ?></div>
                    </td>
                    <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['branch_location']); ?></td>
                    <td><?php echo date_label($row['sale_date']); ?></td>
                    <td><strong class="inr-price">₹<?php echo number_format((float)$row['sale_price'], 2); ?></strong></td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $payMode; ?></span></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <h3>No transactions recorded yet</h3>
                            <p>Click "+ Record New Sale" to process your first vehicle purchase transaction.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>