<?php
/**
 * Sahayata logo, taken from the Figma export.
 * Props: $variant 'primary' (login screen, 152px) or 'badge' (inner screens, 108px)
 */
$variant = ($variant ?? 'badge') === 'primary' ? 'primary' : 'badge';
$file = $variant === 'primary' ? 'images/logo-sahayata.png' : 'images/logo-sahayata-badge.png';
$alt = $variant === 'primary'
    ? 'Sahayata — Unified Disaster Response and Relief Platform'
    : 'Sahayata Unified Platform — Empowering Bangladesh';
?>
<img class="logo logo--<?= e($variant) ?>" src="<?= e(asset($file)) ?>" alt="<?= e($alt) ?>">
