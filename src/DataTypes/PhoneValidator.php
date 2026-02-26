<?php

namespace HeroQR\DataTypes;

use libphonenumber\PhoneNumberUtil;
use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Validates phone numbers using Google's libphonenumber library
 *
 * Example:
 *   Phone::validate("+989358919279");
 */
class PhoneValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $phone = trim($data);

        if (!class_exists(PhoneNumberUtil::class)) {
            throw new \RuntimeException(
                'giggsey/libphonenumber-for-php is required. Install with: composer require giggsey/libphonenumber-for-php'
            );
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($phone, null);
            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }
}