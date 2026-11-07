<?php
/**
 * Volunteer — "History" tab. Variables: $tabs, $deliveries, $flash
 */
use App\Models\Delivery;
?>
<?php component('page-header', ['backHref' => 'volunteer/tasks', 'title' => 'Delivery History', 'titleGap' => 31]); ?>

<section class="content-column screen--with-tabs screen--volunteer">
    <?php component('tab-nav', ['label' => 'Volunteer menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">Completed Deliveries (<?= count($deliveries) ?>):</h2>
    <?php if ($deliveries === []): ?>
        <p class="empty-state">You have not completed a delivery yet.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($deliveries as $delivery): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    ref('DEL', $delivery['id']) . ' · ' . Delivery::itemsText($delivery),
                    'To : ' . $delivery['victim_name'] . ', ' . $delivery['location'],
                    'Delivered : ' . short_date($delivery['delivered_at']),
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
