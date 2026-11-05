<?php
/**
 * Logout button. Logging out changes data, so it is a POST form with a CSRF token
 * (a plain link could be triggered by another website).
 */
?>
<form class="logout-form" method="post" action="<?= e(url('logout')) ?>">
    <?= csrf_field() ?>
    <button class="btn btn--danger" type="submit">Logout</button>
</form>
