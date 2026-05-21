<?php

namespace App\Http\Requests\Concerns;

/**
 * Limpia texto libre antes de validar/guardar (S3-06, anti XSS básico).
 */
trait SanitizesTextInput
{
    protected function sanitizeText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $sinScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value) ?? $value;

        return trim(strip_tags($sinScripts));
    }

    /**
     * @param  list<string>  $fields
     */
    protected function sanitizeFields(array $fields): void
    {
        $limpio = [];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $limpio[$field] = $this->sanitizeText($this->input($field));
            }
        }

        if ($limpio !== []) {
            $this->merge($limpio);
        }
    }
}
