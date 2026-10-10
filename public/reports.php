<?php
$pageTitle = "ADBMS Analytics & Reports";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$selectedYear = max(2020, min((int) date('Y') + 1, (int) ($_GET['year'] ?? date('Y'))));

// 1. Call Stored Procedure: sp_GetMonthlySalesReport (Proposal Phase 3 Item 6)
$monthlyReportQuery = "CALL sp_GetMonthlySalesReport({$selectedYear})";
$monthlyReportResult = mysqli_query($conn, $monthlyReportQuery);

// Clear stored procedure result buffer for subsequent queries
while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
    $extraResult = mysqli_use_result($conn);
    if($extraResult instanceof mysqli_result) {
        mysqli_free_result($extraResult);
    }
}

// 2. Query Database View: view_employee_performance (Proposal Phase 3 Item 1 & 3)
$empPerformance = mysqli_query($conn, "SELECT * FROM view_employee_performance ORDER BY total_revenue_generated DESC");

// 3. Query Database View: view_inventory_summary (Proposal Phase 3 Item 3)
$invSummary = mysqli_query($conn, "SELECT * FROM view_inventory_summary");

// 4. Stored Function Demonstration: fn_CalculateDiscount
$calcPrice = max(1, (float) ($_GET['calc_price'] ?? 1500000));
$calcYear = max(1990, min((int) date('Y'), (int) ($_GET['calc_year'] ?? 2021)));
$discountStmt = mysqli_prepare($conn, 'SELECT fn_CalculateDiscount(?, ?) AS discount');
mysqli_stmt_bind_param($discountStmt, 'di', $calcPrice, $calcYear);
mysqli_stmt_execute($discountStmt);
$discountResult = mysqli_stmt_get_result($discountStmt);
$discountAmount = mysqli_fetch_assoc($discountResult)['discount'] ?? 0;

// =========================================================================
// PROPOSAL PHASE 3 SPECIFIC VERIFICATIONS (Matches ADBMS.pdf Page 4)
// =========================================================================

