<?php
/**
 * Manager — Figma "Manager Dashboard". All numbers come from the database.
 * Variables: $tabs, $stats, $admins, $flash
 */
?>
<?php component('page-header', ['backLogout' => true, 'title' => 'Manager Dashboard', 'icon' => '👑', 'titleGap' => 20]); ?>

<section class="content-column screen--with-tabs screen--manager">
    <?php component('tab-nav', ['label' => 'Manager menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading info-heading--bold stats-heading">System Statistics:</h2>
    <p class="stats-lines">
        <span>👥 Users: <?= e($stats['users']) ?> | 🛡️ Admins: <?= e($stats['admins']) ?></span><br>
        <span>📦 Donations: <?= e($stats['donations']) ?> | 🚚 Delivered: <?= e($stats['delivered']) ?></span>
    </p>

    <h2 class="info-heading info-heading--bold admins-heading">Admin Management :</h2>
    <?php if ($admins === []): ?>
        <p class="empty-state">No administrators yet. Create the first one below.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($admins as $admin): ?>
            <?php $isActive = $admin['status'] === 'active'; ?>
            <?php component('card', [
                'size'    => 'md',
                'lines'   => [
                    'Admin Name : ' . $admin['full_name'],
                    'Status : ' . ($isActive ? 'Active' : 'Inactive'),
                    $admin['email'],
                ],
                'actions' => [$isActive
                    ? ['label' => 'Deactivate', 'variant' => 'danger', 'api' => 'api/manager/admins/' . $admin['id'] . '/status',
                        'body' => ['status' => 'inactive'], 'confirm' => 'Deactivate ' . $admin['full_name'] . '? They will be logged out.']
                    : ['label' => 'Activate', 'api' => 'api/manager/admins/' . $admin['id'] . '/status',
                        'body' => ['status' => 'active']],
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>

    <a class="btn btn--primary create-admin-button" href="<?= e(url('manager/admins/create')) ?>">+ Create New Admin</a>
</section>
