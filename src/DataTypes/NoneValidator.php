<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Null Object Validator: Represents plain text or undefined data types
 * This validator always returns true as it imposes no formatting constraints
 */
class NoneValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        return true;
    }
}