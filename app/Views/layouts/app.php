<?php
/**
 * Main page layout. Every page is rendered inside this file.
 *
 * Variables a page can pass:
 *   $title    string  Text for the browser tab
 *   $theme    string  'bright' for the login screen background (#00C1F6), default otherwise
 *   $styles   array   Extra CSS files from public/assets/css (page-specific)
 *   $scripts  array   Extra JS modules from public/assets/js (page-specific)
 *   $content  string  The page HTML (filled in automatically)
 */
$themeClass = ($theme ?? '') === 'bright' ? 'theme-bright' : 'theme-default';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(App\Core\Csrf::token()) ?>">
    <meta name="base-url" content="<?= e(rtrim(url(), '/')) ?>">
    <title><?= e(isset($title) ? $title . ' | Sahayata' : 'Sahayata') ?></title>

    <link rel="icon" type="image/png" href="<?= e(asset('images/logo-sahayata-badge.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">

    <link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/layout.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
    <?php foreach ($styles ?? [] as $stylesheet): ?>
        <link rel="stylesheet" href="<?= e(asset('css/' . $stylesheet)) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($themeClass) ?>">
    <main class="app-shell">
        <?= $content ?>
    </main>

    <script type="module" src="<?= e(asset('js/main.js')) ?>"></script>
    <?php foreach ($scripts ?? [] as $script): ?>
        <script type="module" src="<?= e(asset('js/' . $script)) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
