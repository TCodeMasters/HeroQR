<?php

declare(strict_types=1);

namespace HeroQR\Contracts\Customs\Drawers;

use Endroid\QrCode\{Color\ColorInterface,
    Logo\LogoInterface,
    Matrix\MatrixInterface,
    QrCodeInterface};
use HeroQR\Customs\{ImageOverlay, ShapeDrawers\SvgShapeDrawer};

/**
 * Renders QR codes as SVG images using GD-based processing.
 *
 * Provides methods for drawing QR code matrices, applying overlays,
 * and preparing corner images. Serves as a base for SVG QR code renderers.
 */
final class SvgDrawer extends AbstractDrawer
{
    /**
     * Draws the QR code on the base image as an SVG.
     *
     * Renders the QR code matrix with optional corner images, block shape,
     * and image overlay. Additional options can be passed via $args.
     *
     * @param MatrixInterface $matrix The QR code matrix
     * @param QrCodeInterface $qrCode The QR code object containing colors
     * @param LogoInterface|null $logo Optional logo to embed in the QR code
     * @param ImageOverlay $imageOverlay Optional image overlay
     * @param string $blockShape The shape of QR code blocks (square, diamond, star)
     * @param mixed ...$args Optional additional arguments (currently expects [$options])
     * @return \SimpleXMLElement The generated SVG element
     * @throws \Exception
     */
    public static function drawQrCode(
        MatrixInterface $matrix,
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo,
        ImageOverlay    $imageOverlay,
        string          $blockShape,
        mixed           ...$args
    ): \SimpleXMLElement
    {
        [$baseBlockSize] = $args;

        $baseImage = self::createBaseImage($matrix, [
            'backgroundColor' => $qrCode->getBackgroundColor(),
            'baseBlockSize' => $baseBlockSize,
        ]);

        $foregroundColor = $qrCode->getForegroundColor();
        $cornerImageUri = $imageOverlay->getUriImage($foregroundColor);

        // فراخوانی متد اصلی رسم ماتریس (دقیقاً مشابه PNG)
        self::drawMatrix($baseImage->g, $matrix, $baseBlockSize, $foregroundColor, $cornerImageUri, $blockShape, $qrCode->getData(), $logo);

        return $baseImage;
    }

    /**
     * Creates the base image for the QR code
     *
     * @param MatrixInterface $matrix The matrix that defines the block layout for the QR code
     * @param array $options Options for creating the base image
     * @return \SimpleXMLElement The created base image resource
     */


    protected static function createBaseImage(MatrixInterface $matrix, array $options = []): \SimpleXMLElement
    {
        $blockPixel = $options['baseBlockSize'];
        $marginSize = $matrix->getMarginLeft();
        $totalSize = ($matrix->getBlockCount() * $blockPixel) + ($marginSize * 2);

        $xml = new \SimpleXMLElement('<svg xmlns="http://www.w3.org/2000/svg"/>');
        $xml->addAttribute('version', '1.1');
        $xml->addAttribute('viewBox', "0 0 {$totalSize} {$totalSize}");

        if (empty($options['exclude_svg_width_and_height'])) {
            $xml->addAttribute('width', "{$totalSize}px");
            $xml->addAttribute('height', "{$totalSize}px");
        }

        $background = $xml->addChild('rect');
        $background->addAttribute('width', (string)$totalSize);
        $background->addAttribute('height', (string)$totalSize);
        parent::allocateColor($background, $options['backgroundColor']);

        $group = $xml->addChild('g');
        $group->addAttribute('transform', "translate({$marginSize}, {$marginSize})");

        return $xml;
    }

    /**
     * Draws the QR code matrix onto the base image.
     *
     * @param mixed $baseImage The base image for the QR code.
     * @param MatrixInterface $matrix The QR code matrix.
     * @param int $baseBlockSize The pixel size of each QR matrix block.
     * @param int|ColorInterface $foregroundColor The fill color for the blocks.
     * @param string $resizedCorner This is not used in SVG, so it's not type hinted
     * @param string $blockShape The shape of the block in the QR code
     * @param LogoInterface|null $logo The logo to embed in the QR code (optional).
     * @param string $data The data encoded in the QR code (optional).
     * @return void
     */
    protected static function drawMatrix(
        mixed              $baseImage,
        MatrixInterface    $matrix,
        int                $baseBlockSize,
        int|ColorInterface $foregroundColor,
        mixed              $resizedCorner,
        string             $blockShape,
        string             $data,
        ?LogoInterface     $logo = null
    ): void
    {
        $blockCount = $matrix->getBlockCount();

        $defs = self::prepareDefinitions($baseImage, $resizedCorner, $blockShape, $baseBlockSize, $foregroundColor);

        $logoBounds = self::prepareLogoBounds($matrix, $logo, $data, $baseBlockSize);

        for ($rowIndex = 0; $rowIndex < $blockCount; $rowIndex++) {
            for ($columnIndex = 0; $columnIndex < $blockCount; $columnIndex++) {

                if (parent::isWithinLogoBounds(array_merge(['rowIndex' => $rowIndex, 'columnIndex' => $columnIndex], $logoBounds))) {
                    continue;
                }

                if ($matrix->getBlockValue($rowIndex, $columnIndex) === 1) {
                    self::drawMatrixBlock($baseImage, $matrix, $rowIndex, $columnIndex, $baseBlockSize, $foregroundColor, $blockShape, '#corner_asset');
                }
            }
        }
    }