// Proposal Phase 3 Item 2A: Nested Subquery for Repeat Buyers
$repeatBuyers = mysqli_query($conn, "
    SELECT * FROM Customer 
    WHERE customer_id IN (
        SELECT customer_id FROM Sales GROUP BY customer_id HAVING COUNT(sale_id) > 1
    )
");

// Proposal Phase 3 Item 2B: Nested Subquery for Sales Above Overall Average
$aboveAvgQuery = "
    SELECT s.sale_id, v.model, s.sale_price, s.sale_date, c.name as customer_name
    FROM Sales s
    INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
    INNER JOIN Customer c ON s.customer_id = c.customer_id
    WHERE s.sale_price > (SELECT AVG(sale_price) FROM Sales)
    ORDER BY s.sale_price DESC
";
$aboveAvgSales = mysqli_query($conn, $aboveAvgQuery);
$overallAvgPrice = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(sale_price) as avg_price FROM Sales"))['avg_price'] ?? 0;

// Proposal Phase 3 Item 4: Live EXPLAIN Query Plan for Multi-table Join
$explainQuery = "EXPLAIN SELECT s.sale_id, c.name, v.model, s.sale_price FROM Sales s INNER JOIN Customer c ON s.customer_id = c.customer_id INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id";
$explainResult = mysqli_query($conn, $explainQuery);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>ADBMS Analytics &amp; Query Optimization</h1>
        <p class="subtitle">Live demonstration of Phase 3 deliverables: Stored Procedures, Multi-table Joins, Nested Subqueries, Stored Functions, and EXPLAIN Query Optimizer Plans.</p>
    </div>
</div>

<!-- =========================================================================
     PROPOSAL DELIVERABLE 1: NESTED SUBQUERIES (Proposal Page 4 Item 2)
     ========================================================================= -->
<div class="page-header" style="margin-top:10px;">
    <h2>1. Nested Queries &amp; Subqueries (Proposal Page 4, Item 2)</h2>
</div>

<div class="concept-box">
    <strong>💡 Subquery A &amp; B Showcase:</strong>
    As specified in the proposal, Subquery 1 finds <strong>repeat buyers</strong> (customers with <code>COUNT &gt; 1</code> in Sales). Subquery 2 dynamically filters transactions whose sale price is <strong>above the overall average sale price</strong> (<code>AVG = ₹<?php echo number_format((float)$overallAvgPrice, 2); ?></code>).
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:30px;">
    <!-- Subquery A: Repeat Buyers -->
    <div class="panel">
        <span class="eyebrow">Subquery A (Repeat Buyers)</span>
        <h3 style="font-size:15px; margin:6px 0 14px; font-weight:700;">Customers with &gt; 1 Vehicle Purchase</h3>
        <code style="display:block; margin-bottom:14px; font-size:11px; white-space:pre-wrap;">WHERE customer_id IN (SELECT customer_id FROM Sales GROUP BY customer_id HAVING COUNT(*) &gt; 1)</code>
        <div class="table-responsive" style="margin-bottom:0; box-shadow:none; border:1px solid var(--border);">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client Name</th>
                        <th>Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($repeatBuyers && mysqli_num_rows($repeatBuyers) > 0): ?>
                        <?php while($rb = mysqli_fetch_assoc($repeatBuyers)): ?>
                            <tr>
                                <td><span class="code-id">CUST-<?php echo sprintf('%03d', (int)$rb['customer_id']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($rb['name']); ?></strong></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($rb['phone']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:15px; color:var(--text-muted);">No repeat buyers found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Subquery B: Above Average Sales -->
    <div class="panel">
        <span class="eyebrow">Subquery B (Above Average Sales)</span>
        <h3 style="font-size:15px; margin:6px 0 14px; font-weight:700;">Sales Above Overall Average (₹<?php echo number_format((float)$overallAvgPrice, 2); ?>)</h3>
        <code style="display:block; margin-bottom:14px; font-size:11px; white-space:pre-wrap;">WHERE sale_price &gt; (SELECT AVG(sale_price) FROM Sales)</code>
        <div class="table-responsive" style="margin-bottom:0; box-shadow:none; border:1px solid var(--border);">
            <table>
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Vehicle</th>
                        <th>Agreed Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($aboveAvgSales && mysqli_num_rows($aboveAvgSales) > 0): ?>
                        <?php while($aas = mysqli_fetch_assoc($aboveAvgSales)): ?>
                            <tr>
                                <td><span class="code-id">SALE-<?php echo sprintf('%03d', (int)$aas['sale_id']); ?></span></td>
                                <td><?php echo htmlspecialchars($aas['model']); ?></td>
                                <td><strong class="inr-price">₹<?php echo number_format((float)$aas['sale_price'], 2); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:15px; color:var(--text-muted);">No sales above average.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     PROPOSAL DELIVERABLE 2: QUERY PLAN & OPTIMIZATION (Proposal Page 4 Item 4)
     ========================================================================= -->
<div class="page-header">
    <h2>2. Query Execution Plan Optimizer: <code>EXPLAIN</code> (Proposal Page 4, Item 4)</h2>
</div>

<div class="concept-box">
    <strong>💡 Query Optimization Analysis:</strong>
    Below is the live execution plan generated by MySQL's cost-based query optimizer for our 3-table join (<code>Customer &bull; Sales &bull; Vehicle</code>). Notice the <code>type</code> column shows <code>eq_ref</code> / <code>ref</code> and the <code>key</code> column utilizes <code>PRIMARY</code> and foreign key indexes, reducing scanned rows.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Query Step</th>
                <th>Target Table</th>
                <th>Access Type</th>
                <th>Candidate Keys</th>
                <th>Index Used (`key`)</th>
                <th>Key Length</th>
                <th>Estimated Rows</th>
                <th>Filter %</th>
            </tr>
        </thead>
        <tbody>
            <?php if($explainResult && mysqli_num_rows($explainResult) > 0): ?>
                <?php while($exp = mysqli_fetch_assoc($explainResult)): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($exp['id'] ?? '1'); ?></strong> (<?php echo htmlspecialchars($exp['select_type'] ?? 'SIMPLE'); ?>)</td>
                        <td><span class="code-id"><?php echo htmlspecialchars($exp['table'] ?? ''); ?></span></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($exp['type'] ?? 'ALL'); ?></span></td>
                        <td><?php echo htmlspecialchars($exp['possible_keys'] ?: 'None'); ?></td>
                        <td><span class="badge badge-success"><?php echo htmlspecialchars($exp['key'] ?: 'TABLE SCAN'); ?></span></td>
                        <td><?php echo htmlspecialchars($exp['key_len'] ?: '—'); ?></td>
                        <td><strong><?php echo htmlspecialchars($exp['rows'] ?? '0'); ?> rows</strong></td>
                        <td><?php echo htmlspecialchars($exp['filtered'] ?? '100'); ?>%</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center; padding:15px;">Unable to fetch EXPLAIN plan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =========================================================================
     PROPOSAL DELIVERABLE 3: STORED PROCEDURE (Proposal Page 4 Item 6)
     ========================================================================= -->
<div class="page-header" style="margin-top:20px;">
    <h2>3. Stored Procedure: <code>sp_GetMonthlySalesReport(year)</code></h2>
    <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <label style="margin-bottom:0;">Filter Report Year:</label>
        <select name="year" onchange="this.form.submit()" style="width:auto; margin-bottom:0;">
            <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $selectedYear == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="concept-box">
    <strong>💡 Why Stored Procedures?</strong> A Stored Procedure is compiled and stored on the MySQL database server. Instead of sending complex multi-line SQL over the network each time, the web application simply sends <code>CALL sp_GetMonthlySalesReport(<?php echo $selectedYear; ?>)</code>. This reduces latency and centralizes analytical logic in the database.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Calendar Month</th>
                <th>Vehicles Sold</th>
                <th>Monthly Sales Revenue</th>
                <th>Average Unit Realization</th>
            </tr>
        </thead>
        <tbody>
            <?php if($monthlyReportResult && mysqli_num_rows($monthlyReportResult) > 0): ?>
                <?php while($m = mysqli_fetch_assoc($monthlyReportResult)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($m['month_name']); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo $m['total_sales_count']; ?> units</span></td>
                        <td><strong class="inr-price">₹<?php echo number_format((float)$m['total_monthly_revenue'], 2); ?></strong></td>
                        <td><span class="inr-price">₹<?php echo number_format((float)$m['average_sale_price'], 2); ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-state" style="padding:24px;">
                            <p class="muted">No sales transactions recorded for year <?php echo $selectedYear; ?>.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =========================================================================
     PROPOSAL DELIVERABLE 4: DATABASE VIEWS & STORED FUNCTIONS
     ========================================================================= -->
<h2>4. Database View: <code>view_employee_performance</code></h2>
<div class="concept-box">
    <strong>💡 Why Database Views?</strong> A View is a stored virtual query. It aggregates performance data across <code>Employee</code>, <code>Branch</code>, and <code>Sales</code> without exposing underlying schema details or writing complex <code>GROUP BY</code> queries in PHP.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Sales Consultant</th>
                <th>Designation</th>
                <th>Showroom Branch</th>
                <th>Units Sold</th>
                <th>Total Revenue Generated</th>
            </tr>
        </thead>
        <tbody>
            <?php if($empPerformance && mysqli_num_rows($empPerformance) > 0): ?>
                <?php while($ep = mysqli_fetch_assoc($empPerformance)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ep['employee_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ep['role']); ?></td>
                        <td><?php echo htmlspecialchars($ep['branch_name']); ?></td>
                        <td><span class="badge badge-success"><?php echo $ep['total_vehicles_sold']; ?> units</span></td>
                        <td><strong class="inr-price">₹<?php echo number_format((float)$ep['total_revenue_generated'], 2); ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:20px;">No employee data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
    <div>
        <h2>5. View: <code>view_inventory_summary</code></h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total Fleet</th>
                        <th>Available</th>
                        <th>Sold Units</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inv = mysqli_fetch_assoc($invSummary)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($inv['type']); ?></strong></td>
                            <td><?php echo $inv['total_count']; ?></td>
                            <td><span class="badge status-available"><?php echo $inv['available_count']; ?></span></td>
                            <td><span class="badge status-sold"><?php echo $inv['sold_count']; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2>6. Stored Function: <code>fn_CalculateDiscount</code></h2>
        <div class="panel">
            <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">
                Executes MySQL server-side deterministic function to calculate festival discount based on vehicle age (&ge; 3 years gets 5%, &lt; 3 years gets 2%).
            </p>
            <form method="GET" action="reports.php">
                <div class="form-group">
                    <label>Vehicle Ex-Showroom Price (₹):</label>
                    <input type="number" name="calc_price" value="<?php echo $calcPrice; ?>" required>
                </div>
                <div class="form-group">
                    <label>Manufacturing Model Year:</label>
                    <input type="number" name="calc_year" value="<?php echo $calcYear; ?>" required>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Calculate via MySQL Function</button>
            </form>

            <div style="margin-top:16px; padding:14px; background:var(--emerald-bg); border:1px solid var(--emerald-border); border-radius:var(--radius-md); font-weight:700; color:var(--emerald-text);">
                Eligible Festive Discount: ₹<?php echo number_format((float)$discountAmount, 2); ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>
