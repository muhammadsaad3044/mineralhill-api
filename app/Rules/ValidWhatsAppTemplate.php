<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidWhatsAppTemplate implements ValidationRule
{
    public const ALLOWED_PLACEHOLDERS = [
        'product',
        'size',
        'quantity',
        'price',
        'total',
        'currency',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The ' . $attribute . ' must be a string.');
            return;
        }

        // Find all placeholders matching {something}
        preg_match_all('/\{([^}]+)\}/', $value, $matches);
        $usedPlaceholders = $matches[1] ?? [];

        // Check for unknown placeholders
        $invalid = [];
        foreach ($usedPlaceholders as $placeholder) {
            if (! in_array($placeholder, self::ALLOWED_PLACEHOLDERS, true)) {
                $invalid[] = '{' . $placeholder . '}';
            }
        }

        if (! empty($invalid)) {
            $allowedFormatted = array_map(fn ($p) => '{' . $p . '}', self::ALLOWED_PLACEHOLDERS);
            $fail('The ' . $attribute . ' contains invalid placeholder(s): ' . implode(', ', array_unique($invalid)) . '. Allowed placeholders are: ' . implode(', ', $allowedFormatted) . '.');
            return;
        }

        // Must contain at least {size} and {quantity}
        if (! in_array('size', $usedPlaceholders, true) || ! in_array('quantity', $usedPlaceholders, true)) {
            $fail('The ' . $attribute . ' must contain at least the {size} and {quantity} placeholders.');
        }
    }
}
