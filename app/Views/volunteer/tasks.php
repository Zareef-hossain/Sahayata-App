<?php
/**
 * Volunteer — Figma "Vulunteer task" (Volunteer Tasks).
 * Variables: $tabs, $availability, $pending, $active, $flash
 */
use App\Models\Delivery;

$isAvailable = $availability === 'available';
?>
<?php component('page-header', ['backLogout' => true, 'title' => 'Volunteer Tasks', 'titleGap' => 31]); ?>

<section class="content-column screen--with-tabs screen--volunteer">
    <?php component('tab-nav', ['label' => 'Volunteer menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <div class="status-row">
        <span class="status-row__label">Status :</span>
        <button class="btn btn--primary status-row__button" type="button"
                data-api-action="api/volunteer/availability"
                aria-label="Your status is <?= $isAvailable ? 'Available' : 'Busy' ?>. Press to change it.">
            <?= $isAvailable ? 'Available' : 'Busy' ?>
        </button>
    </div>

    <h2 class="info-heading list-heading pending-heading">Pending Deliveries near you:</h2>
    <?php if ($pending === []): ?>
        <p class="empty-state">No deliveries are waiting for a volunteer.</p>
    <?php elseif (!$isAvailable): ?>
        <p class="empty-state">Set your status to Available to accept a task.</p>
    <?php endif; ?>
    <div class="card-list">
        <?php foreach ($pending as $delivery): ?>
            <?php component('card', [
                'lines'   => [
                    Delivery::itemsText($delivery) . ' → ' . $delivery['location'],
                    'From : ' . ($delivery['donor_name'] ?? 'Admin'),
                ],
                'actions' => $isAvailable ? [[
                    'label' => 'Accept Task',
                    'api'   => 'api/volunteer/deliveries/' . $delivery['id'] . '/accept',
                ]] : [],
            ]); ?>
        <?php endforeach; ?>
    </div>

    <h2 class="info-heading list-heading section-gap">My Active Tasks:</h2>
    <?php if ($active === []): ?>
        <p class="empty-state">You have no active deliveries.</p>
    <?php endif; ?>
    <div class="card-list">
        <?php foreach ($active as $delivery): ?>
            <?php component('card', [
                'lines'   => [
                    ref('DEL', $delivery['id']) . ' · ' . Delivery::itemsText($delivery) . ' → ' . $delivery['location'],
                    'Status : ' . Delivery::STATUS_LABELS[$delivery['status']],
                ],
                'actions' => [['label' => 'Update Progress', 'href' => 'volunteer/deliveries/' . $delivery['id']]],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
