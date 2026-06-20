<?php

namespace HeroQR\Contracts\Managers;

use Endroid\QrCode\Color\ColorInterface;

/**
 * Defines the contract for managing QR code colors, including the main color,
 * background color, and label color. Each method allows setting and retrieving
 * color values, ensuring consistency and flexibility in QR code customization.
 */
interface ColorManagerInterface
{
    /**
     * Set the foreground color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setColor(array $rgbaColor): void;

    /**
     * Get the foreground color of the QR code
     *
     * @return ColorInterface The current foreground color
     */
    public function getColor(): ColorInterface;

    /**
     * Set the background color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setBackgroundColor(array $rgbaColor): void;

    /**
     * Get the background color of the QR code
     *
     * @return ColorInterface The current background color
     */
    public function getBackgroundColor(): ColorInterface;

    /**
     * Set the label color of the QR code
     *
     * @param array $rgbaColor {Red: int, Green: int, Blue: int, Alpha: float|int} RGBA values
     * @return void
     * @throws \InvalidArgumentException If the RGBA array is invalid
     */
    public function setLabelColor(array $rgbaColor): void;

    /**
     * Get the label color of the QR code
     *
     * @return ColorInterface The current label color
     */
    public function getLabelColor(): ColorInterface;
}
