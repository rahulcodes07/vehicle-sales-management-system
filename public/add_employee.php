<?php
$pageTitle = "Add team member";
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

    // An empty email is stored as NULL so several people can leave it blank
    $emailValue = $email === '' ? null : $email;

    if ($branch_id <= 0 || empty($name) || empty($phone) || $salary <= 0) {
        $errorMsg = "Choose a branch, enter a name and phone number, and a salary above zero.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssds", $branch_id, $name, $role, $phone, $emailValue, $salary, $hire_date);

        try {
            mysqli_stmt_execute($stmt);
            $successMsg = "{$name} has been added to the team.";
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "This team member could not be added. The email may already be in use, or the salary is not valid.";
        }
        mysqli_stmt_close($stmt);
    }
}

$branches = mysqli_query($conn, "SELECT * FROM Branch ORDER BY name ASC");
$form = $errorMsg !== '' ? $_POST : [];

page_head(
    'Add a team member',
    'Assign a new team member to a branch.',
    '<a href="employees.php" class="btn">' . icon('arrow-left', 16) . 'Back to team</a>'
);

if ($successMsg !== '') echo alert_box('success', $successMsg);
if ($errorMsg !== '') echo alert_box('danger', $errorMsg);
?>

<form method="POST" action="add_employee.php" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-section">
        <h2>Role and branch</h2>
        <p>Fields marked <span class="req">*</span> are required.</p>

        <div class="field">
            <label for="branch_id">Branch <span class="req">*</span></label>
            <select id="branch_id" name="branch_id" required>
                <option value="">Choose a branch</option>
                <?php while ($b = mysqli_fetch_assoc($branches)): ?>
                    <option value="<?= (int) $b['branch_id'] ?>" <?= selected($form['branch_id'] ?? '', $b['branch_id']) ?>>
                        <?= e($b['name'] . ', ' . $b['location']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <?php foreach (['Sales Executive', 'Senior Sales Executive', 'Sales Manager', 'Finance Specialist', 'Showroom Host'] as $opt): ?>
                        <option value="<?= e($opt) ?>" <?= selected($form['role'] ?? 'Sales Executive', $opt) ?>><?= e($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="salary">Monthly salary (₹) <span class="req">*</span></label>
                <input type="number" id="salary" name="salary" step="500" min="1000" value="<?= e($form['salary'] ?? '') ?>" placeholder="For example, 52000" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Personal details</h2>
        <p>How to reach them and when they joined.</p>

        <div class="field">
            <label for="name">Full name <span class="req">*</span></label>
            <input type="text" id="name" name="name" value="<?= e($form['name'] ?? '') ?>" placeholder="For example, Kavya Reddy" required>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="phone">Mobile number <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" value="<?= e($form['phone'] ?? '') ?>" placeholder="For example, 9850011223" required>
            </div>
            <div class="field">
                <label for="email">Work email</label>
                <input type="email" id="email" name="email" value="<?= e($form['email'] ?? '') ?>" placeholder="name@company.com">
                <span class="hint">Each team member needs a different email.</span>
            </div>
        </div>
        <div class="field">
            <label for="hire_date">Joining date</label>
            <input type="date" id="hire_date" name="hire_date" value="<?= e($form['hire_date'] ?? date('Y-m-d')) ?>" required>
        </div>
    </div>

    <div class="form-actions">
        <a href="employees.php" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Add team member</button>
    </div>
</form>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>