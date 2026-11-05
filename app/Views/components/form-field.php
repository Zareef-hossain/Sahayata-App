<?php
/**
 * One labelled form field: text input, textarea or select.
 *
 * Props:
 *   $name         string   Field name
 *   $label        string   Text above the field, e.g. 'Phone Number :'
 *   $type         string   'text' | 'email' | 'tel' | 'password' | 'number' | 'textarea' | 'select'
 *   $value        mixed    Current value
 *   $placeholder  ?string
 *   $required     bool
 *   $options      array    For selects: [['value' => 1, 'label' => 'Flood'], ...]
 *   $error        ?string  Validation message
 *   $size         string   'md' (20px high, default) | 'lg' (43px, Make Donation screen)
 *   $boldLabel    bool     Bold label text (Make Donation screen)
 *   $center       bool     Centre the text inside the field (Request for Help screen)
 *   $attributes   array    Extra HTML attributes, e.g. ['minlength' => 8, 'data-rule' => 'phone']
 */
$type ??= 'text';
$value ??= '';
$placeholder ??= null;
$required ??= false;
$options ??= [];
$error ??= null;
$size ??= 'md';
$boldLabel ??= false;
$center ??= false;
$attributes ??= [];

$id = 'field-' . $name;
$errorId = 'error-' . $name;

$controlClasses = ['field__control', 'field__control--' . $size];
if ($center) {
    $controlClasses[] = 'field__control--center';
}
if ($error) {
    $controlClasses[] = 'is-invalid';
}

// Build the shared HTML attributes once, escaping every value.
$attributeList = [
    'id'               => $id,
    'name'             => $name,
    'class'            => implode(' ', $controlClasses),
    'aria-describedby' => $errorId,
    'aria-invalid'     => $error ? 'true' : null,
    'placeholder'      => $type === 'select' ? null : $placeholder,
    'required'         => $required ? true : null,
    ...$attributes,
];
$attributeHtml = '';
foreach ($attributeList as $attribute => $attributeValue) {
    if ($attributeValue === null || $attributeValue === false) {
        continue;
    }
    $attributeHtml .= $attributeValue === true
        ? ' ' . e($attribute)
        : ' ' . e($attribute) . '="' . e($attributeValue) . '"';
}
?>
<div class="field" data-field="<?= e($name) ?>">
    <label class="field__label<?= $boldLabel ? ' field__label--bold' : '' ?>" for="<?= e($id) ?>"><?= e($label ?? '') ?></label>

    <?php if ($type === 'textarea'): ?>
        <textarea<?= $attributeHtml ?>><?= e($value) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select<?= $attributeHtml ?>>
            <option value=""><?= e($placeholder ?? 'Select') ?></option>
            <?php foreach ($options as $option): ?>
                <option value="<?= e($option['value']) ?>" <?= (string) $value === (string) $option['value'] ? 'selected' : '' ?>><?= e($option['label']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input type="<?= e($type) ?>" value="<?= e($value) ?>"<?= $attributeHtml ?>>
    <?php endif; ?>

    <p class="field__error" id="<?= e($errorId) ?>" role="alert"><?= e($error ?? '') ?></p>
</div>
