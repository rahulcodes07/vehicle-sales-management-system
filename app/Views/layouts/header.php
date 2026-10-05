<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Core/bootstrap.php';

$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$flashMessages = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1220">
    <title><?php echo e($pageTitle); ?> · VSMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" data-sidebar>
            <div class="brand">
                <div class="brand-mark">V</div>
                <div>
                    <strong>VSMS</strong>
                    <span>ADBMS Control Center</span>
                </div>
            </div>

            <div class="sidebar-label">Workspace</div>
            <nav class="sidebar-nav" aria-label="Primary navigation">
                <a href="index.php" class="<?php echo nav_active('index.php'); ?>"><span>⌂</span> Dashboard</a>
                <a href="vehicles.php" class="<?php echo nav_active(['vehicles.php', 'add_vehicle.php']); ?>"><span>▣</span> Inventory</a>
                <a href="sales.php" class="<?php echo nav_active(['sales.php', 'add_sale.php']); ?>"><span>↗</span> Sales</a>
                <a href="customers.php" class="<?php echo nav_active(['customers.php', 'add_customer.php']); ?>"><span>♙</span> Customers</a>
                <a href="employees.php" class="<?php echo nav_active(['employees.php', 'add_employee.php']); ?>"><span>♟</span> Team</a>
                <a href="insurance.php" class="<?php echo nav_active(['insurance.php', 'add_insurance.php']); ?>"><span>◇</span> Insurance</a>
            </nav>

            <div class="sidebar-label">Database intelligence</div>
            <nav class="sidebar-nav" aria-label="Database navigation">
                <a href="reports.php" class="<?php echo nav_active('reports.php'); ?>"><span>◫</span> Analytics & Views</a>
                <a href="audit_log.php" class="<?php echo nav_active('audit_log.php'); ?>"><span>⌁</span> Trigger Audit</a>
            </nav>

            <div class="sidebar-footer">
                <span class="status-dot"></span>
                <div><strong>MySQL connected</strong><small>Database-first workspace</small></div>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <button class="menu-toggle" data-menu-toggle aria-label="Toggle navigation" aria-expanded="false">☰</button>
                <div class="breadcrumb"><span>VSMS</span><b>/</b><?php echo e($pageTitle); ?></div>
                <div class="topbar-actions">
                    <a href="add_sale.php" class="quick-action">＋ Record sale</a>
                    <span class="today"><?php echo date('D, M j, Y'); ?></span>
                </div>
            </header>
            <main class="container">
                <?php foreach ($flashMessages as $flash): ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?>" role="status">
                        <span><?php echo e($flash['message']); ?></span>
                        <button type="button" data-dismiss aria-label="Dismiss">×</button>
                    </div>
                <?php endforeach; ?>
