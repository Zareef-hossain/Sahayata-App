<?php
/**
 * Donor — "My Donations" tab (same design language as the dashboard).
 * Variables: $tabs, $donations, $totals, $flash
 */
?>
<?php component('page-header', ['backHref' => 'donor/dashboard', 'title' => 'My Donations']); ?>

<section class="content-column screen--with-tabs screen--donor">
    <?php component('tab-nav', ['label' => 'Donor menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">
        Donations: <?= e($totals['count']) ?> · Total: <?= e(money($totals['total'])) ?>
    </h2>

    <?php if ($donations === []): ?>
        <p class="empty-state">You have not made a donation yet.</p>
    <?php endif; ?>

    <div class="card-list card-list--compact">
        <?php foreach ($donations as $donation): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    ref('DON', $donation['id']) . ' · ' . $donation['donation_type'] . ' · ' . money($donation['amount_bdt']),
                    'For : ' . ($donation['help_request_id']
                        ? $donation['request_disaster'] . ' - ' . $donation['request_location']
                        : 'General relief fund'),
                    $donation['payment_method'] . ' · ' . $donation['demo_reference'],
                    short_date($donation['created_at']),
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
