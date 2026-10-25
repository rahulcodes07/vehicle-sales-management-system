<?php
$pageTitle = "Reports";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$selectedYear = max(2020, min((int) date('Y') + 1, (int) ($_GET['year'] ?? date('Y'))));

// Monthly sales for the chosen year
$monthlyReportQuery = "CALL sp_GetMonthlySalesReport({$selectedYear})";
$monthlyReportResult = mysqli_query($conn, $monthlyReportQuery);

// Clear the procedure's result buffer before running more queries
while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
    $extraResult = mysqli_use_result($conn);
    if ($extraResult instanceof mysqli_result) {
        mysqli_free_result($extraResult);
    }
}

$monthlyRows = [];
if ($monthlyReportResult) {
    while ($m = mysqli_fetch_assoc($monthlyReportResult)) {
        $monthlyRows[] = $m;
    }
}
$maxMonthRevenue = $monthlyRows ? max(array_map(fn ($r) => (float) $r['total_monthly_revenue'], $monthlyRows)) : 0;

// Team results
$empPerformance = mysqli_query($conn, "
    SELECT p.*, GetTotalSalesByEmployee(p.employee_id) AS function_sales_count
    FROM view_employee_performance p
    ORDER BY p.total_revenue_generated DESC
");

// Stock by category
$invSummary = mysqli_query($conn, "SELECT * FROM view_inventory_summary");

// Festive discount estimator
$calcPrice = max(1, (float) ($_GET['calc_price'] ?? 1500000));
$calcYear = max(1990, min((int) date('Y'), (int) ($_GET['calc_year'] ?? 2021)));
$discountStmt = mysqli_prepare($conn, 'SELECT fn_CalculateDiscount(?, ?) AS discount');
mysqli_stmt_bind_param($discountStmt, 'di', $calcPrice, $calcYear);
mysqli_stmt_execute($discountStmt);
$discountResult = mysqli_stmt_get_result($discountStmt);
$discountAmount = mysqli_fetch_assoc($discountResult)['discount'] ?? 0;

// Customers who bought more than once
$repeatBuyers = mysqli_query($conn, "
    SELECT * FROM Customer
    WHERE customer_id IN (
        SELECT customer_id FROM Sales GROUP BY customer_id HAVING COUNT(sale_id) > 1
    )
");

// Sales priced above the overall average
$aboveAvgSales = mysqli_query($conn, "
    SELECT s.sale_id, v.model, s.sale_price, s.sale_date, c.name as customer_name
    FROM Sales s
    INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
    INNER JOIN Customer c ON s.customer_id = c.customer_id
    WHERE s.sale_price > (SELECT AVG(sale_price) FROM Sales)
    ORDER BY s.sale_price DESC
");
$overallAvgPrice = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(sale_price) as avg_price FROM Sales"))['avg_price'] ?? 0;

page_head('Reports', 'Monthly sales, team results and stock levels.');
?>

<section class="section">
    <div class="section-head">
        <h2>Monthly sales</h2>
        <form method="GET" class="field" style="margin:0; display:flex; align-items:center; gap:8px;">
            <label for="year" style="margin:0;">Year</label>
            <select id="year" name="year" data-auto-submit style="width:auto;">
                <?php for ($y = (int) date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $selectedYear === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="num">Vehicles sold</th>
                    <th class="num">Revenue</th>
                    <th>Share of best month</th>
                    <th class="num">Average sale</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($monthlyRows): ?>
                    <?php foreach ($monthlyRows as $m): ?>
                        <?php $barPct = $maxMonthRevenue > 0 ? (int) round(((float) $m['total_monthly_revenue'] / $maxMonthRevenue) * 100) : 0; ?>
                        <tr>
                            <td class="cell-main"><?= e($m['month_name']) ?></td>
                            <td class="num"><?= (int) $m['total_sales_count'] ?></td>
                            <td class="num cell-main"><?= e(money((float) $m['total_monthly_revenue'])) ?></td>
                            <td><span class="bar"><span style="width: <?= $barPct ?>%"></span></span></td>
                            <td class="num"><?= e(money((float) $m['average_sale_price'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= empty_row(5, 'No sales in ' . $selectedYear, 'Pick another year, or record a sale to see it here.') ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Team results</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Consultant</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th class="num">Vehicles sold</th>
                    <th class="num">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($empPerformance && mysqli_num_rows($empPerformance) > 0): ?>
                    <?php while ($ep = mysqli_fetch_assoc($empPerformance)): ?>
                        <tr>
                            <td class="cell-main"><?= e($ep['employee_name']) ?></td>
                            <td><?= e($ep['role']) ?></td>
                            <td><?= e($ep['branch_name']) ?></td>
                            <td class="num"><?= (int) $ep['function_sales_count'] ?></td>
                            <td class="num cell-main"><?= e(money((float) $ep['total_revenue_generated'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?= empty_row(5, 'No team members yet', 'Add team members to see their results here.') ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="split split-even">
    <section>
        <div class="section-head"><h2>Stock by category</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="num">In total</th>
                        <th class="num">Available</th>
                        <th class="num">Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($invSummary) > 0): ?>
                        <?php while ($inv = mysqli_fetch_assoc($invSummary)): ?>
                            <tr>
                                <td class="cell-main"><?= e($inv['type']) ?></td>
                                <td class="num"><?= (int) $inv['total_count'] ?></td>
                                <td class="num"><?= (int) $inv['available_count'] ?></td>
                                <td class="num"><?= (int) $inv['sold_count'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?= empty_row(4, 'No vehicles yet', 'Add vehicles to see stock levels here.') ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="section-head"><h2>Festive discount estimator</h2></div>
        <div class="panel">
            <div class="panel-body">
                <p class="hint" style="margin:0 0 16px;">Vehicles three years old or more get 5% off. Newer vehicles get 2% off.</p>
                <form method="GET" action="reports.php">
                    <div class="field-grid">
                        <div class="field">
                            <label for="calc_price">Vehicle price (₹)</label>
                            <input type="number" id="calc_price" name="calc_price" value="<?= e($calcPrice) ?>" required>
                        </div>
                        <div class="field">
                            <label for="calc_year">Model year</label>
                            <input type="number" id="calc_year" name="calc_year" value="<?= e($calcYear) ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Calculate discount</button>
                </form>
                <div class="result" style="margin-top:16px;">
                    Discount you can offer
                    <strong><?= e(money((float) $discountAmount)) ?></strong>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="split split-even">
    <section>
        <div class="section-head"><h2>Returning customers</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($repeatBuyers && mysqli_num_rows($repeatBuyers) > 0): ?>
                        <?php while ($rb = mysqli_fetch_assoc($repeatBuyers)): ?>
                            <tr>
                                <td>
                                    <div class="cell-main"><?= e($rb['name']) ?></div>
                                    <div class="cell-sub"><?= fmt_code('CUST', (int) $rb['customer_id']) ?></div>
                                </td>
                                <td><?= e($rb['phone']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?= empty_row(2, 'No returning customers yet', 'Customers who buy more than one vehicle will be listed here.') ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="section-head">
            <h2>Sales above average</h2>
            <p>Average sale: <?= e(money((float) $overallAvgPrice)) ?></p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Sale</th>
                        <th>Vehicle</th>
                        <th class="num">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($aboveAvgSales && mysqli_num_rows($aboveAvgSales) > 0): ?>
                        <?php while ($aas = mysqli_fetch_assoc($aboveAvgSales)): ?>
                            <tr>
                                <td class="id"><?= fmt_code('SALE', (int) $aas['sale_id']) ?></td>
                                <td>
                                    <div class="cell-main"><?= e($aas['model']) ?></div>
                                    <div class="cell-sub"><?= e($aas['customer_name']) ?></div>
                                </td>
                                <td class="num cell-main"><?= e(money((float) $aas['sale_price'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?= empty_row(3, 'Nothing above average', 'Sales priced above the average will be listed here.') ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>