<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Validates plain text to prevent unsafe content.
 *
 * Checks for script tags (XSS) and common SQL injection patterns.
 *
 * Example:
 *   Text::validate("Hello world");
 */
class TextValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $text = trim($data);

        if (self::hasScriptTag($text) || self::hasSqlInjection($text)) {
            return false;
        }

        return true;
    }
}
