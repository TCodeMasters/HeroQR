<?php

namespace HeroQR\Contracts\Customs\Drawers;

use Endroid\QrCode\{Color\ColorInterface, Logo\LogoInterface, Matrix\MatrixInterface, QrCodeInterface,};
use HeroQR\Customs\ImageOverlay;

/**
 *  Base abstract drawer for rendering QR codes (PNG, SVG, etc.)
 *  including matrix drawing, color allocation, logo handling, and finder logic.
 */
abstract class AbstractDrawer
{
    protected const FINDER_PATTERN_SIZE = 7;

    /**
     * Draws the QR code on a base image.
     *
     * @param MatrixInterface $matrix The QR code matrix containing the data modules.
     * @param QrCodeInterface $qrCode The QR code object providing configuration such as colors and size.
     * @param LogoInterface|null $logo Optional logo to embed in the center of the QR code.
     * @param ImageOverlay $imageOverlay Optional overlay to apply on top of the QR code.
     * @param string $blockShape The shape of the QR code blocks (e.g., 'square', 'circle').
     * @param mixed ...$args Additional arguments for custom rendering behavior.
     *
     * @return object
     */
    abstract public static function drawQrCode(
        MatrixInterface $matrix,
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo,
        ImageOverlay    $imageOverlay,
        string          $blockShape,
        mixed           ...$args
    ): object;

    /**
     * Creates the base image for the QR code.
     *
     * @param MatrixInterface $matrix The QR code matrix containing the data modules.
     * @param array $options Optional configuration array for base image creation (size, colors, etc.).
     *
     * @return object Returns the base image object (e.g., \GdImage for PNG, \SimpleXMLElement for SVG).
     */
    abstract protected static function createBaseImage(
        MatrixInterface $matrix,
        array           $options = []
    ): object;

    /**
     * Draws the QR code matrix onto the base image.
     *
     * @param mixed $baseImage The base image for the QR code.
     * @param MatrixInterface $matrix The QR code matrix.
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @param int $foregroundColor The fill color for the blocks.
     * @param mixed $resizedCorner Resized corner image for rounded blocks.
     * @param string $blockShape The shape of the block in the QR code
     * @param LogoInterface|null $logo The logo to embed in the QR code (optional).
     * @param string $data The data encoded in the QR code (optional).
     * @return void
     */
    abstract protected static function drawMatrix(
        mixed           $baseImage,
        MatrixInterface $matrix,
        int             $baseBlockSize,
        int             $foregroundColor,
        mixed           $resizedCorner,
        string          $blockShape,
        string          $data,
        ?LogoInterface  $logo = null,
    ): void;

    /**
     * Allocates the foreground color for the base image (PNG or SVG).
     *
     * @param object $baseImage The base image to apply the foreground color to. Can be \GdImage or \SimpleXMLElement (SVG).
     * @param ColorInterface $color The color to allocate.
     * @return int|\SimpleXMLElement Returns a GD color resource for PNG or modifies the SVG element.
     *
     * @throws \InvalidArgumentException If the base image type is not supported.
     */
    protected static function allocateColor(
        object         $baseImage,
        ColorInterface $color
    ): int|\SimpleXMLElement
    {
        if ($baseImage instanceof \GdImage) {
            return self::allocateGdColor($baseImage, $color);
        } elseif ($baseImage instanceof \SimpleXMLElement) {
            return self::allocateSvgColor($baseImage, $color);
        }

        throw new \InvalidArgumentException('Unsupported image type for allocateForegroundColor');
    }

    /**
     * Allocates the foreground color for a GD image.
     *
     * @param \GdImage $baseImage The GD image to apply the foreground color to.
     * @param ColorInterface $color The color to allocate.
     * @return int Returns a GD color resource.
     */
    protected static function allocateGdColor(
        \GdImage       $baseImage,
        ColorInterface $color
    ): int
    {
        $alpha = $color->getAlpha();

        return imagecolorallocatealpha(
            $baseImage,
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue(),
            min(127, max(0, $alpha))
        );
    }

