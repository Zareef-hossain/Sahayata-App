<?php
/**
 * One-time messages ("Request submitted", "Invalid password", ...).
 * Props: $messages array [['type' => 'success'|'error', 'message' => '...'], ...]
 *
 * Printed without whitespace inside the <div> so CSS :empty hides it
 * (and it takes no space) when there are no messages.
 */
$items = '';
foreach ($messages ?? [] as $flashMessage) {
    $items .= '<p class="flash flash--' . e($flashMessage['type']) . '">' . e($flashMessage['message']) . '</p>';
}
?>
<div class="flash-area" data-flash-area aria-live="polite"><?= $items ?></div>
