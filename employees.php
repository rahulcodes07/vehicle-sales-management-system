<?php
$pageTitle = "Employees";
include 'header.php';

$query = "
    SELECT e.*, b.name as branch_name, b.location as branch_location 
    FROM Employee e 
    INNER JOIN Branch b ON e.branch_id = b.branch_id
    ORDER BY e.employee_id ASC
";
$result = mysqli_query($conn, $query);
?>

<div class="page-header">
    <div>
        <h1>Dealership Staff & Employees</h1>
        <p class="subtitle">Showroom managers, sales consultants, and finance advisors.</p>
    </div>
    <a href="add_employee.php" class="btn btn-primary">+ Add New Employee</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS Join Concept:</strong> This table demonstrates an <code>INNER JOIN</code> between <code>Employee</code> and <code>Branch</code> on <code>e.branch_id = b.branch_id</code>, resolving normalized branch details.
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Designation / Role</th>
                <th>Branch / Showroom</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Monthly Salary</th>
                <th>Joining Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['employee_id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['role']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['branch_name']); ?> (<?php echo htmlspecialchars($row['branch_location']); ?>)</td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?></td>
                        <td><strong>₹<?php echo number_format($row['salary'], 2); ?></strong></td>
                        <td><?php echo date('M d, Y', strtotime($row['hire_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#64748b;">No employee records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>