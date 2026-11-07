<?php
/**
 * Request progress, based on the Figma text "🟡 Pending → 🔵 Assigned → 🟢 Done".
 * Finished steps are green, the current step blue (bold), later steps yellow.
 *
 * Props:
 *   $status   string  Current help request status
 *   $history  array   Rows from HelpRequest::history() (to show dates)
 */

$steps = ['pending', 'approved', 'assigned', 'picked_up', 'in_transit', 'delivered'];
$status ??= 'pending';
$dates = [];
foreach ($history ?? [] as $entry) {
    $dates[$entry['status']] = $entry['created_at'];
}
$currentIndex = array_search($status, $steps, true);
?>
<section class="timeline" aria-labelledby="timeline-heading">
    <h2 class="info-heading info-heading--bold" id="timeline-heading">Delivery Status:</h2>

    <?php if ($status === 'rejected'): ?>
        <p class="timeline__rejected">🔴 Rejected<?= isset($dates['rejected']) ? ' — ' . e(short_date($dates['rejected'])) : '' ?></p>
        <p class="timeline__note">An admin could not approve this request. You can submit a new request with more details.</p>
    <?php else: ?>
        <ol class="timeline__steps">
            <?php foreach ($steps as $index => $step): ?>
                <?php
                $isFinished = $index < $currentIndex || $status === 'delivered';
                $state = $isFinished ? 'done' : ($index === $currentIndex ? 'current' : 'upcoming');
                $marker = ['done' => '🟢', 'current' => '🔵', 'upcoming' => '🟡'][$state];
                ?>
                <li class="timeline__step timeline__step--<?= $state ?>" <?= $state === 'current' ? 'aria-current="step"' : '' ?>>
                    <span aria-hidden="true"><?= $marker ?></span>
                    <?= e(App\Models\HelpRequest::STATUS_LABELS[$step]) ?>
                    <?php if (isset($dates[$step])): ?><span class="timeline__date">(<?= e(short_date($dates[$step])) ?>)</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
