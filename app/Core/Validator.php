<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Server-side form validation. The same rules are mirrored in
 * public/assets/js/modules/validation.js for instant feedback in the browser.
 *
 *   $errors = Validator::validate($data, [
 *       'email'    => 'required|email|max:150',
 *       'phone'    => 'required|phone',
 *       'urgency'  => 'required|in:low,medium,high',
 *   ]);
 *   // $errors = ['email' => 'Please enter a valid email address.'] or [] when valid
 */
final class Validator
{
    public static function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleList) {
            $value = $data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
            $isEmpty = $value === null || $value === '' || $value === [];

            foreach (explode('|', $ruleList) as $rule) {
                [$name, $argument] = array_pad(explode(':', $rule, 2), 2, '');

                if ($isEmpty && $name !== 'required') {
                    continue; // optional and empty: skip the other rules
                }

                $error = self::check($name, $argument, $value, $label, $data, $isEmpty);
                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // one message per field is enough
                }
            }
        }

        return $errors;
    }

    private static function check(string $rule, string $argument, mixed $value, string $label, array $data, bool $isEmpty): ?string
    {
        $text = is_scalar($value) ? (string) $value : '';

        return match ($rule) {
            'required' => $isEmpty ? "{$label} is required." : null,
            'email'    => filter_var($text, FILTER_VALIDATE_EMAIL) ? null : 'Please enter a valid email address.',
            'phone'    => self::isPhone($text) ? null : 'Please enter a valid Bangladeshi mobile number (e.g. 017XXXXXXXX).',
            'min'      => mb_strlen($text) >= (int) $argument ? null : "{$label} must be at least {$argument} characters.",
            'max'      => mb_strlen($text) <= (int) $argument ? null : "{$label} must be at most {$argument} characters.",
            'numeric'  => is_numeric($text) ? null : "{$label} must be a number.",
            'gt'       => (is_numeric($text) && (float) $text > (float) $argument) ? null : "{$label} must be greater than {$argument}.",
            'in'       => in_array($text, explode(',', $argument), true) ? null : "Please choose a valid {$label}.",
            'digits'   => preg_match('/^\d{' . (int) $argument . '}$/', $text) ? null : "{$label} must be {$argument} digits.",
            'same'     => $text === (string) ($data[$argument] ?? '') ? null : "{$label} does not match.",
            'password' => (preg_match('/[A-Za-z]/', $text) && preg_match('/\d/', $text))
                ? null : "{$label} must contain at least one letter and one number.",
            default    => throw new \LogicException("Unknown validation rule: {$rule}"),
        };
    }

    /** Bangladeshi mobile number: 01XXXXXXXXX (11 digits, operator digit 3-9), optional +88. */
    public static function isPhone(string $value): bool
    {
        return (bool) preg_match('/^(\+?88)?01[3-9]\d{8}$/', $value);
    }
}
