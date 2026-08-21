<?php
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Prepared statement to delete the sale
    // The MySQL Trigger `trg_after_sale_delete` will automatically fire!
    $stmt = mysqli_prepare($conn, "DELETE FROM Sales WHERE sale_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    try {
        if (mysqli_stmt_execute($stmt)) {
            header("Location: sales.php?msg=" . urlencode("Sale #{$id} was cancelled. MySQL Trigger restored vehicle to 'Available' and recorded audit log."));
            exit;
        } else {
            header("Location: sales.php?err=" . urlencode("Error: " . mysqli_error($conn)));
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        header("Location: sales.php?err=" . urlencode("Error: Could not cancel sale due to foreign key constraints or active insurance records."));
        exit;
    }
} else {
    header("Location: sales.php?err=" . urlencode("Invalid Sale ID."));
    exit;
}
?>
