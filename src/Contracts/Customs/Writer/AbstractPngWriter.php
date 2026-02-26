<?php

declare(strict_types=1);

namespace HeroQR\Contracts\Customs\Writer;

use Endroid\QrCode\{ImageData\LabelImageData,
    ImageData\LogoImageData,
    Label\LabelAlignment,
    Label\LabelInterface,
    Logo\LogoInterface,
    Matrix\MatrixInterface,
    QrCodeInterface,
    RoundBlockSizeMode,
    Writer\AbstractGdWriter,
    Writer\Result\GdResult,
    Writer\Result\ResultInterface,
    Writer\WriterInterface};
use HeroQR\{Contracts\Customs\Drawers\PngDrawer,
    Customs\ImageOverlay, Customs\ShapePaths};

/**
 * The AbstractWriter class is responsible for generating QR code images using the GD library
 * This class provides base functionality for creating QR code images with options like logo and label embedding
 * Subclasses should implement specific logic for rendering QR codes using the GD image processing library
 */
readonly abstract class AbstractPngWriter extends AbstractGdWriter implements WriterInterface
{
    protected const QUALITY_MULTIPLIER = 10;
    protected ImageOverlay $imageOverlay;
    protected string $blockShape;

    public function __construct($background, $overlay, $blockShape)
    {
        $this->imageOverlay = new ImageOverlay($background, $overlay);
        $this->blockShape = ShapePaths::getValueByKey($blockShape) ?? 'drawSquare';
    }

    /**
     * Writes a QR code image with optional logo and label
     *
     * @param QrCodeInterface $qrCode The QR code to be generated
     * @param LogoInterface|null $logo The logo to be embedded in the QR code (optional)
     * @param LabelInterface|null $label The label to be added to the QR code (optional)
     * @param array $options Additional options for the QR code generation
     *
     * @return ResultInterface The result containing the generated QR code image
     * @throws \Exception If the GD extension is not loaded
     */
    public function write(
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo = null,
        ?LabelInterface $label = null,
        array           $options = []
    ): ResultInterface
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('Unable to generate image: please check if the GD extension is enabled and configured correctly');
        }

        $matrix = $this->getMatrix($qrCode);
        $baseBlockSize = (RoundBlockSizeMode::Margin === $qrCode->getRoundBlockSizeMode() ? 10 : intval($matrix->getBlockSize())) * self::QUALITY_MULTIPLIER;

        $baseImage = PngDrawer::drawQrCode($matrix, $qrCode, $logo, $this->imageOverlay, $this->blockShape, $baseBlockSize);

        $targetImage = $this->createTargetImage($matrix, $qrCode, $label);

        PngDrawer::copyResampledImage(
            $targetImage,
            $baseImage,
            ['X' => $matrix->getMarginLeft(), 'Y' => $matrix->getMarginRight(), 'Width' => $matrix->getInnerSize(), 'Height' => $matrix->getInnerSize()],
            ['X' => 0, 'Y' => 0, 'Width' => imagesy($baseImage), 'Height' => imagesy($baseImage)]
        );

        $this->destroyImages([$baseImage]);

        $result = new GdResult($matrix, $targetImage);

        if ($logo instanceof LogoInterface) {
            $result = $this->addLogoToResult($logo, $result);
        }

        if ($label instanceof LabelInterface) {
            $result = $this->addLabelToResult($label, $result);
        }

        return $result;
    }

    /**
     * Creates the target image for the QR code, including label if provided
     *
     * @param MatrixInterface $matrix The matrix representation of the QR code
     * @param QrCodeInterface $qrCode The QR code instance
     * @param LabelInterface|null $label The optional label to add below the QR code
     *
     * @return resource|\GdImage The created target image.
     * @throws \Exception
     */
    private function createTargetImage(
        MatrixInterface $matrix,
        QrCodeInterface $qrCode,
        ?LabelInterface $label
    ): \GdImage
    {
        $targetWidth = $matrix->getOuterSize();
        $targetHeight = $matrix->getOuterSize();

        if ($label instanceof LabelInterface) {
            $labelImageData = LabelImageData::createForLabel($label);
            $targetHeight += $labelImageData->getHeight() + $label->getMargin()->getTop() + $label->getMargin()->getBottom();
        }

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imageantialias($targetImage, true);
        imagesavealpha($targetImage, true);
        imagealphablending($targetImage, false);

        $backgroundColor = imagecolorallocatealpha(
            $targetImage,
            $qrCode->getBackgroundColor()->getRed(),
            $qrCode->getBackgroundColor()->getGreen(),
            $qrCode->getBackgroundColor()->getBlue(),
            $qrCode->getBackgroundColor()->getAlpha()
        );

        imagefill($targetImage, 0, 0, $backgroundColor);
        imagealphablending($targetImage, true);

        return $targetImage;
    }

    /**
     * Adds a logo to the image, centered and with optional punch out background
     * Throws an exception if the logo is not in PNG format
     *
     * @param LogoInterface $logo The logo to add
     * @param GdResult $result The image to add the logo to
     *
     * @return GdResult The updated image with the logo
     * @throws \Exception
     */
    private function addLogoToResult(
        LogoInterface $logo,
        GdResult      $result
    ): GdResult
    {
        $logoImageData = LogoImageData::createForLogo($logo);

        if ('image/png' !== $logoImageData->getMimeType()) {
            throw new \Exception('PNG Writer does not support ' . substr($logoImageData->getMimeType(), 6) . ' logo');
        }

        $targetImage = $result->getImage();
        $matrix = $result->getMatrix();

        $xOffsetStart = intval($matrix->getOuterSize() / 2 - $logoImageData->getWidth() / 2);
        $yOffsetStart = intval($matrix->getOuterSize() / 2 - $logoImageData->getHeight() / 2);

        if ($logoImageData->getPunchoutBackground()) {
            $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            imagealphablending($targetImage, false);

            for ($xOffset = $xOffsetStart; $xOffset < $xOffsetStart + $logoImageData->getWidth(); ++$xOffset) {
                for ($yOffset = $yOffsetStart; $yOffset < $yOffsetStart + $logoImageData->getHeight(); ++$yOffset) {
                    imagesetpixel($targetImage, $xOffset, $yOffset, $transparent);
                }
            }
        }

        imagealphablending($targetImage, true);
        imagesavealpha($targetImage, true);

        $logoImage = $logoImageData->getImage();
        imagealphablending($logoImage, true);

        PngDrawer::copyResampledImage(
            $targetImage, $logoImage,
            ['X' => $xOffsetStart, 'Y' => $yOffsetStart, 'Width' => $logoImageData->getWidth(), 'Height' => $logoImageData->getHeight()],
            ['X' => 0, 'Y' => 0, 'Width' => imagesx($logoImage), 'Height' => imagesy($logoImage)]
        );

        return new GdResult($matrix, $targetImage);
    }

    /**
     * Adds a label with text to the image
     *
     * The label's position is based on its alignment (left, center, right) and margin
     * The text is drawn using the specified font and color
     *
     * @param LabelInterface $label The label to add
     * @param GdResult $result The image to add the label to
     *
     * @return GdResult The updated image
     * @throws \Exception
     */
    private function addLabelToResult(
        LabelInterface $label,
        GdResult       $result
    ): GdResult
    {
        $targetImage = $result->getImage();
        $labelImageData = LabelImageData::createForLabel($label);

        $textColor = imagecolorallocatealpha(
            $targetImage,
            $label->getTextColor()->getRed(),
            $label->getTextColor()->getGreen(),
            $label->getTextColor()->getBlue(),
            $label->getTextColor()->getAlpha()
        );

        $x = intval(imagesx($targetImage) / 2 - $labelImageData->getWidth() / 2);
        $y = imagesy($targetImage) - $label->getMargin()->getBottom();

        if (LabelAlignment::Left === $label->getAlignment()) {
            $x = $label->getMargin()->getLeft();
        } elseif (LabelAlignment::Right === $label->getAlignment()) {
            $x = imagesx($targetImage) - $labelImageData->getWidth() - $label->getMargin()->getRight();
        }

        imagettftext($targetImage, $label->getFont()->getSize(), 0, $x, $y, $textColor, $label->getFont()->getPath(), $label->getText());

        return new GdResult($result->getMatrix(), $targetImage);
    }

    /**
     * Frees up memory by destroying image resources.
     *
     * @param array $images An array of image resources.
     * @return void
     */
    private function destroyImages(array $images): void
    {
        foreach ($images as $image) {
            imagedestroy($image);
        }
    }
}
