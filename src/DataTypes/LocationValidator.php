<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Validates geographic coordinates (latitude, longitude, optional altitude).
 *
 * Coordinates must be "latitude,longitude" or "latitude,longitude,altitude".
 * Latitude: -90 to 90, Longitude: -180 to 180, Altitude: numeric if present.
 *
 * Example: "51.3890,12.3,24" or "51.3890,12.3"
 */
class LocationValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $coordinates = trim($data);
        
        $parts = explode(',', $coordinates);

        if (count($parts) < 2 || count($parts) > 3) {
            return false;
        }

        foreach ($parts as $part) {
            if (!is_numeric($part)) {
                return false;
            }
        }

        $latitude = (float)$parts[0];
        $longitude = (float)$parts[1];

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return false;
        }

        if (isset($parts[2])) {
            $altitude = (float)$parts[2];
            if (!is_numeric($altitude)) {
                return false;
            }
        }

        return true;
    }
}