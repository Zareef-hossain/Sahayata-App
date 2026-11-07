<?php
/**
 * Row of light-blue tab buttons, e.g. "Home | My Donations | Profile".
 *
 * Props:
 *   $items  array  [['label' => 'Home', 'href' => 'donor/dashboard', 'active' => true, 'width' => 57], ...]
 *                  'width' (px) is optional — use it to match the Figma size of each tab.
 *   $label  string Accessible name of the navigation, e.g. 'Donor menu'
 */
?>
<nav class="tab-nav" aria-label="<?= e($label ?? 'Section menu') ?>">
    <?php foreach ($items ?? [] as $item): ?>
        <a class="tab-nav__item<?= !empty($item['active']) ? ' is-active' : '' ?>"
           href="<?= e(url($item['href'])) ?>"
           <?= !empty($item['active']) ? 'aria-current="page"' : '' ?>
           <?= isset($item['width']) ? 'style="width: ' . (int) $item['width'] . 'px"' : '' ?>><?= e($item['label']) ?></a>
    <?php endforeach; ?>
</nav>