    /**
     * Draws a block of the matrix on the QR code image, handling special corner blocks and filling others with a foreground color
     *
     * @param \SimpleXMLElement $baseImage The base image where the block will be drawn.
     * @param MatrixInterface $matrix The matrix representing the QR code.
     * @param int $rowIndex The row index of the block.
     * @param int $columnIndex The column index of the block.
     * @param ColorInterface $foregroundColor The color to fill the block.
     * @param string $blockShape The shape of the block in the QR code.
     * @param string $resizedCorner The resized corner image (used for corner blocks).
     * @return void
     */
    private static function drawMatrixBlock(
        \SimpleXMLElement $baseImage,
        MatrixInterface   $matrix,
        int               $rowIndex,
        int               $columnIndex,
        int               $baseBlockSize,
        int|ColorInterface $foregroundColor,
        string            $blockShape,
        string            $resizedCorner
    ): void
    {
        $blockCount = $matrix->getBlockCount();

        if (parent::isFinderPattern($rowIndex, $columnIndex, $blockCount)) {
            self::drawCornerBlock($baseImage, $rowIndex, $columnIndex, $blockCount, $baseBlockSize, $resizedCorner);
        } else {
            SvgShapeDrawer::{$blockShape}($baseImage, $rowIndex, $columnIndex, $baseBlockSize, $foregroundColor);
        }
    }

    /**
     * Position and rotate QR finder patterns (corners) in the SVG
     *
     * @param \SimpleXMLElement $baseImage The SVG element to append the corner image to.
     * @param int $rowIndex The current row index in the QR matrix.
     * @param int $columnIndex The current column index in the QR matrix.
     * @param int $blockCount Total number of blocks in the QR matrix (width/height).
     * @param int $baseBlockSize The size (in pixels) of a single QR module.
     * @param string $resizedCorner The URI or path to the corner image asset.
     *
     * @return void
     */
    protected static function drawCornerBlock(
        \SimpleXMLElement $baseImage,
        int               $rowIndex,
        int               $columnIndex,
        int               $blockCount,
        int               $baseBlockSize,
        string            $resizedCorner
    ): void
    {
        if (!parent::isFinderStart($rowIndex, $columnIndex, $blockCount)) return;

        $x = ($baseBlockSize * $columnIndex);
        $y = ($baseBlockSize * $rowIndex);
        $pixelSize = $baseBlockSize * self::FINDER_PATTERN_SIZE;

        $use = $baseImage->addChild('use');
        $use->addAttribute('href', $resizedCorner);
        $use->addAttribute('x', (string)$x);
        $use->addAttribute('y', (string)$y);

        $rotation = parent::getFinderRotation($rowIndex, $columnIndex);
        if ($rotation !== 0) {
            $cx = $x + ($pixelSize / 2);
            $cy = $y + ($pixelSize / 2);
            $use->addAttribute('transform', "rotate({$rotation} {$cx} {$cy})");
        }
    }
    private static function prepareDefinitions(\SimpleXMLElement $baseImage, $cornerUri, $shape,int $size, $color): \SimpleXMLElement
    {
        $root = dom_import_simplexml($baseImage)->ownerDocument;
        $svgElement = simplexml_import_dom($root);
        $defs = $svgElement->addChild('defs');

        // تعریف تصویر گوشه
        $pixelSize = $size * self::FINDER_PATTERN_SIZE;
        $imgDef = $defs->addChild('image');
        $imgDef->addAttribute('id', 'corner_asset');
        $imgDef->addAttribute('xlink:href', $cornerUri, 'http://www.w3.org/1999/xlink');
        $imgDef->addAttribute('width', (string)$pixelSize);
        $imgDef->addAttribute('height', (string)$pixelSize);

        // تعریف شکل بدنه
        SvgShapeDrawer::{$shape}($defs, 0, 0, $size, $color);

        return $defs;
    }

    /**
     * خلاصه کردن منطق لوگو
     */
    private static function prepareLogoBounds(MatrixInterface $matrix, ?LogoInterface $logo, string $data, int $baseBlockSize): array
    {
        $blockCount = $matrix->getBlockCount();
        [$width, $height, $padding] = parent::prepareLogoDimensions($logo, $data, $blockCount, $baseBlockSize, SvgDrawer::class);

        [$startRow, $endRow, $startCol, $endCol] = parent::calculateLogoBounds([
            'blockCount' => $blockCount, 'width' => $width, 'height' => $height, 'padding' => $padding
        ]);

        return [
            'logoStartRow' => intval($startRow),
            'logoEndRow' => intval($endRow),
            'logoStartCol' => intval($startCol),
            'logoEndCol' => intval($endCol)
        ];
    }
}