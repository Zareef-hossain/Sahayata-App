<?php
/**
 * Top of every screen: "< Back" button, logo and page title.
 *
 * Props:
 *   $backHref    ?string  Link for the Back button (null = no button)
 *   $backLogout  bool     Back button logs out (first screen of a role)
 *   $logo        string   'badge' (inner screens) or 'primary' (login screen)
 *   $title       ?string  Page heading, e.g. 'Request for Help'
 *   $icon        ?string  Emoji shown before the title, e.g. '❤' or '👑'
 *   $titleGap    ?int     Space (px) between logo and title, measured per Figma screen
 */
$backHref ??= null;
$backLogout ??= false;
$logo ??= 'badge';
$title ??= null;
$icon ??= null;
$titleGap ??= null;
?>
<header class="page-header">
    <?php if ($backLogout): ?>
        <?php component('back-button', ['logout' => true]); ?>
    <?php elseif ($backHref !== null): ?>
        <?php component('back-button', ['href' => $backHref]); ?>
    <?php endif; ?>

    <?php component('logo', ['variant' => $logo]); ?>

    <?php if ($title !== null): ?>
        <h1 class="page-title"<?= $titleGap !== null ? ' style="margin-top: ' . (int) $titleGap . 'px"' : '' ?>>
            <?php if ($icon !== null): ?><span class="page-title__icon" aria-hidden="true"><?= e($icon) ?></span><?php endif; ?>
            <?= e($title) ?>
        </h1>
    <?php endif; ?>
</header>
