<?php
$pageTitle = "Team";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$query = "
    SELECT e.*, b.name as branch_name, b.location as branch_location
    FROM Employee e
    INNER JOIN Branch b ON e.branch_id = b.branch_id
    ORDER BY e.employee_id ASC
";
$result = mysqli_query($conn, $query);
$teamCount = mysqli_num_rows($result);

page_head(
    'Team',
    'Staff across all branches, with their role and contact details.',
    '<a href="add_employee.php" class="btn btn-primary">' . icon('plus', 16) . 'Add team member</a>'
);

if (isset($_GET['msg'])) {
    echo alert_box('success', (string) $_GET['msg']);
}
?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Team member</th>
                <th>Role</th>
                <th>Branch</th>
                <th>Phone</th>
                <th>Email</th>
                <th class="num">Monthly salary</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($teamCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div class="cell-person">
                                <div class="avatar"><?= e(strtoupper(substr((string) $row['name'], 0, 1))) ?></div>
                                <div>
                                    <div class="cell-main"><?= e($row['name']) ?></div>
                                    <div class="cell-sub"><?= fmt_code('EMP', (int) $row['employee_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= tag((string) $row['role']) ?></td>
                        <td>
                            <div class="cell-main"><?= e($row['branch_name']) ?></div>
                            <div class="cell-sub"><?= e($row['branch_location']) ?></div>
                        </td>
                        <td><?= e($row['phone']) ?></td>
                        <td><?= e($row['email'] ?: 'Not given') ?></td>
                        <td class="num"><?= e(money((float) $row['salary'])) ?></td>
                        <td><?= e(date_label($row['hire_date'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(7, 'No team members yet', 'Add your first team member to get started.', '<a href="add_employee.php" class="btn btn-primary btn-sm">Add team member</a>') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>