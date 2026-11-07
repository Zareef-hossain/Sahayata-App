<?php
/**
 * Volunteer — Figma "Delivery" (Delivery Progress).
 * The Figma "Select Status" box (blue bar + white list) is built from radio buttons.
 * Variables: $delivery, $nextStatuses, $flash
 */
use App\Models\Delivery;
?>
<?php component('page-header', ['backHref' => 'volunteer/tasks', 'title' => 'Delivery Progress', 'titleGap' => 25]); ?>

<section class="content-column screen--delivery">
    <dl class="detail-list">
        <div class="detail-list__row"><dt>Task ID :</dt> <dd><?= e(ref('DEL', $delivery['id'])) ?></dd></div>
        <div class="detail-list__row"><dt>Victim :</dt> <dd><?= e($delivery['victim_name']) ?></dd></div>
        <div class="detail-list__row"><dt>Address :</dt> <dd><?= e($delivery['location']) ?></dd></div>
    </dl>
    <p class="delivery-extra">
        Items: <?= e(Delivery::itemsText($delivery)) ?> · Contact:
        <a href="tel:<?= e($delivery['contact_phone']) ?>"><?= e($delivery['contact_phone']) ?></a><br>
        Current status: <strong><?= e(Delivery::STATUS_LABELS[$delivery['status']]) ?></strong>
    </p>

    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php if ($nextStatuses === []): ?>
        <p class="empty-state section-gap">This delivery is complete. Thank you!</p>
    <?php else: ?>
        <form class="status-form" data-api-form="api/volunteer/deliveries/<?= e($delivery['id']) ?>/status" data-validate novalidate>
            <fieldset class="status-picker" data-field="status">
                <legend class="info-heading info-heading--bold status-picker__heading">Update Current Status :</legend>
                <p class="status-picker__bar" aria-hidden="true">Select Status</p>
                <ul class="status-picker__list">
                    <?php foreach ($nextStatuses as $value => $label): ?>
                        <li>
                            <input class="status-picker__input" type="radio" id="status-<?= e($value) ?>" name="status"
                                   value="<?= e($value) ?>" required aria-describedby="error-status">
                            <label class="status-picker__option" for="status-<?= e($value) ?>"><?= e($label) ?></label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="field__error" id="error-status" role="alert"></p>
            </fieldset>
            <button class="btn btn--primary status-form__submit" type="submit">Update Status</button>
        </form>
    <?php endif; ?>
</section>
