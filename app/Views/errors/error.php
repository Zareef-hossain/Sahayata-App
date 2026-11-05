<?php
/**
 * Error page (404, 403, 419, 500). Uses the same header as the other screens.
 * Variables: $status, $message, $details (only shown in debug mode)
 */
?>
<?php component('page-header', ['backHref' => '', 'title' => 'Error ' . $status]); ?>

<section class="content-column error-page">
    <p class="error-page__message"><?= e($message) ?></p>
    <?php if (!empty($details)): ?>
        <pre class="error-page__details"><?= e($details) ?></pre>
    <?php endif; ?>
</section>
