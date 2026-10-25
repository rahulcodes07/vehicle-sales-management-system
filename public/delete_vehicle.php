<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('danger', 'Vehicles can only be removed from the vehicles page.');
    redirect_to('vehicles.php');
}

verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    flash('danger', 'That vehicle could not be found.');
    redirect_to('vehicles.php');
}

try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM Vehicle WHERE vehicle_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    flash('success', "Vehicle #{$id} was removed from stock.");
} catch (mysqli_sql_exception $exception) {
    flash('danger', "This vehicle can't be removed because a sale has been recorded for it.");
}

redirect_to('vehicles.php');