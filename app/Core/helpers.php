<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money(mixed $value): string
{
    return '₹' . number_format((float) $value, 2);
}

function date_label(?string $value, string $format = 'M d, Y'): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date($format, $timestamp) : '—';
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

function old(string $key, mixed $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function selected(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'selected' : '';
}

function page_is(string|array $pages): bool
{
    $current = basename($_SERVER['PHP_SELF'] ?? '');
    return in_array($current, (array) $pages, true);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

function nav_active(string|array $pages): string
{
    return page_is($pages) ? 'active' : '';
}
