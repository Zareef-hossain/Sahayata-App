<?php
/**
 * Donor — Figma "Donation" (Make Donation). DEMO payment only.
 * Variables: $helpRequest (?array), $donationTypes, $paymentMethods, $flash
 */
$toOptions = static fn (array $rows) => array_map(
    static fn (array $row) => ['value' => $row['id'], 'label' => $row['name']],
    $rows
);
?>
<?php component('page-header', ['backHref' => 'donor/dashboard', 'title' => 'Make Donation']); ?>

<form class="content-column screen--donate" data-api-form="api/donor/donations" data-validate novalidate>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php if ($helpRequest !== null): ?>
        <input type="hidden" name="help_request_id" value="<?= e($helpRequest['id']) ?>">
        <p class="donate-for">For: <?= e($helpRequest['disaster_type']) ?> - <?= e($helpRequest['location']) ?> (<?= e(ref('REQ', $helpRequest['id'])) ?>)</p>
    <?php endif; ?>

    <div class="donate-fields">
        <?php component('form-field', [
            'name' => 'donation_type_id', 'label' => 'Donation Type :', 'type' => 'select',
            'placeholder' => 'Select donation type', 'options' => $toOptions($donationTypes),
            'required' => true, 'size' => 'lg', 'boldLabel' => true,
        ]); ?>
        <?php component('form-field', [
            'name' => 'amount_bdt', 'label' => 'Enter Amount (BDT) :', 'type' => 'number',
            'required' => true, 'size' => 'lg', 'boldLabel' => true,
            'attributes' => ['min' => 1, 'max' => App\Models\Donation::MAX_AMOUNT, 'step' => '0.01', 'inputmode' => 'decimal'],
        ]); ?>
        <?php component('form-field', [
            'name' => 'payment_method_id', 'label' => 'Payment Method :', 'type' => 'select',
            'placeholder' => 'Select payment method', 'options' => $toOptions($paymentMethods),
            'required' => true, 'size' => 'xl', 'boldLabel' => true,
        ]); ?>
        <?php component('form-field', [
            'name' => 'payer_mobile', 'label' => 'Enter Mobile Number :', 'type' => 'tel',
            'required' => true, 'size' => 'sm', 'boldLabel' => true,
            'attributes' => ['data-rule' => 'phone', 'inputmode' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 14],
        ]); ?>
    </div>

    <button class="btn btn--primary donate-submit" type="submit">Confirm Payment</button>
    <p class="demo-note">Demo payment: the donation is recorded, but no real bKash, Nagad, Rocket or card payment is made.</p>
</form>
