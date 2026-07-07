<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules;

class Lexer
{
    /**
     * Tokenize the raw expression string.
     */
    public function tokenize(string $expression): array
    {
        $tokens = [];
        $length = strlen($expression);
        $i = 0;

        while ($i < $length) {
            $char = $expression[$i];

            // Ignore whitespace
            if (ctype_space($char)) {
                $i++;
                continue;
            }

            // Group logical operators
            if ($char === '&' && ($i + 1 < $length) && $expression[$i + 1] === '&') {
                $tokens[] = ['type' => 'LOGICAL', 'value' => '&&'];
                $i += 2;
                continue;
            }
            if ($char === '|' && ($i + 1 < $length) && $expression[$i + 1] === '|') {
                $tokens[] = ['type' => 'LOGICAL', 'value' => '||'];
                $i += 2;
                continue;
            }

            // Group comparison operators
            if (($char === '=' || $char === '!' || $char === '<' || $char === '>') && ($i + 1 < $length) && $expression[$i + 1] === '=') {
                $tokens[] = ['type' => 'OPERATOR', 'value' => $char . '='];
                $i += 2;
                continue;
            }
            if ($char === '<' || $char === '>') {
                $tokens[] = ['type' => 'OPERATOR', 'value' => $char];
                $i++;
                continue;
            }

            // Parentheses and commas
            if ($char === '(' || $char === ')' || $char === ',') {
                $tokens[] = ['type' => 'PUNCTUATION', 'value' => $char];
                $i++;
                continue;
            }

            // Quoted strings
            if ($char === "'" || $char === '"') {
                $quote = $char;
                $val = '';
                $i++;
                while ($i < $length && $expression[$i] !== $quote) {
                    $val .= $expression[$i];
                    $i++;
                }
                $i++; // skip closing quote
                $tokens[] = ['type' => 'LITERAL', 'value' => $val];
                continue;
            }

            // Numbers
            if (ctype_digit($char) || $char === '.') {
                $val = '';
                while ($i < $length && (ctype_digit($expression[$i]) || $expression[$i] === '.')) {
                    $val .= $expression[$i];
                    $i++;
                }
                $numVal = str_contains($val, '.') ? (float)$val : (int)$val;
                $tokens[] = ['type' => 'LITERAL', 'value' => $numVal];
                continue;
            }

            // Identifiers (variables / function names)
            if (ctype_alpha($char) || $char === '_' || $char === '-') {
                $val = '';
                while ($i < $length && (ctype_alnum($expression[$i]) || $expression[$i] === '_' || $expression[$i] === '-' || $expression[$i] === '.')) {
                    $val .= $expression[$i];
                    $i++;
                }

                if (strtolower($val) === 'true') {
                    $tokens[] = ['type' => 'LITERAL', 'value' => true];
                } elseif (strtolower($val) === 'false') {
                    $tokens[] = ['type' => 'LITERAL', 'value' => false];
                } elseif (strtolower($val) === 'null') {
                    $tokens[] = ['type' => 'LITERAL', 'value' => null];
                } else {
                    $tokens[] = ['type' => 'IDENTIFIER', 'value' => $val];
                }
                continue;
            }

            // Fallback character skip
            $i++;
        }

        return $tokens;
    }
}
