<?php
/**
 * Manager — "+ Create New Admin" form (same components as Sign Up).
 * Variables: $flash
 */
?>
<?php component('page-header', ['backHref' => 'manager/dashboard', 'title' => 'Create New Admin']); ?>

<form class="form content-column" data-api-form="api/manager/admins" data-validate novalidate>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <div class="form__fields form__fields--flush">
        <?php component('form-field', [
            'name' => 'full_name', 'label' => 'Full Name :', 'required' => true,
            'attributes' => ['minlength' => 2, 'maxlength' => 100, 'autocomplete' => 'off'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'email', 'label' => 'Email Address :', 'type' => 'email', 'required' => true,
            'attributes' => ['maxlength' => 150, 'autocomplete' => 'off'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'phone', 'label' => 'Phone Number :', 'type' => 'tel', 'required' => true,
            'attributes' => ['data-rule' => 'phone', 'inputmode' => 'tel', 'maxlength' => 14],
        ]); ?>
        <?php component('form-field', [
            'name' => 'password', 'label' => 'Password:', 'type' => 'password', 'required' => true,
            'attributes' => ['minlength' => 8, 'maxlength' => 72, 'data-rule' => 'password', 'autocomplete' => 'new-password'],
        ]); ?>
    </div>

    <button class="btn btn--primary form__submit" type="submit">Create Admin</button>
</form>
