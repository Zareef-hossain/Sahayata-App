<?php
/**
 * Row of choice buttons where only one can be selected
 * (Choose Login Type, Select Role, Urgency Level). Built from radio inputs,
 * so it works with the keyboard and without JavaScript.
 *
 * Props:
 *   $name      string  Form field name, e.g. 'urgency'
 *   $legend    string  Text above the buttons, e.g. 'Urgency Level:'
 *   $options   array   [['value' => 'low', 'label' => 'Low', 'variant' => 'low', 'width' => 91], ...]
 *                       'variant' (optional): low | medium | high colour; 'width' (optional) in px
 *   $selected  ?string Currently selected value
 *   $required  bool
 *   $error     ?string Validation message
 *   $modifier  ?string Extra layout class, e.g. 'login' -> option-group--login
 *                       option 'height' (px) is also optional
 */
$name ??= 'option';
$selected ??= null;
$required ??= false;
$error ??= null;
$modifier ??= null;
$errorId = 'error-' . $name;
?>
<fieldset class="option-group<?= $modifier ? ' option-group--' . e($modifier) : '' ?>" data-field="<?= e($name) ?>">
    <legend class="option-group__legend"><?= e($legend ?? '') ?></legend>
    <div class="option-group__items">
        <?php foreach ($options ?? [] as $index => $option): ?>
            <?php $inputId = $name . '-' . $option['value']; ?>
            <input class="option-group__input" type="radio"
                   id="<?= e($inputId) ?>" name="<?= e($name) ?>" value="<?= e($option['value']) ?>"
                   <?= (string) $selected === (string) $option['value'] ? 'checked' : '' ?>
                   <?= $required && $index === 0 ? 'required' : '' ?>
                   aria-describedby="<?= e($errorId) ?>">
            <label class="option-group__item<?= isset($option['variant']) ? ' option-group__item--' . e($option['variant']) : '' ?>"
                   for="<?= e($inputId) ?>"
                   <?php
                   $sizeStyle = (isset($option['width']) ? 'width: ' . (int) $option['width'] . 'px;' : '')
                       . (isset($option['height']) ? ' height: ' . (int) $option['height'] . 'px;' : '');
                   ?>
                   <?= $sizeStyle !== '' ? 'style="' . e(trim($sizeStyle)) . '"' : '' ?>><?= e($option['label']) ?></label>
        <?php endforeach; ?>
    </div>
    <p class="field__error" id="<?= e($errorId) ?>" role="alert"><?= e($error ?? '') ?></p>
</fieldset>
