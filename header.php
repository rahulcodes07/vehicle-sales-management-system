<?php
if (!isset($conn)) {
    require_once 'db.php';
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - VSMS' : 'Vehicle Sales Management System'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div class="nav-brand">
            <span class="logo-icon">🚗</span>
            <span class="logo-text">VSMS <small>ADBMS Portal</small></span>
        </div>
        <div class="nav-links">
            <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="vehicles.php" class="<?php echo in_array($currentPage, ['vehicles.php', 'add_vehicle.php', 'edit_vehicle.php']) ? 'active' : ''; ?>">Vehicles</a>
            <a href="customers.php" class="<?php echo in_array($currentPage, ['customers.php', 'add_customer.php']) ? 'active' : ''; ?>">Customers</a>
            <a href="employees.php" class="<?php echo in_array($currentPage, ['employees.php', 'add_employee.php']) ? 'active' : ''; ?>">Employees</a>
            <a href="sales.php" class="<?php echo in_array($currentPage, ['sales.php', 'add_sale.php']) ? 'active' : ''; ?>">Sales</a>
            <a href="insurance.php" class="<?php echo in_array($currentPage, ['insurance.php', 'add_insurance.php']) ? 'active' : ''; ?>">Insurance</a>
            <a href="reports.php" class="<?php echo $currentPage == 'reports.php' ? 'active' : ''; ?>">📊 Analytics & Views</a>
            <a href="audit_log.php" class="<?php echo $currentPage == 'audit_log.php' ? 'active' : ''; ?>">⚡ Trigger Logs</a>
        </div>
    </nav>
    <main class="container">
