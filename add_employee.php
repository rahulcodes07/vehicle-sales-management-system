<?php
$pageTitle = "Add Employee";
include 'header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = intval($_POST['branch_id'] ?? 0);
    $name      = trim($_POST['name'] ?? '');
    $role      = trim($_POST['role'] ?? 'Sales Executive');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $salary    = floatval($_POST['salary'] ?? 0);
    $hire_date = $_POST['hire_date'] ?? date('Y-m-d');

    if ($branch_id <= 0 || empty($name) || empty($phone) || $salary <= 0) {
        $errorMsg = "Please fill in all mandatory fields with valid values.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssds", $branch_id, $name, $role, $phone, $email, $salary, $hire_date);

        try {
            if (mysqli_stmt_execute($stmt)) {
                $successMsg = "Employee '{$name}' registered successfully!";
            } else {
                $errorMsg = "Database Error: " . mysqli_error($conn);
            }
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "Database Error: Email must be unique or Check Constraint violation (salary > 0).";
        }
        mysqli_stmt_close($stmt);
    }
}

$branches = mysqli_query($conn, "SELECT * FROM Branch ORDER BY name ASC");
?>

<div class="page-header">
    <div>
        <h1>Add Showroom Employee</h1>
        <p class="subtitle">Assign staff members to dealership branches.</p>
    </div>
    <a href="employees.php" class="btn" style="background:#64748b;">&larr; Back to Employees</a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_employee.php">
        <div class="form-group">
            <label for="branch_id">Dealership Branch *</label>
            <select id="branch_id" name="branch_id" required>
                <option value="">-- Select Branch --</option>
                <?php while($b = mysqli_fetch_assoc($branches)): ?>
                    <option value="<?php echo $b['branch_id']; ?>">
                        <?php echo htmlspecialchars($b['name'] . ' (' . $b['location'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="name">Employee Full Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g. Vikram Joshi" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="role">Designation / Role *</label>
                <select id="role" name="role">
                    <option value="Sales Executive">Sales Executive</option>
                    <option value="Senior Sales Executive">Senior Sales Executive</option>
                    <option value="Sales Manager">Sales Manager</option>
                    <option value="Finance Specialist">Finance Specialist</option>
                    <option value="Showroom Host">Showroom Host</option>
                </select>
            </div>

            <div class="form-group">
                <label for="salary">Monthly Salary (₹) *</label>
                <input type="number" id="salary" name="salary" step="500" min="1000" placeholder="e.g. 45000" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g. 9811223344" required>
            </div>

            <div class="form-group">
                <label for="email">Work Email</label>
                <input type="email" id="email" name="email" placeholder="e.g. vikram@vsms.com">
            </div>
        </div>

        <div class="form-group">
            <label for="hire_date">Date of Joining</label>
            <input type="date" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Add Employee Record</button>
    </form>
</div>

<?php include 'footer.php'; ?>
