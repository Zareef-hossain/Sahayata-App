<?php
/**
 * TEMPORARY page after login, until the role's real screens are built.
 * Variables: $user, $roleName, $phase
 */
?>
<?php component('page-header', ['title' => 'Welcome, ' . $roleName . '!']); ?>

<section class="content-column placeholder-page">
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <p class="placeholder-page__text">Logged in as <strong><?= e($user['full_name']) ?></strong> (<?= e($user['email']) ?>).</p>
    <p class="placeholder-page__text">The <?= e($roleName) ?> screens are built in <?= e($phase) ?>.</p>

    <?php component('logout-button'); ?>
</section>
