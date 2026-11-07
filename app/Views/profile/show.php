<?php
/**
 * "Profile" / "My Profile" tab for every role.
 * Variables: $user, $tabs (empty for roles without tabs), $homePath, $flash
 */
?>
<?php component('page-header', ['backHref' => $homePath, 'title' => 'My Profile']); ?>

<section class="content-column screen--profile<?= $tabs !== [] ? ' screen--with-tabs' : '' ?>">
    <?php if ($tabs !== []): ?>
        <?php component('tab-nav', ['label' => 'Menu', 'items' => $tabs]); ?>
    <?php endif; ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <dl class="profile-summary">
        <div><dt>Role :</dt> <dd><?= e($user['role_name']) ?></dd></div>
        <div><dt>Email :</dt> <dd><?= e($user['email']) ?></dd></div>
        <div><dt>Member since :</dt> <dd><?= e(short_date($user['created_at'])) ?></dd></div>
    </dl>

    <form class="profile-form" data-api-form="api/profile" data-validate novalidate>
        <?php component('form-field', [
            'name' => 'full_name', 'label' => 'Full Name :', 'value' => $user['full_name'], 'required' => true,
            'attributes' => ['minlength' => 2, 'maxlength' => 100, 'autocomplete' => 'name'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'phone', 'label' => 'Phone Number :', 'type' => 'tel', 'value' => $user['phone'], 'required' => true,
            'attributes' => ['data-rule' => 'phone', 'inputmode' => 'tel', 'maxlength' => 14, 'autocomplete' => 'tel'],
        ]); ?>
        <button class="btn btn--primary profile-form__submit" type="submit">Save Changes</button>
    </form>

    <form class="profile-form" data-api-form="api/profile/password" data-validate novalidate>
        <h2 class="info-heading info-heading--bold">Change Password:</h2>
        <?php component('form-field', [
            'name' => 'current_password', 'label' => 'Current Password :', 'type' => 'password', 'required' => true,
            'attributes' => ['maxlength' => 72, 'autocomplete' => 'current-password'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'password', 'label' => 'New Password :', 'type' => 'password', 'required' => true,
            'attributes' => ['minlength' => 8, 'maxlength' => 72, 'data-rule' => 'password', 'autocomplete' => 'new-password'],
        ]); ?>
        <button class="btn btn--primary profile-form__submit" type="submit">Change Password</button>
    </form>

    <?php component('logout-button'); ?>
</section>
