<?php
$pageTitle = "Employees";
require_once __DIR__ . '/../app/Views/layouts/header.php';

// Preserved exact query logic
$query = "
    SELECT e.*, b.name as branch_name, b.location as branch_location 
    FROM Employee e 
    INNER JOIN Branch b ON e.branch_id = b.branch_id
    ORDER BY e.employee_id ASC
";
$result = mysqli_query($conn, $query);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Dealership Team & Staff</h1>
        <p class="subtitle">Showroom consultants, branch managers, and finance advisors assigned to dealership branches.</p>
    </div>
    <a href="add_employee.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add New Team Member</span>
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>

<div class="concept-box">
    <strong>💡 ADBMS Join Concept (Proposal Phase 3 Item 1):</strong> This directory demonstrates an <code>INNER JOIN</code> between <code>Employee</code> and <code>Branch</code> on <code>e.branch_id = b.branch_id</code>, resolving normalized branch locations without storing redundant location strings in the Employee table (Third Normal Form - 3NF).
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Employee Code</th>
                <th>Staff Member</th>
                <th>Designation / Role</th>
                <th>Showroom Branch</th>
                <th>Contact Phone</th>
                <th>Work Email</th>
                <th>Base Monthly Salary</th>
                <th>Joining Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $initial = strtoupper(substr($row['name'], 0, 1));
                    $role = htmlspecialchars($row['role']);
                    $roleClass = match($role) {
                        'Sales Manager' => 'badge-purple',
                        'Senior Sales Executive' => 'badge-info',
                        'Finance Specialist' => 'badge-cyan',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td>
                            <span class="code-id">EMP-<?php echo sprintf('%03d', (int)$row['employee_id']); ?></span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="avatar" style="width:32px; height:32px; font-size:13px;"><?php echo $initial; ?></div>
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?php echo $roleClass; ?>"><?php echo $role; ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['branch_name']); ?></strong>
                            <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['branch_location']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?></td>
                        <td>
                            <strong class="inr-price">₹<?php echo number_format((float)$row['salary'], 2); ?></strong>
                        </td>
                        <td><?php echo date_label($row['hire_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                            </div>
                            <h3>No employees registered</h3>
                            <p>No staff records are present in the database. Use "+ Add New Team Member" to onboard employees.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>