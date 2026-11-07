<?php
/**
 * Donor — Figma "Donator" (dashboard).
 * Variables: $tabs, $requests (approved requests open for donations), $flash
 */
use App\Models\HelpRequest;
?>
<?php component('page-header', ['backLogout' => true, 'title' => 'Welcome, Donor!', 'icon' => '❤']); ?>

<section class="content-column screen--with-tabs screen--donor">
    <?php component('tab-nav', ['label' => 'Donor menu', 'items' => $tabs]); ?>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h2 class="info-heading list-heading">Approved Requests:</h2>

    <?php if ($requests === []): ?>
        <p class="empty-state">There are no approved requests right now. Please check again later.</p>
    <?php endif; ?>

    <div class="card-list">
        <?php foreach ($requests as $request): ?>
            <?php component('card', [
                'lines'   => [
                    $request['disaster_type'] . '  - ' . $request['location'],
                    'Urgency: ' . HelpRequest::URGENCY_LABELS[$request['urgency']],
                ],
                'actions' => [['label' => 'Donate Now', 'href' => 'donor/donate?request=' . $request['id']]],
            ]); ?>
        <?php endforeach; ?>
    </div>
</section>
