<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('danger', 'Sale cancellation must be submitted from the protected form.');
    redirect_to('sales.php');
}

verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($id <= 0) {
    flash('danger', 'Invalid sale ID.');
    redirect_to('sales.php');
}

try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM Sales WHERE sale_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    flash('success', "Sale #{$id} cancelled. The delete trigger restored the vehicle and recorded the audit event.");
} catch (mysqli_sql_exception $exception) {
    flash('danger', 'This sale cannot be cancelled while dependent insurance or records exist.');
}

redirect_to('sales.php');
