<?php
/**
 * Victim — Figma "Request Help".
 * Variables: $disasterTypes, $flash
 */
$disasterOptions = array_map(
    static fn (array $type) => ['value' => $type['id'], 'label' => $type['name']],
    $disasterTypes
);
?>
<?php component('page-header', ['backLogout' => true, 'title' => 'Request for Help']); ?>

<form class="form content-column screen--request-help" data-api-form="api/victim/requests" data-validate novalidate>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php component('option-group', [
        'name'     => 'urgency',
        'legend'   => 'Urgency Level:',
        'required' => true,
        'options'  => [
            ['value' => 'low',    'label' => 'Low',    'variant' => 'low'],
            ['value' => 'medium', 'label' => 'Medium', 'variant' => 'medium'],
            ['value' => 'high',   'label' => 'High',   'variant' => 'high', 'width' => 84],
        ],
    ]); ?>

    <div class="form__fields">
        <?php component('form-field', [
            'name' => 'disaster_type_id', 'label' => 'Disaster Type:', 'type' => 'select',
            'placeholder' => 'Select (Flood, Fire, etc.)', 'options' => $disasterOptions,
            'required' => true, 'center' => true,
        ]); ?>

        <div class="location-field">
            <?php component('form-field', [
                'name' => 'location', 'label' => 'Location:', 'placeholder' => 'Enter exact location',
                'required' => true, 'center' => true, 'attributes' => ['minlength' => 3, 'maxlength' => 255],
            ]); ?>
            <!-- Figma text "📍 My Location": fills the hidden coordinates using the browser's location. -->
            <button class="text-link location-field__button" type="button" data-my-location>📍 My Location</button>
            <input type="hidden" name="latitude" value="">
            <input type="hidden" name="longitude" value="">
            <p class="location-field__status" data-location-status aria-live="polite"></p>
        </div>

        <?php component('form-field', [
            'name' => 'contact_phone', 'label' => 'Phone Number :', 'type' => 'tel',
            'placeholder' => 'Enter Contact Number', 'required' => true, 'center' => true,
            'attributes' => ['data-rule' => 'phone', 'inputmode' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 14],
        ]); ?>
        <?php component('form-field', [
            'name' => 'description', 'label' => 'Description:', 'type' => 'textarea',
            'placeholder' => 'Type what you need...', 'required' => true,
            'attributes' => ['minlength' => 5, 'maxlength' => 1000],
        ]); ?>
    </div>

    <button class="btn btn--primary btn--text-dark form__submit" type="submit">SUBMIT REQUEST</button>

    <nav class="link-row" aria-label="Victim menu">
        <a class="text-link" href="<?= e(url('victim/track')) ?>">Track My Request</a>
        <a class="text-link" href="<?= e(url('profile')) ?>">My Profile</a>
    </nav>
</form>
