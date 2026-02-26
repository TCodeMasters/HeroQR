<?php

declare(strict_types=1);

namespace HeroQR\Core\Writers;

use Endroid\QrCode\{Label\LabelInterface,
    Logo\LogoInterface,
    QrCodeInterface,
    Writer\Result\ResultInterface,
    Writer\Result\SvgResult};
use HeroQR\Contracts\Customs\Writer\AbstractSvgWriter;

/**
 * Writes QR codes as SVG with custom options
 *
 * Extends the base SVG writer to allow optional compression and
 * handles embedding logos and labels in the QR code
 */
readonly class CustomSvgWriter extends AbstractSvgWriter
{
    public const WRITER_OPTION_COMPACT = 'compact';
    public const WRITER_OPTION_BLOCK_ID = 'block_id';
    public const WRITER_OPTION_EXCLUDE_XML_DECLARATION = 'exclude_xml_declaration';
    public const WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT = 'exclude_svg_width_and_height';

    /**
     * Generates an SVG QR code with optional logo, label, and customization options
     *
     * @param QrCodeInterface $qrCode The QR code instance
     * @param LogoInterface|null $logo Optional logo
     * @param LabelInterface|null $label Optional label
     * @param array $options Optional SVG flags (compact, block ID, exclude XML/size)
     * @return ResultInterface SVG result of the QR code
     * @throws \Exception
     */
    public function write(
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo = null,
        ?LabelInterface $label = null,
        array           $options = []): ResultInterface
    {
        if (!isset($options[self::WRITER_OPTION_COMPACT])) {
            $options[self::WRITER_OPTION_COMPACT] = true;
        }

        if (!isset($options[self::WRITER_OPTION_BLOCK_ID])) {
            $options[self::WRITER_OPTION_BLOCK_ID] = 'block';
        }

        if (!isset($options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION])) {
            $options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION] = false;
        }

        if (!isset($options[self::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT])) {
            $options[self::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT] = false;
        }

        /**
         * @var SvgResult $gdResult
         */
        $SvgResult = parent::write($qrCode, $logo, $label, $options);

        return new SvgResult(
            $SvgResult->getMatrix(),
            $SvgResult->getXml(),
            boolval($options[self::WRITER_OPTION_EXCLUDE_XML_DECLARATION])
        );
    }
}
