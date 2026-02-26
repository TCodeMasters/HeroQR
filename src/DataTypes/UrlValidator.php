<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Validates URLs to ensure proper structure and prevent unsafe content.
 *
 * Checks:
 * - URL format (http/https)
 * - Valid domain (host)
 * - No SQL injections, script tags, or relative path traversals
 *
 * Example:
 *   Url::validate("https://HeroQR.ir");
 */
class UrlValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $url = trim($data);
        $parsedUrl = parse_url($url);

        if (!filter_var($url, FILTER_VALIDATE_URL) || empty($parsedUrl['host'])) {
            return false;
        }

        if (
            !preg_match('/^(https?:\/\/(?:[a-zA-Z0-9-]+\.)+[a-zA-Z0-9-]+(?:\/[^\s]*)?(\?[^\s]*)?(#\S*)?)$/i', $url)
            && !filter_var($url, FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        if (self::hasSqlInjection($url) || self::hasScriptTag($url) || preg_match('/(\.\.\/)/', $url)) {
            return false;
        }

        return true;
    }
}
