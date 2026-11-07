<?php
/**
 * Admin — Figma "Assign Delivery".
 * Variables: $request, $delivery (?array), $donation (?array), $volunteers, $canAssign, $flash
 */
use App\Models\Delivery;
use App\Models\HelpRequest;

$items = $delivery !== null
    ? Delivery::itemsText($delivery)
    : ($donation !== null ? $donation['donation_type'] . ' (' . money($donation['amount_bdt']) . ')' : 'Relief for ' . $request['disaster_type']);
?>
<?php component('page-header', ['backHref' => 'admin/dashboard', 'title' => 'Assign Delivery', 'titleGap' => 25]); ?>

<section class="content-column screen--assign">
    <dl class="detail-list">
        <div class="detail-list__row"><dt>Request ID :</dt> <dd><?= e(ref('REQ', $request['id'])) ?></dd></div>
        <div class="detail-list__row"><dt>Donation ID :</dt> <dd><?= $donation !== null ? e(ref('DON', $donation['id'])) : 'None yet' ?></dd></div>
        <div class="detail-list__row"><dt>Items :</dt> <dd><?= e($items) ?></dd></div>
        <div class="detail-list__row"><dt>Target Location :</dt> <dd><?= e($request['location']) ?></dd></div>
    </dl>

    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php if (!$canAssign): ?>
        <p class="empty-state section-gap">
            This request is <?= e(HelpRequest::STATUS_LABELS[$request['status']]) ?> and cannot be assigned now.
        </p>
    <?php else: ?>
        <?php if ($delivery !== null && $delivery['volunteer_name'] !== null): ?>
            <p class="empty-state section-gap">Currently assigned to <?= e($delivery['volunteer_name']) ?>. Choose another volunteer to reassign.</p>
        <?php endif; ?>

        <h2 class="info-heading info-heading--bold list-heading volunteers-heading">Available Volunteers :</h2>
        <?php if ($volunteers === []): ?>
            <p class="empty-state">There are no volunteer accounts yet.</p>
        <?php endif; ?>
        <div class="card-list card-list--assign">
            <?php foreach ($volunteers as $volunteer): ?>
                <?php $isAvailable = $volunteer['availability'] === 'available'; ?>
                <?php component('card', [
                    'size'    => 'md',
                    'lines'   => [
                        '👤 ' . $volunteer['full_name'] . ' (' . ($isAvailable ? 'Available' : 'Busy') . ')',
                        '📞 ' . $volunteer['phone'],
                        'Active tasks : ' . $volunteer['active_tasks'],
                    ],
                    'actions' => $isAvailable ? [[
                        'label' => 'Assign to this Task',
                        'api'   => 'api/admin/requests/' . $request['id'] . '/assign',
                        'body'  => ['volunteer_id' => (int) $volunteer['id']],
                    ]] : [],
                ]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
