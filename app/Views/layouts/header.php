<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Core/bootstrap.php';

$pageTitle = $pageTitle ?? 'Overview';
$flashMessages = consume_flash();

/* ---------- Small view helpers shared by every page ---------- */

function icon(string $name, int $size = 18): string
{
    static $paths = [
        'grid'     => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'car'      => '<path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/>',
        'receipt'  => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'team'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 17c.6-1.7 1.9-2.5 3-2.5s2.4.8 3 2.5M15 9h3M15 13h3"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'chart'    => '<path d="M18 20V10M12 20V4M6 20v-6"/>',
        'history'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'arrow-left' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        'x-circle' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
        'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'menu'     => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'x'        => '<path d="M18 6 6 18M6 6l12 12"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1z"/>',
    ];

    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? '') . '</svg>';
}

function tag(string $text, string $variant = '', bool $dot = false): string
{
    $class = 'tag' . ($variant !== '' ? ' tag-' . $variant : '') . ($dot ? ' tag-dot' : '');
    return '<span class="' . e($class) . '">' . e($text) . '</span>';
}

function fmt_code(string $prefix, int $id): string
{
    return e($prefix . '-' . sprintf('%03d', $id));
}

function alert_box(string $type, string $message): string
{
    $icons = ['success' => 'check-circle', 'danger' => 'x-circle'];
    return '<div class="alert alert-' . e($type) . '" role="' . ($type === 'danger' ? 'alert' : 'status') . '">'
        . icon($icons[$type] ?? 'info', 18)
        . '<p>' . e($message) . '</p>'
        . '<button type="button" class="alert-close" data-dismiss aria-label="Dismiss">' . icon('x', 16) . '</button>'
        . '</div>';
}

function page_head(string $title, string $subtitle = '', string $actions = ''): void
{
    echo '<div class="page-head"><div><h1>' . e($title) . '</h1>';
    if ($subtitle !== '') {
        echo '<p>' . e($subtitle) . '</p>';
    }
    echo '</div>';
    if ($actions !== '') {
        echo '<div class="page-actions">' . $actions . '</div>';
    }
    echo '</div>';
}

function empty_row(int $cols, string $title, string $text, string $action = ''): string
{
    return '<tr><td colspan="' . $cols . '"><div class="empty">' . icon('inbox', 28)
        . '<h3>' . e($title) . '</h3><p>' . e($text) . '</p>' . $action . '</div></td></tr>';
}

/* ---------- Navigation ---------- */

$navGroups = [
    'Showroom' => [
        ['index.php', 'Overview', 'grid', ['index.php']],
        ['vehicles.php', 'Vehicles', 'car', ['vehicles.php', 'add_vehicle.php']],
        ['sales.php', 'Sales', 'receipt', ['sales.php', 'add_sale.php']],
        ['customers.php', 'Customers', 'users', ['customers.php', 'add_customer.php']],
    ],
    'Company' => [
        ['employees.php', 'Team', 'team', ['employees.php', 'add_employee.php']],
        ['insurance.php', 'Insurance', 'shield', ['insurance.php', 'add_insurance.php']],
    ],
    'Insights' => [
        ['reports.php', 'Reports', 'chart', ['reports.php']],
        ['audit_log.php', 'Activity log', 'history', ['audit_log.php']],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#12161d">
    <title><?= e($pageTitle) ?> | Ghost Vehicle Sales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="skip-link" href="#content">Skip to content</a>
    <div class="sidebar-backdrop" data-sidebar-backdrop></div>
    <div class="app">
        <aside class="sidebar" data-sidebar>
            <a class="brand" href="index.php">
                <span class="brand-mark"><?= icon('car', 20) ?></span>
                <span class="brand-text">
                    <strong>Ghost Vehicle Sales</strong>
                    <small>Dealership management</small>
                </span>
            </a>
            <nav aria-label="Main">
                <?php foreach ($navGroups as $group => $links): ?>
                    <p class="nav-group"><?= e($group) ?></p>
                    <?php foreach ($links as [$href, $label, $iconName, $pages]): ?>
                        <a href="<?= e($href) ?>" class="nav-link <?= nav_active($pages) ?>"<?= page_is($pages) ? ' aria-current="page"' : '' ?>>
                            <?= icon($iconName, 18) ?>
                            <span><?= e($label) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar">
                <button type="button" class="menu-toggle" data-menu-toggle aria-label="Open menu" aria-expanded="false">
                    <?= icon('menu', 20) ?>
                </button>
                <span class="topbar-date"><?= e(date('l, j F Y')) ?></span>
                <a href="add_sale.php" class="btn btn-primary btn-sm"><?= icon('plus', 16) ?>Record sale</a>
            </header>
            <main id="content" class="content">
                <?php foreach ($flashMessages as $flash): ?>
                    <?= alert_box((string) $flash['type'], (string) $flash['message']) ?>
                <?php endforeach; ?>