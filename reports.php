<?php
$pageTitle = "ADBMS Analytics & Reports";
include 'header.php';

$selectedYear = intval($_GET['year'] ?? date('Y'));

// 1. Call Stored Procedure: sp_GetMonthlySalesReport
$monthlyReportQuery = "CALL sp_GetMonthlySalesReport({$selectedYear})";
$monthlyReportResult = mysqli_query($conn, $monthlyReportQuery);

// Clear stored procedure result buffer for subsequent queries
while(mysqli_more_results($conn) && mysqli_next_result($conn)) {
    $extraResult = mysqli_use_result($conn);
    if($extraResult instanceof mysqli_result) {
        mysqli_free_result($extraResult);
    }
}

// 2. Query Database View: view_employee_performance
$empPerformance = mysqli_query($conn, "SELECT * FROM view_employee_performance ORDER BY total_revenue_generated DESC");

// 3. Query Database View: view_inventory_summary
$invSummary = mysqli_query($conn, "SELECT * FROM view_inventory_summary");

// 4. Stored Function Demonstration
$calcPrice = floatval($_GET['calc_price'] ?? 1500000);
$calcYear = intval($_GET['calc_year'] ?? 2021);
$discountResult = mysqli_query($conn, "SELECT fn_CalculateDiscount({$calcPrice}, {$calcYear}) as discount");
$discountAmount = mysqli_fetch_assoc($discountResult)['discount'] ?? 0;
?>

<div class="page-header">
    <div>
        <h1>ADBMS Analytics & Database Objects</h1>
        <p class="subtitle">Demonstration of MySQL Stored Procedures, Views, Aggregate Metrics, and Stored Functions.</p>
    </div>
</div>

<!-- Section 1: Stored Procedure Report -->
<div class="page-header" style="margin-top:20px;">
    <h2>1. Stored Procedure: <code>sp_GetMonthlySalesReport(year)</code></h2>
    <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <label style="margin-bottom:0;">Select Year:</label>
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
                <th>Month</th>
                <th>Total Vehicles Sold</th>
                <th>Monthly Sales Revenue</th>
                <th>Average Sale Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if($monthlyReportResult && mysqli_num_rows($monthlyReportResult) > 0): ?>
                <?php while($m = mysqli_fetch_assoc($monthlyReportResult)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($m['month_name']); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo $m['total_sales_count']; ?> vehicles</span></td>
                        <td><strong>₹<?php echo number_format($m['total_monthly_revenue'], 2); ?></strong></td>
                        <td>₹<?php echo number_format($m['average_sale_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px; color:#64748b;">No sales transactions recorded for year <?php echo $selectedYear; ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Section 2: View 1 (Employee Performance) -->
<h2>2. Database View: <code>view_employee_performance</code></h2>
<div class="concept-box">
    <strong>💡 Why Database Views?</strong> A View is a stored virtual query. It aggregates performance data across <code>Employee</code>, <code>Branch</code>, and <code>Sales</code> without exposing underlying schema details or writing complex <code>GROUP BY</code> queries in PHP.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Designation</th>
                <th>Showroom Branch</th>
                <th>Vehicles Sold</th>
                <th>Total Revenue Brought In</th>
            </tr>
        </thead>
        <tbody>
            <?php if($empPerformance && mysqli_num_rows($empPerformance) > 0): ?>
                <?php while($ep = mysqli_fetch_assoc($empPerformance)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ep['employee_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ep['role']); ?></td>
                        <td><?php echo htmlspecialchars($ep['branch_name']); ?></td>
                        <td><span class="badge badge-success"><?php echo $ep['total_vehicles_sold']; ?> sales</span></td>
                        <td><strong>₹<?php echo number_format($ep['total_revenue_generated'], 2); ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:20px;">No employee data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Section 3: View 2 & Stored Function -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
    <div>
        <h2>3. View: <code>view_inventory_summary</code></h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total Stock</th>
                        <th>Available</th>
                        <th>Sold</th>
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
        <h2>4. Stored Function: <code>fn_CalculateDiscount</code></h2>
        <div class="form-card" style="max-width:100%;">
            <p style="font-size:13px; color:#64748b; margin-bottom:15px;">
                Executes MySQL server-side deterministic function to calculate discount based on vehicle age (&ge; 3 years gets 5%, &lt; 3 years gets 2%).
            </p>
            <form method="GET" action="reports.php">
                <div class="form-group">
                    <label>Vehicle Price (₹):</label>
                    <input type="number" name="calc_price" value="<?php echo $calcPrice; ?>" required>
                </div>
                <div class="form-group">
                    <label>Manufacturing Year:</label>
                    <input type="number" name="calc_year" value="<?php echo $calcYear; ?>" required>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Calculate via MySQL Function</button>
            </form>

            <div style="margin-top:15px; padding:12px; background:#dcfce7; border-radius:6px; font-weight:600; color:#15803d;">
                Calculated Discount: ₹<?php echo number_format($discountAmount, 2); ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
