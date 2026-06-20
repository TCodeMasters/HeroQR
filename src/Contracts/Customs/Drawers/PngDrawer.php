<?php

declare(strict_types=1);

namespace HeroQR\Contracts\Customs\Drawers;

use Endroid\QrCode\{Logo\LogoInterface, Matrix\MatrixInterface, QrCodeInterface};
use HeroQR\Customs\{ImageOverlay, ShapeDrawers\PngShapeDrawer};

/**
 * Handles rendering of QR codes using the GD image library.
 *
 * Provides methods for drawing QR code matrices, applying overlays,
 * and preparing corner images. Serves as a base for GD-based QR code renderers.
 */
final class PngDrawer extends AbstractDrawer
{
    /**
     * Draws the QR code on the base image as an PNG.
     *
     * Prepares and resizes the corner image, then renders the QR code matrix
     * with the specified block size, color, and shape, and applies the image overlay.
     *
     * @param MatrixInterface $matrix The QR code matrix.
     * @param QrCodeInterface $qrCode The QR code object.
     * @param ?LogoInterface $logo The logo to embed in the QR code (optional)
     * @param ImageOverlay $imageOverlay Optional image overlay.
     * @param string $blockShape The shape of QR code blocks (square, diamond, star)
     * @param mixed ...$args Optional additional arguments
     * @return \GdImage
     * @throws \Exception
     */
    public static function drawQrCode(
        MatrixInterface $matrix,
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo,
        ImageOverlay    $imageOverlay,
        string          $blockShape,
        mixed           ...$args
    ): \GdImage
    {
        [$baseBlockSize] = $args;

        $baseImage = self::createBaseImage($matrix, ['baseBlockSize' => $baseBlockSize]);
        $cornerImage = $imageOverlay->getImage($qrCode->getForegroundColor());
        $resizedCorner = self::resizeCornerImage($cornerImage, $baseBlockSize);
        $foregroundColor = AbstractDrawer::allocateColor($baseImage, $qrCode->getForegroundColor());

        self::drawMatrix($baseImage, $matrix, $baseBlockSize, $foregroundColor, $resizedCorner, $blockShape, $qrCode->getData(), $logo);

        imagedestroy($cornerImage);
        imagedestroy($resizedCorner);
        return $baseImage;
    }

    /**
     * Creates the base image for the QR code
     *
     * @param MatrixInterface $matrix The matrix that defines the block layout for the QR code
     *
     * @return \GdImage The created base image resource
     */
    protected static function createBaseImage(
        MatrixInterface $matrix,
        array           $options = []
    ): \GdImage
    {
        $baseImage = imagecreatetruecolor($matrix->getBlockCount() * $options['baseBlockSize'], $matrix->getBlockCount() * $options['baseBlockSize']);

        imageantialias($baseImage, true);
        imagesavealpha($baseImage, true);
        imagealphablending($baseImage, false);

        $transparentColor = imagecolorallocatealpha($baseImage, 0, 0, 0, 127);
        imagefill($baseImage, 0, 0, $transparentColor);

        return $baseImage;
    }

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
    protected static function drawMatrix(
        mixed           $baseImage,
        MatrixInterface $matrix,
        int             $baseBlockSize,
        int             $foregroundColor,
        mixed           $resizedCorner,
        string          $blockShape,
        string          $data,
        ?LogoInterface  $logo = null,
    ): void
    {
        $blockCount = $matrix->getBlockCount();
        [$logoWidthBlocks, $logoHeightBlocks, $logoPaddingBlocks] = parent::prepareLogoDimensions($logo, $data, $blockCount, $baseBlockSize, PngDrawer::class);

        [$logoStartRow, $logoEndRow, $logoStartCol, $logoEndCol] = parent::calculateLogoBounds([
            'blockCount' => $blockCount,
            'width' => $logoWidthBlocks,
            'height' => $logoHeightBlocks,
            'padding' => $logoPaddingBlocks
        ]);

        for ($rowIndex = 0; $rowIndex < $blockCount; ++$rowIndex) {
            for ($columnIndex = 0; $columnIndex < $blockCount; ++$columnIndex) {
                if (parent::isWithinLogoBounds([
                    'rowIndex' => $rowIndex,
                    'columnIndex' => $columnIndex,
                    'logoStartRow' => intval($logoStartRow),
                    'logoEndRow' => intval($logoEndRow),
                    'logoStartCol' => intval($logoStartCol),
                    'logoEndCol' => intval($logoEndCol)
                ])) {
                    continue;
                }

                if ($matrix->getBlockValue($rowIndex, $columnIndex) === 1) {
                    self::drawMatrixBlock($baseImage, $matrix, $rowIndex, $columnIndex, $baseBlockSize, $foregroundColor, $blockShape, $resizedCorner);
                }
            }
        }
    }

