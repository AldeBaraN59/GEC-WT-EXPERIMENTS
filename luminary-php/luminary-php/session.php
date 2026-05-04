<?php
// session.php — Shared Session & Cookie Handler
session_start();

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_theme_cookie_name(): string {
    if (is_logged_in()) {
        return 'luminary_theme_' . (int) $_SESSION['user_id'];
    }

    return 'luminary_theme_guest';
}

$allowed_themes = ['classic', 'midnight', 'sepia', 'frost'];
$current_theme = 'classic';

if (is_logged_in()) {
    $theme_cookie = get_theme_cookie_name();

    if (
        isset($_COOKIE[$theme_cookie]) &&
        in_array($_COOKIE[$theme_cookie], $allowed_themes, true)
    ) {
        $current_theme = $_COOKIE[$theme_cookie];
    }

    if (
        isset($_GET['theme']) &&
        in_array($_GET['theme'], $allowed_themes, true)
    ) {
        $current_theme = $_GET['theme'];

        setcookie(
            $theme_cookie,
            $current_theme,
            time() + (30 * 24 * 60 * 60),
            '/'
        );

        $_COOKIE[$theme_cookie] = $current_theme;
    }
}

function get_user_name(): string {
    return isset($_SESSION['user_name'])
        ? htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8')
        : 'Guest';
}

function get_user_email(): string {
    return isset($_SESSION['user_email'])
        ? htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8')
        : '';
}

function get_theme_css(string $theme): string {
    $themes = [
        'classic'  => ['--ink:#0d0d0d', '--paper:#f5f0e8', '--cream:#faf7f2', '--gold:#c8922a', '--gold-light:#e8b84b', '--muted:#8a8278', '--border:#d8cfc0'],
        'midnight' => ['--ink:#e8e4dc', '--paper:#1a1a1a', '--cream:#141414', '--gold:#e8b84b', '--gold-light:#f5d07a', '--muted:#6a6460', '--border:#2a2a2a'],
        'sepia'    => ['--ink:#2c1a0e', '--paper:#f2e8d5', '--cream:#faf4e8', '--gold:#a0621a', '--gold-light:#c8922a', '--muted:#7a6a58', '--border:#c8b898'],
        'frost'    => ['--ink:#1a2a3a', '--paper:#eef4fb', '--cream:#f5f9ff', '--gold:#2a7ab8', '--gold-light:#4a9ad8', '--muted:#6a8298', '--border:#c8d8e8'],
    ];

    $vars = $themes[$theme] ?? $themes['classic'];
    return ':root { ' . implode('; ', $vars) . '; }';
}
?>