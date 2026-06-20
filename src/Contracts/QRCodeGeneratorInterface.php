<?php

namespace HeroQR\Contracts;

use Endroid\QrCode\Matrix\Matrix;
use HeroQR\DataTypes\DataType;

/**
 * Interface QRCodeGeneratorInterface
 *
 * This interface defines methods for generating and customizing QR codes, including
 * setting data, size, color, margin, logo, label, and encoding. It also provides
 * methods for generating the QR code in various formats, retrieving the matrix
 * representation, and saving the QR code to a file.
 */
interface QRCodeGeneratorInterface
{
    /**
     * Generate a QR code in the specified format
     *
     * @param string $format The desired output format (e.g., 'png', 'svg')
     * @return self
     * @throws \InvalidArgumentException If the format is invalid
     */
    public function generate(string $format): self;

    /**
     * Returns the QR code's matrix representation
     * The matrix is a grid of black and white cells representing the QR code
     *
     * @return Matrix The matrix representation of the QR code
     * @throws \RuntimeException If the QR code has not been generated yet
     */
    public function getMatrix(): Matrix;

    /**
     * Get the matrix as an array
     *
     * @return array The QR code matrix represented as a 2D array
     * @throws \RuntimeException If no QR code has been generated yet
     */
    public function getMatrixAsArray(): array;

    /**
     * Returns the QR code as a raw string
     *
     * @return string The raw string representation of the QR code
     * @throws \RuntimeException If the QR code has not been generated yet
     */
    public function getString(): string;

    /**
     * Returns the QR code as a Base64-encoded data URI
     *
     * @return string The data URI representation of the QR code
     * @throws \RuntimeException If the QR code has not been generated yet
     */
    public function getDataUri(): string;

    /**
     * Save the generated QR code to a file
     *
     * @param string $path The path to save the QR code file
     * @return bool True if the file was saved successfully, false otherwise
     * @throws \InvalidArgumentException If the format is unsupported
     * @throws \RuntimeException If no QR code has been generated yet
     */
    public function saveTo(string $path): bool;

    /**
     * Set the data to be encoded in the QR code
     *
     * @param string $data The data to encode
     * @param DataType $type DataType auto validation
     * @return self
     */
    public function setData(string $data, DataType $type): self;

    /**
     * Set the size of the QR code
     *
     * @param int $size The size of the QR code
     * @return self
     * @throws \InvalidArgumentException If the size is not a positive integer
     */
    public function setSize(int $size): self;

    /**
     * Set the margin around the QR code
     *
     * @param int $margin The margin size
     * @return self
     * @throws \InvalidArgumentException If the margin is negative
     */
    public function setMargin(int $margin): self;

    /**
     * Set the round block size mode
     *
     * @param string $mode The round block size mode as a string.
     * @return self
     * @throws \InvalidArgumentException If the given mode is invalid.
     */
    public function setBlockSizeMode(string $mode): self;

    /**
     * Set the error correction level for the QR code
     *
     * @param string $level The error correction level as a string.
     * @return self
     * @throws \InvalidArgumentException If the given level is invalid. Accepted values: low, medium, quartile, high.
     */
    public function setErrorCorrectionLevel(string $level): self;

    /**
     * Set the foreground color of the QR code
     *
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @param float $a Alpha/opacity (0.0 = fully transparent, 1.0 = fully opaque)
     * @return $this Returns the QRCodeGenerator instance for method chaining
     *
     * @throws \InvalidArgumentException If any value is out of the valid range
     */
    public function setColor(int $r, int $g, int $b, float $a): self;

    /**
     * Set the background color of the QR code
     *
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @param float $a Alpha/opacity (0.0 = fully transparent, 1.0 = fully opaque)
     * @return self Returns the QRCodeGenerator instance for method chaining
     *
     * @throws \InvalidArgumentException If any value is out of the valid range
     */
    public function setBackgroundColor(int $r, int $g, int $b, float $a): self;

    /**
     * Set the logo to be embedded in the QR code
     *
     * @param string $logoPath The path to the logo file
     * @param int $logoSize The size of the logo
     * @return self
     * @throws \InvalidArgumentException If the logo file does not exist
     */
    public function setLogo(string $logoPath, int $logoSize): self;

    /**
     * Set the label properties for the QR code
     *
     * @param string $label The text label to be displayed on the QR code
     * @param string $textAlign The text alignment for the label
     * @param array $textColor The color of the label text in hexadecimal format
     * @param int $fontSize The font size of the label text (default is 50)
     * @param array $margin The margin for the label [top, right, bottom, left]
     * @return self Returns the current instance for method chaining
     * @throws \InvalidArgumentException If the label is empty
     */
    public function setLabel(
        string $label,
        string $textAlign,
        array  $textColor,
        int    $fontSize,
        array  $margin
    ): self;

    /**
     * Set the encoding type for the QR code
     *
     * @param string $encoding The encoding type ('UTF-16' ,'UTF-8', 'ASCII', 'ISO-8859-1', 'ISO-8859-5', 'ISO-8859-15') and more...
     * @return self Returns the current instance for method chaining
     */
    public function setEncoding(string $encoding): self;
}
