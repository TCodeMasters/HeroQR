<?php

namespace HeroQR\Core\Writers;

use Endroid\QrCode\{Label\LabelInterface,
    Logo\LogoInterface,
    QrCodeInterface,
    Writer\Result\GdResult,
    Writer\Result\PngResult,
    Writer\Result\ResultInterface};
use HeroQR\Contracts\Customs\Writer\AbstractPngWriter;

/**
 * Writes QR codes as PNG with custom options
 *
 * Extends the base PNG writer to allow optional compression and
 * handles embedding logos and labels in the QR code
 */
readonly class CustomPngWriter extends AbstractPngWriter
{
    public const WRITER_OPTION_COMPRESSION_LEVEL = 'compression_level';

    /**
     * Generates an PNG QR code with optional logo, label, and customization options
     *
     * @param QrCodeInterface $qrCode QR Code instance to render
     * @param LogoInterface|null $logo Optional logo to embed in the QR code
     * @param LabelInterface|null $label Optional label to add to the QR code
     * @param array $options Optional PNG flags (compression level and...)
     * @return ResultInterface PNG result of the QR code
     * @throws \Exception
     */
    public function write(
        QrCodeInterface $qrCode,
        ?LogoInterface  $logo = null,
        ?LabelInterface $label = null,
        array           $options = []
    ): ResultInterface
    {

        $options[self::WRITER_OPTION_COMPRESSION_LEVEL] = $options[self::WRITER_OPTION_COMPRESSION_LEVEL] ?? 1;

        /**
         * @var GdResult $gdResult
         */
        $gdResult = parent::write($qrCode, $logo, $label, $options);

        return new PngResult(
            $gdResult->getMatrix(),
            $gdResult->getImage(),
            $options[self::WRITER_OPTION_COMPRESSION_LEVEL]
        );
    }
}
