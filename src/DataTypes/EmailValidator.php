<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Provides robust validation for email addresses.
 *
 * Validates format, applies a regex, checks domain MX/A records,
 * and blocks blacklisted domains.
 */
class EmailValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $email = trim($data);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));

        $blacklist = ['example.com', 'test.com', 'invalid.com', 'nonexistentdomain.xyz'];
        foreach ($blacklist as $blockedDomain) {
            if (str_ends_with($domain, $blockedDomain)) {
                return false;
            }
        }

        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return false;
        }

        return true;
    }
}