    /**
     * Draws a block of the matrix on the QR code image, handling special corner blocks and filling others with a foreground color
     *
     * @param \GdImage $baseImage The base image where the block will be drawn.
     * @param MatrixInterface $matrix The matrix representing the QR code.
     * @param int $rowIndex The row index of the block.
     * @param int $columnIndex The column index of the block.
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @param int $foregroundColor The color to fill the block.
     * @param string $blockShape The shape of the block in the QR code.
     * @param \GdImage $resizedCorner The resized corner image (used for corner blocks).
     * @return void
     */
    private static function drawMatrixBlock(
        \GdImage        $baseImage,
        MatrixInterface $matrix,
        int             $rowIndex,
        int             $columnIndex,
        int             $baseBlockSize,
        int             $foregroundColor,
        string          $blockShape,
        \GdImage        $resizedCorner
    ): void
    {
        $blockCount = $matrix->getBlockCount();

        if (parent::isFinderPattern($rowIndex, $columnIndex, $blockCount)) {
            self::drawCornerBlock($baseImage, $blockCount, $rowIndex, $columnIndex, $baseBlockSize, $resizedCorner);
        } else {
            PngShapeDrawer::{$blockShape}($baseImage, $rowIndex, $columnIndex, $baseBlockSize, $foregroundColor);
        }
    }

    /**
     * Resizes the corner image to fit the QR code matrix
     *
     * @param \GdImage $cornerImage The original corner image
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @return \GdImage The resized corner image for the QR code.
     */
    private static function resizeCornerImage(
        \GdImage $cornerImage,
        int      $baseBlockSize
    ): \GdImage
    {
        $cornerSize = self::FINDER_PATTERN_SIZE * $baseBlockSize;
        $resizedCorner = imagecreatetruecolor($cornerSize, $cornerSize);

        imageantialias($resizedCorner, true);
        imagesavealpha($resizedCorner, true);
        imagealphablending($resizedCorner, false);

        $transparent = imagecolorallocatealpha($resizedCorner, 0, 0, 0, 127);
        imagefill($resizedCorner, 0, 0, $transparent);

        imagealphablending($resizedCorner, true);

        AbstractDrawer::copyResampledImage(
            $resizedCorner,
            $cornerImage,
            ['X' => 0, 'Y' => 0, 'Width' => $cornerSize, 'Height' => $cornerSize,],
            ['X' => 0, 'Y' => 0, 'Width' => imagesy($cornerImage), 'Height' => imagesy($cornerImage)]
        );

        return $resizedCorner;
    }

    /**
     * Draws a corner block on the QR code image (top-left, top-right, or bottom-left)
     *
     * @param mixed $baseImage The base image where the corner block will be drawn.
     * @param int $blockCount The matrix representing the QR code.
     * @param int $rowIndex The row index of the block.
     * @param int $columnIndex The column index of the block.
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @param mixed $resizedCorner The resized corner image.
     * @return void
     */
    protected static function drawCornerBlock(
        \GdImage $baseImage,
        int      $blockCount,
        int      $rowIndex,
        int      $columnIndex,
        int      $baseBlockSize,
        \GdImage $resizedCorner,
    ): void
    {
        if (!parent::isFinderStart($rowIndex, $columnIndex, $blockCount)) return;

        $rotation = parent::getFinderRotation($rowIndex, $columnIndex);

        $gdRotation = match ($rotation) {
            90  => 270,
            270 => 90,
            default => 0,
        };

        if ($gdRotation !== 0) {
            $transparent = imagecolorallocatealpha($resizedCorner, 0, 0, 0, 127);
            $rotatedCorner = imagerotate($resizedCorner, $gdRotation, $transparent);
            imagealphablending($rotatedCorner, false);
            imagesavealpha($rotatedCorner, true);
        } else {
            $rotatedCorner = $resizedCorner;
        }

        imagecopyresampled(
            $baseImage,
            $rotatedCorner,
            $columnIndex * $baseBlockSize, $rowIndex * $baseBlockSize, 0, 0,
            self::FINDER_PATTERN_SIZE * $baseBlockSize,  self::FINDER_PATTERN_SIZE * $baseBlockSize,
            imagesx($rotatedCorner), imagesy($rotatedCorner)
        );
    }
}