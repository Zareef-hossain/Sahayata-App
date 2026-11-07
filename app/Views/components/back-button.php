<?php
/**
 * "< Back" button in the top-left corner.
 *
 * Props:
 *   $href    string  Where to go back to.
 *   $logout  bool    On a role's first screen there is nothing to go back to inside the
 *                    app, so "Back" logs out and returns to the login screen. Logging out
 *                    changes data, so this is a POST form with a CSRF token.
 */
$logout ??= false;
?>
<?php if ($logout): ?>
    <form class="back-button-form" method="post" action="<?= e(url('logout')) ?>">
        <?= csrf_field() ?>
        <button class="back-button" type="submit" title="Log out and return to the login screen">&lt; Back</button>
    </form>
<?php else: ?>
    <a class="back-button" href="<?= e(url($href ?? '')) ?>">&lt; Back</a>
<?php endif; ?>
