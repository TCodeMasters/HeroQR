<?php

declare(strict_types=1);

namespace HeroQR\Contracts\Customs\Writer;

use Endroid\QrCode\{QrCodeInterface,
    ImageData\LogoImageData,
    Logo\LogoInterface,
    Label\LabelInterface,
    Writer\AbstractGdWriter,
    Writer\WriterInterface,
    Writer\Result\ResultInterface,
    Writer\Result\SvgResult,
    RoundBlockSizeMode};
use HeroQR\{Contracts\Customs\Drawers\SvgDrawer,
    Customs\ImageOverlay, Customs\ShapePaths};

/**
 * The AbstractWriter class is responsible for generating QR code images using the GD library
 * This class provides base functionality for creating QR code images with options like logo and label embedding
 * Subclasses should implement specific logic for rendering QR codes using the GD image processing library
 */
readonly abstract class AbstractSvgWriter extends AbstractGdWriter implements WriterInterface
{
    public const WRITER_OPTION_FORCE_XLINK_HREF = 'force_xlink_href';
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
     *
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
        $baseBlockSize = (RoundBlockSizeMode::Margin === $qrCode->getRoundBlockSizeMode() ? 10 : intval($matrix->getBlockSize()));

        $xml = SvgDrawer::drawQrCode($matrix, $qrCode, $logo, $this->imageOverlay, $this->blockShape, $baseBlockSize);

        if (empty($xml)) {
            throw new \Exception('SvgDrawer::drawQrCode() returned empty XML');
        }

        $result = new SvgResult($matrix, $xml, boolval($options['exclude_xml_declaration']));

        if ($logo instanceof LogoInterface) {
            $this->addLogoToResult($logo, $result, $options);
        }

        return $result;
    }

    /**
     * Adds a logo to the image, centered and with optional punch out background
     * Throws an exception if the logo is not in PNG format
     * 
     * @param LogoInterface $logo The logo to add
     * @param SvgResult $result The image to add the logo to
     * @param array $options
     *
     * @return void
     * @throws \Exception
     */
    private function addLogoToResult(
        LogoInterface $logo,
        SvgResult     $result,
        array         $options
    ): void
    {
        if ($logo->getPunchoutBackground()) {
            throw new \Exception('The SVG writer does not support logo punchout background');
        }

        $logoImageData = LogoImageData::createForLogo($logo);

        if (!isset($options[self::WRITER_OPTION_FORCE_XLINK_HREF])) {
            $options[self::WRITER_OPTION_FORCE_XLINK_HREF] = false;
        }

        $xml = $result->getXml();

        /** @var \SimpleXMLElement $xmlAttributes */
        $xmlAttributes = $xml->attributes();

        $x = intval($xmlAttributes->width) / 2 - $logoImageData->getWidth() / 2;
        $y = intval($xmlAttributes->height) / 2 - $logoImageData->getHeight() / 2;

        $imageDefinition = $xml->addChild('image');
        $imageDefinition->addAttribute('x', strval($x));
        $imageDefinition->addAttribute('y', strval($y));
        $imageDefinition->addAttribute('width', strval($logoImageData->getWidth()));
        $imageDefinition->addAttribute('height', strval($logoImageData->getHeight()));
        $imageDefinition->addAttribute('preserveAspectRatio', 'none');

        if ($options[self::WRITER_OPTION_FORCE_XLINK_HREF]) {
            $imageDefinition->addAttribute('xlink:href', $logoImageData->createDataUri(), 'http://www.w3.org/1999/xlink');
        } else {
            $imageDefinition->addAttribute('href', $logoImageData->createDataUri());
        }
    }
}