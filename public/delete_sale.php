<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('danger', 'Sales can only be cancelled from the sales page.');
    redirect_to('sales.php');
}

verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    flash('danger', 'That sale could not be found.');
    redirect_to('sales.php');
}

try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM Sales WHERE sale_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    flash('success', "Sale #{$id} was cancelled and the vehicle is back in stock.");
} catch (mysqli_sql_exception $exception) {
    flash('danger', 'This sale could not be cancelled. Please try again.');
}

redirect_to('sales.php');