<?php
$pageTitle = "Overview";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Summary figures
$v_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle"))['total'] ?? 0;
$v_avail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle WHERE status='Available'"))['total'] ?? 0;
$v_sold  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Vehicle WHERE status='Sold'"))['total'] ?? 0;
$c_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Customer"))['total'] ?? 0;
$e_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Employee"))['total'] ?? 0;
$i_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Insurance"))['total'] ?? 0;
$s_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(sale_price), 0) as revenue FROM Sales"));
$sales_count = (int) ($s_total['total'] ?? 0);
$total_revenue = $s_total['revenue'] ?? 0;
$inventory_mix = mysqli_query($conn, "SELECT * FROM view_inventory_summary ORDER BY total_count DESC");
$top_rep = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM view_employee_performance ORDER BY total_revenue_generated DESC LIMIT 1"));
$recent_sales = mysqli_query($conn, "SELECT * FROM Sales_Summary ORDER BY sale_id DESC LIMIT 5");

$stockPercent = (int) $v_total > 0 ? (int) round(((int) $v_avail / (int) $v_total) * 100) : 0;
$mixCount = mysqli_num_rows($inventory_mix);
$recentCount = mysqli_num_rows($recent_sales);

page_head(
    'Overview',
    'Stock, sales and team results in one place.',
    '<a href="add_vehicle.php" class="btn">' . icon('car', 16) . 'Add vehicle</a>'
    . '<a href="add_sale.php" class="btn btn-primary">' . icon('plus', 16) . 'Record sale</a>'
);
?>

<section class="summary" aria-label="Summary">
    <div class="summary-cell">
        <p class="figure-label">Total revenue</p>
        <p class="figure figure-lg"><?= e(money($total_revenue)) ?></p>
        <p class="figure-note">From <?= number_format($sales_count) ?> <?= $sales_count === 1 ? 'sale' : 'sales' ?></p>
    </div>

    <div class="summary-cell">
        <p class="figure-label">Vehicles in stock</p>
        <p class="figure"><?= number_format((int) $v_avail) ?> <span class="figure-unit">available</span></p>
        <div class="meter" role="img" aria-label="<?= $stockPercent ?> percent of vehicles are available">
            <span class="meter-fill" style="width: <?= $stockPercent ?>%"></span>
        </div>
        <p class="figure-note"><?= number_format((int) $v_sold) ?> sold, <?= number_format((int) $v_total) ?> in total</p>
    </div>

    <dl class="summary-cell kv">
        <div><dt>Customers</dt><dd><?= number_format((int) $c_total) ?></dd></div>
        <div><dt>Team members</dt><dd><?= number_format((int) $e_total) ?></dd></div>
        <div><dt>Insurance policies</dt><dd><?= number_format((int) $i_total) ?></dd></div>
    </dl>
</section>

<div class="split">
    <section class="panel">
        <div class="panel-head">
            <h2>Stock by category</h2>
            <a href="vehicles.php">View all vehicles</a>
        </div>
        <div class="panel-body mix">
            <?php if ($mixCount === 0): ?>
                <p class="figure-note">No vehicles have been added yet.</p>
            <?php endif; ?>
            <?php while ($mix = mysqli_fetch_assoc($inventory_mix)): ?>
                <?php
                $catTotal = max(1, (int) $mix['total_count']);
                $availPct = (int) round(((int) $mix['available_count'] / $catTotal) * 100);
                ?>
                <div class="mix-row">
                    <div class="mix-top">
                        <strong><?= e($mix['type']) ?></strong>
                        <span><?= (int) $mix['available_count'] ?> of <?= (int) $mix['total_count'] ?> available</span>
                    </div>
                    <div class="meter"><span class="meter-fill" style="width: <?= $availPct ?>%"></span></div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Top performer</h2>
            <a href="reports.php">Full report</a>
        </div>
        <div class="panel-body">
            <?php if ($top_rep && (int) $top_rep['total_vehicles_sold'] > 0): ?>
                <div class="leader">
                    <div class="avatar avatar-lg"><?= e(strtoupper(substr((string) $top_rep['employee_name'], 0, 1))) ?></div>
                    <div>
                        <strong><?= e($top_rep['employee_name']) ?></strong>
                        <span><?= e($top_rep['role']) ?>, <?= e($top_rep['branch_name']) ?></span>
                    </div>
                </div>
                <p class="leader-count"><?= number_format((int) $top_rep['total_vehicles_sold']) ?><small>vehicles sold</small></p>
                <p class="figure-note">Revenue brought in: <strong><?= e(money($top_rep['total_revenue_generated'])) ?></strong></p>
            <?php else: ?>
                <p class="figure-note">Results will show here once the first sale is recorded.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="section">
    <div class="section-head">
        <h2>Recent sales</h2>
        <a href="sales.php">View all sales</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Sale</th>
                    <th>Vehicle</th>
                    <th>Customer</th>
                    <th>Sold by</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th class="num">Amount</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentCount > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($recent_sales)): ?>
                        <tr>
                            <td class="id"><?= fmt_code('SALE', (int) $row['sale_id']) ?></td>
                            <td>
                                <div class="cell-main"><?= e($row['vehicle_model']) ?></div>
                                <div class="cell-sub"><?= e($row['vehicle_color']) ?></div>
                            </td>
                            <td><?= e($row['customer_name']) ?></td>
                            <td><?= e($row['employee_name']) ?></td>
                            <td><?= e($row['branch_location']) ?></td>
                            <td><?= e(date_label($row['sale_date'])) ?></td>
                            <td class="num cell-main"><?= e(money($row['sale_price'])) ?></td>
                            <td><?= tag((string) $row['payment_mode']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?= empty_row(8, 'No sales yet', 'Sales you record will appear here.', '<a href="add_sale.php" class="btn btn-primary btn-sm">Record the first sale</a>') ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>