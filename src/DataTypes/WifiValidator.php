<?php

namespace HeroQR\DataTypes;

use HeroQR\Contracts\DataTypes\AbstractDataType;

/**
 * Validates Wi-Fi credentials for QR Code.
 * Format: WIFI:T:<encryption>;S:<SSID>;P:<password>;
 * Encryption types: WPA, WPA2, WEP, nopass, WPA/WPA2-Personal (treated as WPA2)
 * Checks SSID length/characters and password rules based on encryption type.
 * Example: WIFI:T:WPA2;S:MyNetwork;P:password123;
 */
class WifiValidator extends AbstractDataType
{
    public static function validate(string $data): bool
    {
        $wifiString = trim($data);

        $pattern = '/^WIFI:T:(WPA|WPA2|WEP|nopass|WPA\/WPA2-Personal);S:([^;]+);(?:P:([^;]*))?;$/';

        if (empty($wifiString) || !preg_match($pattern, $wifiString, $matches)) {
            return false;
        }

        list(, $encryptionType, $ssid, $password) = $matches;

        if ($encryptionType === 'WPA/WPA2-Personal') {
            $encryptionType = 'WPA2';
        }

        if (strlen($ssid) > 32 || preg_match('/[;:]/', $ssid)) {
            return false;
        }

        if (in_array($encryptionType, ['WPA', 'WPA2'])) {
            if (empty($password) || strlen($password) < 8 || strlen($password) > 63) {
                return false;
            }
        }

        if ($encryptionType === 'WEP') {
            if (empty($password) || !in_array(strlen($password), [10, 26]) || !ctype_xdigit($password)) {
                return false;
            }
        }

        if ($encryptionType === 'nopass') {
            if (!empty($password)) {
                return false;
            }
        }

        return true;
    }
}