    /**
     * Allocates the foreground color for an SVG image.
     *
     * @param \SimpleXMLElement $baseImage The SVG image to apply the foreground color to.
     * @param ColorInterface $color The color to allocate.
     * @return \SimpleXMLElement Returns the modified SVG element.
     */
    protected static function allocateSvgColor(
        \SimpleXMLElement $baseImage,
        ColorInterface    $color
    ): \SimpleXMLElement
    {
        $alpha = 1 - ($color->getAlpha() / 127);

        $baseImage->addAttribute('fill', $color->getHex());
        $baseImage->addAttribute('fill-opacity', min(1, max(0, $alpha)));

        return $baseImage;
    }

    /**
     * Prepares the logo dimensions and calculates the necessary padding based on the data length.
     *
     * @param LogoInterface|null $logo The logo to be used in the QR code.
     * @param string|null $data The data for the QR code.
     * @param int $blockCount The number of blocks in the QR code.
     * @param int $baseBlockSize The base size of the QR code blocks.
     * @param string $drawer Fully-qualified class name of the drawer (e.g., SvgDrawer, PngDrawer).
     * @return array{int,int,int} The width, height, and padding for the logo.
     */
    protected static function prepareLogoDimensions(
        ?LogoInterface $logo,
        ?string        $data,
        int            $blockCount,
        int            $baseBlockSize,
        string         $drawer
    ): array
    {
        if ($logo === null) {
            return [0, 0, 0];
        }

        [$logoWidth, $logoHeight] = self::calculateLogoDimensions($logo, $baseBlockSize);

        $logoRatio = ($logoWidth * $logoHeight) / ($blockCount * $blockCount);

        $length = strlen($data ?? '');

        $padding = self::calculateLogoPadding($blockCount, $logoRatio, $length, $drawer);

        return [$logoWidth, $logoHeight, $padding];
    }

    /**
     * Calculates the logo padding based on drawer type.
     *
     * @param int $blockCount The number of blocks in the QR code.
     * @param float $logoRatio The ratio of the logo size to the QR code size.
     * @param int $length The length of the QR code data.
     * @param string $drawer Fully-qualified class name of the drawer (e.g., SvgDrawer, PngDrawer).
     * @return int The calculated logo padding.
     */
    private static function calculateLogoPadding(int $blockCount, float $logoRatio, int $length, string $drawer): int
    {
        if ($drawer === 'HeroQR\Contracts\Customs\Drawers\SvgDrawer') {
            return max(
                1,
                (int)ceil($blockCount * 0.02 + $logoRatio * $blockCount * 0.3)
            );
        } else {
            return max(
                2,
                (int)ceil($blockCount * 0.035 + $logoRatio * $blockCount * 0.6 + log10($length ?: 1))
            );
        }
    }

    /**
     * Calculates the dimensions of the logo based on the base block size.
     *
     * @param LogoInterface|null $logo The logo to be resized.
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @return array{int,int} The resized width and height of the logo in blocks.
     */
    private static function calculateLogoDimensions(
        ?LogoInterface $logo,
        int            $baseBlockSize
    ): array
    {
        return $logo === null
            ? [0, 0] : [
                (int)ceil($logo->getResizeToWidth() / $baseBlockSize),
                (int)ceil($logo->getResizeToHeight() / $baseBlockSize)
            ];
    }

    /**
     * Calculates the bounds for the logo's placement within the QR code.
     *
     * @param array $logo Contains logo dimensions, block count, and padding information.
     * @return array The start and end row and column indices for logo placement.
     */
    protected static function calculateLogoBounds(array $logo): array
    {
        $logoStartRow = max(0, ($logo['blockCount'] - $logo['height']) / 2 - $logo['padding']);
        $logoEndRow = min($logo['blockCount'], ($logo['blockCount'] + $logo['height']) / 2 + $logo['padding']);
        $logoStartCol = max(0, ($logo['blockCount'] - $logo['width']) / 2 - $logo['padding']);
        $logoEndCol = min($logo['blockCount'], ($logo['blockCount'] + $logo['width']) / 2 + $logo['padding']);

        return [$logoStartRow, $logoEndRow, $logoStartCol, $logoEndCol];
    }

