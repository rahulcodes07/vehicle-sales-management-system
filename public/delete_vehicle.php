<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('danger', 'Vehicle deletion must be submitted from the protected form.');
    redirect_to('vehicles.php');
}

verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($id <= 0) {
    flash('danger', 'Invalid vehicle ID.');
    redirect_to('vehicles.php');
}

try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM Vehicle WHERE vehicle_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    flash('success', "Vehicle #{$id} deleted successfully.");
} catch (mysqli_sql_exception $exception) {
    flash('danger', 'Referential integrity protection: this vehicle is referenced by sales or insurance records.');
}

redirect_to('vehicles.php');
