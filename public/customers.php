<?php
$pageTitle = "Customers";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM Customer WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY customer_id DESC");
    $searchTerm = "%{$search}%";
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM Customer ORDER BY customer_id DESC");
}
$customerCount = mysqli_num_rows($result);

page_head(
    'Customers',
    'Everyone registered with the dealership, with their contact details.',
    '<a href="add_customer.php" class="btn btn-primary">' . icon('plus', 16) . 'Register customer</a>'
);

if (isset($_GET['msg'])) {
    echo alert_box('success', (string) $_GET['msg']);
}
if (isset($_GET['err'])) {
    echo alert_box('danger', (string) $_GET['err']);
}
?>

<form method="GET" class="toolbar">
    <div class="field search">
        <label for="search" class="sr-only">Search customers</label>
        <?= icon('search', 16) ?>
        <input type="search" id="search" name="search" value="<?= e($search) ?>" placeholder="Search by name, phone or email">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if (!empty($search)): ?>
        <a href="customers.php" class="btn">Clear</a>
    <?php endif; ?>
    <div class="toolbar-end"><span><?= number_format($customerCount) ?> <?= $customerCount === 1 ? 'customer' : 'customers' ?></span></div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($customerCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div class="cell-person">
                                <div class="avatar"><?= e(strtoupper(substr((string) $row['name'], 0, 1))) ?></div>
                                <div>
                                    <div class="cell-main"><?= e($row['name']) ?></div>
                                    <div class="cell-sub"><?= fmt_code('CUST', (int) $row['customer_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= e($row['phone']) ?></td>
                        <td><?= e($row['email'] ?: 'Not given') ?></td>
                        <td><?= e($row['address']) ?></td>
                        <td><?= isset($row['created_at']) ? e(date_label($row['created_at'])) : '—' ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?= empty_row(5, 'No customers found', !empty($search) ? 'Nothing matched your search. Try a different name, phone number or email.' : 'No customers have been registered yet.', '<a href="add_customer.php" class="btn btn-primary btn-sm">Register a customer</a>') ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>