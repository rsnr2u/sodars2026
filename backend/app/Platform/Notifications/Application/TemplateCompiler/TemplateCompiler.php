<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Application\TemplateCompiler;

class TemplateCompiler
{
    /**
     * Compile template content replacing curly-bracket placeholders with dot-notation context objects.
     */
    public function compile(string $template, array $context): string
    {
        // Matches double curly braces e.g., {{booking.code}}, {{customer.name}}
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\-\.]+)\s*\}\}/', function (array $matches) use ($context) {
            $key = $matches[1];
            
            // Resolve nested array/object fields using Laravel helper
            $value = data_get($context, $key);
            
            if ($value === null) {
                // Keep the placeholder raw or replace it with empty if not found in context
                return '';
            }

            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }

            return (string) $value;
        }, $template) ?? $template;
    }
}
