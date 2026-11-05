<?php
/**
 * Phase 1 component preview (debug mode only).
 * The top half rebuilds the "Request for Help" screen with the reusable
 * components so it can be compared with the Figma export.
 * The bottom half shows the remaining components and two system checks.
 */
$disasterOptions = array_map(
    static fn (array $type) => ['value' => $type['id'], 'label' => $type['name']],
    $disasterTypes
);
?>
<?php component('page-header', ['backHref' => '', 'title' => 'Request for Help']); ?>

<form class="form content-column" data-validate data-demo-form novalidate>
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
        <?php component('form-field', [
            'name' => 'location', 'label' => 'Location:', 'placeholder' => 'Enter exact location',
            'required' => true, 'center' => true, 'attributes' => ['maxlength' => 255],
        ]); ?>
        <?php component('form-field', [
            'name' => 'contact_phone', 'label' => 'Phone Number :', 'type' => 'tel',
            'placeholder' => 'Enter  Contract Number', 'required' => true, 'center' => true,
            'attributes' => ['data-rule' => 'phone', 'inputmode' => 'tel'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'description', 'label' => 'Description:', 'type' => 'textarea',
            'placeholder' => 'Type what you need...', 'required' => true,
            'attributes' => ['maxlength' => 1000],
        ]); ?>
    </div>

    <button class="btn btn--primary btn--text-dark form__submit" type="submit">SUBMIT REQUEST</button>
</form>

<hr class="dev-divider">

<section class="content-column dev-section" aria-labelledby="dev-other-heading">
    <h2 class="dev-section__title" id="dev-other-heading">Other components</h2>

    <?php component('tab-nav', ['label' => 'Example menu', 'items' => [
        ['label' => 'Home', 'href' => 'dev/components', 'active' => true, 'width' => 57],
        ['label' => 'My Donations', 'href' => 'dev/components', 'width' => 113],
        ['label' => 'Profile', 'href' => 'dev/components', 'width' => 69],
    ]]); ?>

    <p class="info-heading">Approved Requests:</p>
    <?php component('card', [
        'lines'   => ['Flood  - Example', 'Urgency: High'],
        'actions' => [['label' => 'Donate Now', 'href' => 'dev/components']],
    ]); ?>
    <?php component('card', [
        'size'    => 'md',
        'lines'   => ['Urgent : Example item', 'Location : Example', 'Details : Example text'],
        'actions' => [
            ['label' => '✔ Approve', 'href' => 'dev/components'],
            ['label' => '❌ Reject', 'variant' => 'danger', 'href' => 'dev/components'],
        ],
    ]); ?>

    <dl class="detail-list">
        <div class="detail-list__row"><dt>Donation ID :</dt><dd>#DON-000</dd></div>
        <div class="detail-list__row"><dt>Items :</dt><dd>Example items</dd></div>
    </dl>

    <?php component('form-field', [
        'name' => 'amount_bdt', 'label' => 'Enter Amount (BDT) :', 'type' => 'number',
        'size' => 'lg', 'boldLabel' => true, 'attributes' => ['min' => 1],
    ]); ?>

    <button class="btn btn--danger" type="button">Deactivate</button>

    <h2 class="dev-section__title">System checks</h2>
    <button class="btn btn--primary" type="button" data-health-check>Check database connection</button>
    <button class="btn btn--primary" type="button" data-csrf-check>Test CSRF-protected request</button>
    <button class="btn btn--danger" type="button" data-csrf-fail-check>Test request without CSRF token</button>
    <pre class="dev-output" data-dev-output aria-live="polite">Results appear here.</pre>
</section>
