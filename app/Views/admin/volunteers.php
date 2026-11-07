<?php
/**
 * Admin — "Volunteers" tab. Variables: $tabs, $volunteers, $flash
 */
?>
<?php component('page-header', ['backHref' => 'admin/dashboard', 'title' => 'Volunteers', 'titleGap' => 36]); ?>

<section class="content-column screen--with-tabs screen--admin">
    <?php component('tab-nav', ['label' => 'Admin menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">Volunteers (<?= count($volunteers) ?>):</h2>
    <?php if ($volunteers === []): ?>
        <p class="empty-state">No volunteers have registered yet.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($volunteers as $volunteer): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    '👤 ' . $volunteer['full_name'] . ' (' . ($volunteer['availability'] === 'available' ? 'Available' : 'Busy') . ')',
                    '📞 ' . $volunteer['phone'] . ($volunteer['status'] !== 'active' ? ' · Account not active' : ''),
                    'Active tasks : ' . $volunteer['active_tasks'] . ' · Delivered : ' . $volunteer['delivered_tasks'],
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
