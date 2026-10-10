<?php
$pageTitle = "Add Employee";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
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
    <div class="page-header-content">
        <h1>Onboard Dealership Staff Member</h1>
        <p class="subtitle">Assign showroom personnel, sales executives, and finance advisors to authorized dealership branches.</p>
    </div>
    <a href="employees.php" class="btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        <span>Back to Team</span>
    </a>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success">
        <div class="alert-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span><?php echo htmlspecialchars($successMsg); ?></span>
        </div>
        <button type="button" data-dismiss aria-label="Dismiss">&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger">
        <div class="alert-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span><?php echo htmlspecialchars($errorMsg); ?></span>
        </div>
        <button type="button" data-dismiss aria-label="Dismiss">&times;</button>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="add_employee.php">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        
        <div class="form-group">
            <label for="branch_id">Authorized Showroom Branch (Foreign Key) *</label>
            <select id="branch_id" name="branch_id" required>
                <option value="">-- Choose Dealership Branch --</option>
                <?php while($b = mysqli_fetch_assoc($branches)): ?>
                    <option value="<?php echo $b['branch_id']; ?>">
                        <?php echo htmlspecialchars($b['name'] . ' — ' . $b['location']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="name">Staff Member Full Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g. Kavya Reddy, Arjun Mehta" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="role">Designation / Organizational Role *</label>
                <select id="role" name="role">
                    <option value="Sales Executive">Sales Executive</option>
                    <option value="Senior Sales Executive">Senior Sales Executive</option>
                    <option value="Sales Manager">Sales Manager</option>
                    <option value="Finance Specialist">Finance Specialist</option>
                    <option value="Showroom Host">Showroom Host</option>
                </select>
            </div>

            <div class="form-group">
                <label for="salary">Monthly Base Salary (₹ &gt; 0) *</label>
                <input type="number" id="salary" name="salary" step="500" min="1000" placeholder="e.g. 52000" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="phone">Contact Mobile Number *</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g. 9850011223" required>
            </div>

            <div class="form-group">
                <label for="email">Dealership Email (Unique)</label>
                <input type="email" id="email" name="email" placeholder="e.g. staff@vsms.com">
            </div>
        </div>

        <div class="form-group">
            <label for="hire_date">Date of Joining / Employment</label>
            <input type="date" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Onboard Staff Member</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>
