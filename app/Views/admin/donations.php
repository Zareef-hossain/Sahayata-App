<?php
/**
 * Admin — "Donations" tab. Variables: $tabs, $donations, $totals, $flash
 */
?>
<?php component('page-header', ['backHref' => 'admin/dashboard', 'title' => 'Donations', 'titleGap' => 36]); ?>

<section class="content-column screen--with-tabs screen--admin">
    <?php component('tab-nav', ['label' => 'Admin menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">
        Donations: <?= e($totals['count']) ?> · Total: <?= e(money($totals['total'])) ?>
    </h2>
    <p class="demo-note demo-note--left">All payments are demo records; no real money was transferred.</p>
    <?php if ($donations === []): ?>
        <p class="empty-state">No donations yet.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($donations as $donation): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    ref('DON', $donation['id']) . ' · ' . $donation['donation_type'] . ' · ' . money($donation['amount_bdt']),
                    'Donor : ' . $donation['donor_name'],
                    'For : ' . ($donation['help_request_id']
                        ? ref('REQ', $donation['help_request_id']) . ' ' . $donation['request_disaster'] . ' - ' . $donation['request_location']
                        : 'General relief fund'),
                    $donation['payment_method'] . ' · ' . $donation['demo_reference'] . ' · ' . short_date($donation['created_at']),
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