    /**
     * Formats a number to a string with specific decimal precision.
     *
     * @param float $number The number to format.
     * @param int $precision The number of decimal places.
     * @return string The formatted number string.
     */
    protected static function formatNumber(float $number, int $precision = 2): string
    {
        $string = number_format($number, $precision, '.', '');
        $string = rtrim($string, '0');

        return rtrim($string, '.');
    }


    /**
     * Checks if the corner image needs to be rotated based on the matrix position.
     *
     * @param int $rowIndex The current row index in the matrix.
     * @param int $columnIndex The current column index in the matrix.
     * @param int $blackCount
     * @return bool True if the corner image needs to be rotated, false otherwise.
     */
    protected static function isFinderStart(
        int $rowIndex,
        int $columnIndex,
        int $blackCount
    ): bool
    {
        return ($rowIndex === 0 && $columnIndex === 0) ||
            ($rowIndex === 0 && $columnIndex === $blackCount - self::FINDER_PATTERN_SIZE) ||
            ($rowIndex === $blackCount - self::FINDER_PATTERN_SIZE && $columnIndex === 0);
    }

    /**
     * Resamples and copies a portion of the source image to the destination image
     *
     * @param mixed $dstImage Destination image resource
     * @param mixed $srcImage Source image resource
     * @param array $dst_X_Y_W_H Destination coordinates and dimensions (X, Y, Width, Height)
     * @param array $src_X_Y_W_H Source coordinates and dimensions (X, Y, Width, Height)
     * @return bool True on success, false on failure
     */
    public static function copyResampledImage(
        mixed $dstImage,
        mixed $srcImage,
        array $dst_X_Y_W_H,
        array $src_X_Y_W_H
    ): bool
    {
        return imagecopyresampled(
            $dstImage,
            $srcImage,
            $dst_X_Y_W_H['X'],
            $dst_X_Y_W_H['Y'],
            $src_X_Y_W_H['X'],
            $src_X_Y_W_H['Y'],
            $dst_X_Y_W_H['Width'],
            $dst_X_Y_W_H['Height'],
            $src_X_Y_W_H['Width'],
            $src_X_Y_W_H['Height'],
        );
    }

    /**
     * Checks if the given row and column are within the calculated logo bounds.
     *
     * @param array $columns Contains row and column indices, and logo bounds.
     * @return bool True if the position is within the logo bounds, false otherwise.
     */
    protected static function isWithinLogoBounds(
        array $columns
    ): bool
    {
        return $columns['rowIndex'] >= $columns['logoStartRow'] && $columns['rowIndex'] < $columns['logoEndRow'] &&
            $columns['columnIndex'] >= $columns['logoStartCol'] && $columns['columnIndex'] < $columns['logoEndCol'];
    }

    /**
     * Determines whether the given row and column belong to a finder pattern area
     *
     * @param int $rowIndex The row index of the current block
     * @param int $colIndex The column index of the current block
     * @param int $blockCount Total number of blocks per side of the QR matrix
     *
     * @return bool True if the block is part of a finder pattern; otherwise false
     */
    protected static function isFinderPattern(
        int $rowIndex,
        int $colIndex,
        int $blockCount
    ): bool
    {
        return ($rowIndex < self::FINDER_PATTERN_SIZE && $colIndex < self::FINDER_PATTERN_SIZE) ||
            ($rowIndex < self::FINDER_PATTERN_SIZE && $colIndex >= $blockCount - self::FINDER_PATTERN_SIZE) ||
            ($rowIndex >= $blockCount - self::FINDER_PATTERN_SIZE && $colIndex < self::FINDER_PATTERN_SIZE);
    }

    /**
     * Returns the rotation angle (0, 90, or 270 degrees) for a finder pattern based on its position
     *
     * @param int $rowIndex The row index of the finder
     * @param int $colIndex The column index of the finder
     * @return int Rotation angle in degrees (0, 90, or 270)
     */
    protected static function getFinderRotation(int $rowIndex, int $colIndex): int
    {
        if ($rowIndex === 0 && $colIndex > 0) return 90;
        if ($rowIndex > 0 && $colIndex === 0) return 270;
        return 0;
    }
}