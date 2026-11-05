<?php
/**
 * "< Back" button in the top-left corner.
 * Props: $href string — where to go back to.
 */
?>
<a class="back-button" href="<?= e(url($href ?? '')) ?>">&lt; Back</a>
