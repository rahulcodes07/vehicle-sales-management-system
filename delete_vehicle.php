<?php
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Attempt deletion using Prepared Statement
    $stmt = mysqli_prepare($conn, "DELETE FROM Vehicle WHERE vehicle_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    try {
        if (mysqli_stmt_execute($stmt)) {
            header("Location: vehicles.php?msg=" . urlencode("Vehicle #{$id} deleted successfully."));
            exit;
        } else {
            header("Location: vehicles.php?err=" . urlencode("Error deleting vehicle: " . mysqli_error($conn)));
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        // ADBMS Concept: Foreign Key Constraint (Referential Integrity) violation caught!
        header("Location: vehicles.php?err=" . urlencode("Referential Integrity Protection: Cannot delete this vehicle because active sales/insurance records reference it."));
        exit;
    }
} else {
    header("Location: vehicles.php?err=" . urlencode("Invalid Vehicle ID."));
    exit;
}
?>
