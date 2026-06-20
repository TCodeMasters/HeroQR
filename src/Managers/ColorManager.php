<?php

declare(strict_types=1);

namespace HeroQR\Managers;

use Endroid\QrCode\Color\{Color, ColorInterface};
use HeroQR\Contracts\Managers\ColorManagerInterface;

/**
 * Manages the foreground, background, and label colors for QR codes
 * Allows setting and getting colors using RGBA arrays and ensures proper conversion.
 * The class ensures proper handling of colors for the QR code, including support for alpha transparency
 */
class ColorManager implements ColorManagerInterface
{
    /**
     * ColorManager constructor
     *
     * @param ColorInterface $foregroundColor Default color of the QR code (black)
     * @param ColorInterface $backgroundColor Default background color of the QR code (white)
     * @param ColorInterface $labelColor Default label color (black)
     */
    public function __construct(
        private ColorInterface $foregroundColor = new Color(0, 0, 0), # Default black
        private ColorInterface $backgroundColor = new Color(255, 255, 255), # Default white
        private ColorInterface $labelColor = new Color(0, 0, 0), # Default black
    ) {}

    /**
     * Set the foreground color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setColor(array $rgbaColor): void
    {
        $this->foregroundColor = $this->createColorFromRgba($rgbaColor);
    }

    /**
     * Get the foreground color of the QR code
     *
     * @return ColorInterface The current foreground color
     */
    public function getColor(): ColorInterface
    {
        return $this->foregroundColor;
    }

    /**
     * Set the background color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setBackgroundColor(array $rgbaColor): void
    {
        $this->backgroundColor = $this->createColorFromRgba($rgbaColor);
    }

    /**
     * Get the background color of the QR code
     *
     * @return ColorInterface The current background color
     */
    public function getBackgroundColor(): ColorInterface
    {
        return $this->backgroundColor;
    }

    /**
     * Set the label color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setLabelColor(array $rgbaColor): void
    {
        $this->labelColor = $this->createColorFromRgba($rgbaColor);
    }

    /**
     * Get the label color of the QR code
     *
     * @return ColorInterface The current label color
     */
    public function getLabelColor(): ColorInterface
    {
        return $this->labelColor;
    }

    /**
     * Validates that the RGBA array contains all required keys with correct types/values
     *
     * @param array $rgba {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @throws \InvalidArgumentException If validation fails
     * @return void
     */
    private function validateRgbaArray(array $rgba): void
    {
        $required = ['Red', 'Green', 'Blue'];
        $missing = array_diff($required, array_keys($rgba));

        if (!empty($missing)) {
            throw new \InvalidArgumentException("Missing color keys: " . implode(', ', $missing));
        }

        foreach (['Red', 'Green', 'Blue'] as $color) {
            if (!is_int($rgba[$color]) || $rgba[$color] < 0 || $rgba[$color] > 255) {
                throw new \InvalidArgumentException("{$color} must be integer (0-255). Input: {$rgba[$color]}");
            }
        }

        if (isset($rgba['Alpha'])) {
            if (!is_numeric($rgba['Alpha']) || $rgba['Alpha'] < 0 || $rgba['Alpha'] > 1.0) {
                throw new \InvalidArgumentException("Alpha must be between 0 and 1.");
            }
        }
    }

    /**
     * Helper method to create a Color object from an RGBA array
     *
     * @param array $rgba {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return ColorInterface The created Color object
     */
    public function createColorFromRgba(array $rgba): ColorInterface
    {
        $this->validateRgbaArray($rgba);

        $rawAlpha = $rgba['Alpha'] ?? 1.0;
        $alpha = (int)round((1 - $rawAlpha) * 127);
        $gdAlpha = max(0, min(127, $alpha));

        return new Color(
            (int)$rgba['Red'],
            (int)$rgba['Green'],
            (int)$rgba['Blue'],
            $gdAlpha
        );
    }
}
