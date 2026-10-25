<?php
$pageTitle = "Sales";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$result = mysqli_query($conn, "SELECT * FROM view_sales_details ORDER BY sale_id DESC");
$saleCount = mysqli_num_rows($result);

page_head(
    'Sales',
    'Every completed sale, who made it and how it was paid for.',
    '<a href="add_sale.php" class="btn btn-primary">' . icon('plus', 16) . 'Record sale</a>'
);

if (isset($_GET['msg'])) {
    echo alert_box('success', (string) $_GET['msg']);
}
if (isset($_GET['err'])) {
    echo alert_box('danger', (string) $_GET['err']);
}
?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Sale</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Sold by</th>
                <th>Branch</th>
                <th>Date</th>
                <th class="num">Price</th>
                <th>Payment</th>
                <th class="actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($saleCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="id"><?= fmt_code('SALE', (int) $row['sale_id']) ?></td>
                        <td>
                            <div class="cell-main"><?= e($row['vehicle_model']) ?></div>
                            <div class="cell-sub"><?= e($row['vehicle_type']) ?>, <?= e($row['vehicle_color']) ?></div>
                        </td>
                        <td>
                            <div class="cell-main"><?= e($row['customer_name']) ?></div>
                            <div class="cell-sub"><?= e($row['customer_phone']) ?></div>
                        </td>
                        <td><?= e($row['employee_name']) ?></td>
                        <td><?= e($row['branch_location']) ?></td>
                        <td><?= e(date_label($row['sale_date'])) ?></td>
                        <td class="num cell-main"><?= e(money((float) $row['sale_price'])) ?></td>
                        <td><?= tag((string) $row['payment_mode']) ?></td>
                        <td class="actions">
                            <form method="POST" action="delete_sale.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $row['sale_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Cancel this sale? The vehicle will go back into stock as available.">Cancel sale</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(9, 'No sales recorded', 'Once you record a sale it will be listed here.', '<a href="add_sale.php" class="btn btn-primary btn-sm">Record a sale</a>') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>