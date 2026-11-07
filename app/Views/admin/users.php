<?php
/**
 * Admin — "Users" tab. Variables: $tabs, $users, $flash
 */
$statusLabels = ['pending' => 'Not verified', 'active' => 'Active', 'inactive' => 'Inactive'];
?>
<?php component('page-header', ['backHref' => 'admin/dashboard', 'title' => 'Users', 'titleGap' => 36]); ?>

<section class="content-column screen--with-tabs screen--admin">
    <?php component('tab-nav', ['label' => 'Admin menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">Registered Users (<?= count($users) ?>):</h2>
    <?php if ($users === []): ?>
        <p class="empty-state">No users have registered yet.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($users as $user): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    '👤 ' . $user['full_name'] . ' (' . $user['role_name'] . ')',
                    $user['email'],
                    '📞 ' . $user['phone'] . ' · ' . $statusLabels[$user['status']],
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
