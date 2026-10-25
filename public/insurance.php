<?php
$pageTitle = "Insurance";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$query = "
    SELECT i.*, v.model as vehicle_model, v.color as vehicle_color, c.name as customer_name, c.phone as customer_phone
    FROM Insurance i
    INNER JOIN Vehicle v ON i.vehicle_id = v.vehicle_id
    INNER JOIN Customer c ON i.customer_id = c.customer_id
    ORDER BY i.insurance_id DESC
";
$result = mysqli_query($conn, $query);
$policyCount = mysqli_num_rows($result);

page_head(
    'Insurance',
    'Policies issued for vehicles, with the insurer, cover period and premium.',
    '<a href="add_insurance.php" class="btn btn-primary">' . icon('plus', 16) . 'Issue policy</a>'
);

if (isset($_GET['msg'])) {
    echo alert_box('success', (string) $_GET['msg']);
}
?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Policy</th>
                <th>Insurer</th>
                <th>Vehicle</th>
                <th>Policyholder</th>
                <th>Cover period</th>
                <th class="num">Premium</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($policyCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php $isActive = strtotime($row['end_date']) >= time(); ?>
                    <tr>
                        <td>
                            <div class="cell-main"><?= e($row['policy_no']) ?></div>
                            <div class="cell-sub"><?= fmt_code('INS', (int) $row['insurance_id']) ?></div>
                        </td>
                        <td><?= e($row['provider']) ?></td>
                        <td>
                            <div class="cell-main"><?= e($row['vehicle_model']) ?></div>
                            <div class="cell-sub"><?= e($row['vehicle_color']) ?></div>
                        </td>
                        <td>
                            <div class="cell-main"><?= e($row['customer_name']) ?></div>
                            <div class="cell-sub"><?= e($row['customer_phone']) ?></div>
                        </td>
                        <td><?= e(date_label($row['start_date'])) ?> to <?= e(date_label($row['end_date'])) ?></td>
                        <td class="num cell-main"><?= e(money((float) $row['premium'])) ?></td>
                        <td><?= tag($isActive ? 'Active' : 'Expired', $isActive ? 'ok' : 'warn', true) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(7, 'No policies yet', 'Issue a policy to link cover to a vehicle and its owner.', '<a href="add_insurance.php" class="btn btn-primary btn-sm">Issue a policy</a>') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>