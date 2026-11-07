<?php
/**
 * Admin — Figma "Request approvals" (Dashboard tab).
 * Variables: $tabs, $pending, $approved, $deliveries, $flash
 */
use App\Models\Delivery;
use App\Models\HelpRequest;
?>
<?php component('page-header', ['backLogout' => true, 'title' => 'Request Approvals', 'titleGap' => 36]); ?>

<section class="content-column screen--with-tabs screen--admin">
    <?php component('tab-nav', ['label' => 'Admin menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">Pending Victim Requests:</h2>
    <?php if ($pending === []): ?>
        <p class="empty-state">No requests are waiting for approval.</p>
    <?php endif; ?>
    <div class="card-list">
        <?php foreach ($pending as $request): ?>
            <?php component('card', [
                'size'    => 'md',
                'lines'   => [
                    ref('REQ', $request['id']) . ' · ' . HelpRequest::URGENCY_LABELS[$request['urgency']] . ' : ' . $request['disaster_type'],
                    'Location : ' . $request['location'],
                    'Details : ' . $request['description'],
                ],
                'actions' => [
                    ['label' => '✔ Approve', 'api' => 'api/admin/requests/' . $request['id'] . '/approve'],
                    ['label' => '❌ Reject', 'variant' => 'danger', 'api' => 'api/admin/requests/' . $request['id'] . '/reject',
                        'confirm' => 'Reject request ' . ref('REQ', $request['id']) . '?'],
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>

    <h2 class="info-heading list-heading section-gap">Approved Requests (assign delivery):</h2>
    <?php if ($approved === []): ?>
        <p class="empty-state">No approved requests are waiting for a volunteer.</p>
    <?php endif; ?>
    <div class="card-list">
        <?php foreach ($approved as $request): ?>
            <?php component('card', [
                'lines'   => [
                    ref('REQ', $request['id']) . ' · ' . $request['disaster_type'] . ' - ' . $request['location'],
                    'Status : ' . HelpRequest::STATUS_LABELS[$request['status']]
                        . ' · Urgency : ' . HelpRequest::URGENCY_LABELS[$request['urgency']],
                ],
                'actions' => [['label' => 'Assign Delivery', 'href' => 'admin/requests/' . $request['id'] . '/assign']],
            ]); ?>
        <?php endforeach; ?>
    </div>

    <h2 class="info-heading list-heading section-gap">Delivery Assignments:</h2>
    <?php if ($deliveries === []): ?>
        <p class="empty-state">No deliveries yet. A delivery is created when a donor donates or an admin assigns a volunteer.</p>
    <?php endif; ?>
    <div class="card-list card-list--compact">
        <?php foreach ($deliveries as $delivery): ?>
            <?php component('card', [
                'size'  => 'md',
                'lines' => [
                    ref('DEL', $delivery['id']) . ' · ' . Delivery::itemsText($delivery) . ' → ' . $delivery['location'],
                    'Volunteer : ' . ($delivery['volunteer_name'] ?? 'Not assigned'),
                    'Status : ' . Delivery::STATUS_LABELS[$delivery['status']],
                ],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
