<?php

namespace HeroQR\DataTypes;

/**
 * Represents all supported data types in HeroQR
 *
 * Each enum value holds the corresponding class name for validation and generation
 */
enum DataType: string
{
    case None = NoneValidator::class;
    case Text = TextValidator::class;
    case Url = UrlValidator::class;
    case Email = EmailValidator::class;
    case Phone = PhoneValidator::class;
    case Wifi = WifiValidator::class;
    case Location = LocationValidator::class;
}
