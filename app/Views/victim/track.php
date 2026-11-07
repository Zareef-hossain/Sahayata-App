<?php
/**
 * Victim — Figma "Track Request".
 * Variables: $requests (all of this victim's requests), $request (selected or null),
 *            $history, $delivery, $reliefPoint, $flash
 */
use App\Models\HelpRequest;
?>
<?php component('page-header', ['backHref' => 'victim/request-help', 'title' => 'Track My Request']); ?>

<section class="content-column screen--track">
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php if ($request === null): ?>
        <p class="empty-state">You have not requested help yet.</p>
        <a class="btn btn--primary btn--text-dark" href="<?= e(url('victim/request-help')) ?>">Request Help</a>
    <?php else: ?>
        <?php component('osm-map', [
            'latitude'  => $request['latitude'],
            'longitude' => $request['longitude'],
            'label'     => 'Map of your request location',
        ]); ?>

        <div class="track-block track-block--relief">
            <h2 class="info-heading info-heading--bold">🏥 Nearest Relief Point</h2>
            <?php if ($reliefPoint !== null): ?>
                <p><?= e($reliefPoint['name']) ?> — about <?= e(number_format($reliefPoint['distance_km'], 1)) ?> km away</p>
            <?php else: ?>
                <p>Use "📍 My Location" when requesting help to see the nearest relief point.</p>
            <?php endif; ?>
        </div>

        <div class="track-block">
            <p class="track-summary">
                <?= e(ref('REQ', $request['id'])) ?> · <?= e($request['disaster_type']) ?> · <?= e($request['location']) ?>
                · Urgency: <?= e(HelpRequest::URGENCY_LABELS[$request['urgency']]) ?>
            </p>
            <?php component('status-timeline', ['status' => $request['status'], 'history' => $history]); ?>
        </div>

        <div class="track-block volunteer-info">
            <h2 class="info-heading info-heading--bold">Volunteer Info:</h2>
            <?php if ($delivery !== null && $delivery['volunteer_name'] !== null): ?>
                <p>👤 Name: <?= e($delivery['volunteer_name']) ?></p>
                <p>📞 Contact: <a href="tel:<?= e($delivery['volunteer_phone']) ?>"><?= e($delivery['volunteer_phone']) ?></a></p>
            <?php else: ?>
                <p>👤 No volunteer assigned yet.</p>
            <?php endif; ?>
        </div>

        <?php if (count($requests) > 1): ?>
            <div class="track-block">
                <h2 class="info-heading info-heading--bold">My Requests:</h2>
                <ul class="request-list">
                    <?php foreach ($requests as $item): ?>
                        <li>
                            <a class="text-link<?= (int) $item['id'] === (int) $request['id'] ? ' is-current' : '' ?>"
                               href="<?= e(url('victim/track?request=' . $item['id'])) ?>">
                                <?= e(ref('REQ', $item['id'])) ?> · <?= e($item['disaster_type']) ?> · <?= e(HelpRequest::STATUS_LABELS[$item['status']]) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
