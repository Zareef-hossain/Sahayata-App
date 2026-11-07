<?php
/**
 * White information card with action buttons underneath
 * (e.g. "Flood - Sylhet / Urgency: High" + "Donate Now").
 *
 * Props:
 *   $lines    array   Text lines shown in the card (escaped automatically)
 *   $actions  array   Buttons under the card. Each item is one of:
 *                     link:   ['label' => 'Donate Now', 'href' => 'donor/donate?request=4']
 *                     API:    ['label' => 'Approve', 'api' => 'api/admin/requests/4/approve',
 *                              'confirm' => 'Approve this request?', 'body' => ['status' => 'x']]
 *                     Optional 'variant' => 'primary' (default) | 'danger'
 *                     API buttons are sent by public/assets/js/modules/actions.js.
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
                <?php if (isset($action['api'])): ?>
                    <button class="<?= $buttonClass ?>" type="button"
                            data-api-action="<?= e($action['api']) ?>"
                            <?= isset($action['confirm']) ? 'data-confirm="' . e($action['confirm']) . '"' : '' ?>
                            <?= isset($action['body']) ? 'data-body="' . e(json_encode($action['body'])) . '"' : '' ?>><?= e($action['label']) ?></button>
                <?php else: ?>
                    <a class="<?= $buttonClass ?>" href="<?= e(url($action['href'] ?? '')) ?>"><?= e($action['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
