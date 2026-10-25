<?php
$pageTitle = "Activity log";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$result = mysqli_query($conn, "SELECT * FROM Sales_Audit_Log ORDER BY log_id DESC");

page_head('Activity log', 'A record of every sale made or cancelled.');
?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Activity</th>
                <th>Details</th>
                <th>Sale</th>
                <th>Vehicle</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    [$label, $variant] = match ($row['action_type']) {
                        'INSERT' => ['Sale recorded', 'ok'],
                        'DELETE' => ['Sale cancelled', 'warn'],
                        default  => ['Updated', 'info'],
                    };
                    ?>
                    <tr>
                        <td class="num" style="text-align:left;"><?= e(date('j M Y, g:i A', strtotime((string) $row['action_timestamp']))) ?></td>
                        <td><?= tag($label, $variant, true) ?></td>
                        <td><?= e($row['action_description']) ?></td>
                        <td class="id"><?= $row['sale_id'] ? fmt_code('SALE', (int) $row['sale_id']) : '—' ?></td>
                        <td class="id"><?= $row['vehicle_id'] ? fmt_code('VH', (int) $row['vehicle_id']) : '—' ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(5, 'Nothing here yet', 'When you record or cancel a sale, it will be listed here.') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>