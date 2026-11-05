<?php
/**
 * Top of every inner screen: "< Back" button, logo and page title.
 *
 * Props:
 *   $backHref  ?string  Link for the Back button (null = no button)
 *   $logo      string   'badge' (inner screens) or 'primary' (login screen)
 *   $title     ?string  Page heading, e.g. 'Request for Help'
 *   $icon      ?string  Emoji shown before the title, e.g. '❤' or '👑'
 */
$backHref ??= null;
$logo ??= 'badge';
$title ??= null;
$icon ??= null;
?>
<header class="page-header">
    <?php if ($backHref !== null): ?>
        <?php component('back-button', ['href' => $backHref]); ?>
    <?php endif; ?>

    <?php component('logo', ['variant' => $logo]); ?>

    <?php if ($title !== null): ?>
        <h1 class="page-title">
            <?php if ($icon !== null): ?><span class="page-title__icon" aria-hidden="true"><?= e($icon) ?></span><?php endif; ?>
            <?= e($title) ?>
        </h1>
    <?php endif; ?>
</header>
