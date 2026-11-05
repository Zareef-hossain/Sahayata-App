<?php
/**
 * White information card with action buttons underneath
 * (e.g. "Flood - Sylhet / Urgency: High" + "Donate Now").
 *
 * Props:
 *   $lines    array   Text lines shown in the card (escaped automatically)
 *   $actions  array   Buttons/forms under the card. Each item:
 *                     ['label' => 'Approve', 'variant' => 'primary'|'danger', 'href' => 'donor/donate?request=4']
 *                     or a form button:
 *                     ['label' => 'Approve', 'variant' => 'primary', 'action' => 'admin/requests/4/approve']
 *   $size     string  'sm' (49px), 'md' (70px) — matches the Figma card heights
 */
$lines ??= [];
$actions ??= [];
$size ??= 'sm';
?>
<article class="card">
    <div class="card__body card__body--<?= e($size) ?>">
        <?php foreach ($lines as $line): ?>
            <p class="card__line"><?= e($line) ?></p>
        <?php endforeach; ?>
    </div>

    <?php if ($actions !== []): ?>
        <div class="card__actions">
            <?php foreach ($actions as $action): ?>
                <?php $buttonClass = 'btn btn--' . e($action['variant'] ?? 'primary') . ' card__action'; ?>
                <?php if (isset($action['action'])): ?>
                    <form class="card__form" method="post" action="<?= e(url($action['action'])) ?>">
                        <?= csrf_field() ?>
                        <button class="<?= $buttonClass ?>" type="submit"><?= e($action['label']) ?></button>
                    </form>
                <?php else: ?>
                    <a class="<?= $buttonClass ?>" href="<?= e(url($action['href'] ?? '')) ?>"><?= e($action['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
