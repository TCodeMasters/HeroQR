<?php

namespace HeroQR\Tests\Integration;

use HeroQR\{Core\QRCodeGenerator, DataTypes\DataType};
use PHPUnit\Framework\{Attributes\DataProvider, Attributes\Test, TestCase};

/**
 * Class QRCodeExportTest
 * Tests the export functionality of QRCodeGenerator
 */
class QRCodeExportTest extends TestCase
{
    private QRCodeGenerator $qrCodeGenerator;
    private string $outputPath;

    /**
     * Initializes the QRCodeGenerator instance
     */
    protected function setUp(): void
    {
        $this->qrCodeGenerator = new QRCodeGenerator();
        $this->outputPath = './testQrcode-' . uniqid();
    }

    /**
     * Provides supported export formats
     */
    public static function formatProvider(): array
    {
        return [['png'], ['svg'], ['webp'], ['gif'], ['eps'], ['binary']];
    }

    /**
     * Test exporting QR code to multiple formats using a data provider
     */
    #[Test]
    #[DataProvider('formatProvider')]
    public function isExportsQrcodeToSupportedFormats(string $format): void
    {
        $this->prepareQRCodeGenerator();

        $this->qrCodeGenerator->generate($format);
        $this->qrCodeGenerator->saveTo($this->outputPath);

        $extension = ($format === 'binary' ? 'bin' : $format);
        $file = $this->outputPath . '.' . $extension;

        $this->assertFileExists($file);
        $this->assertNotEmpty(file_get_contents($file));

        if ($format === 'svg') {
            $content = file_get_contents($file);
            $this->assertStringContainsString('<svg', $content);
            $this->assertStringContainsString('</svg>', $content);
        }

        $this->deleteFile($file);
    }

    /**
     * Test exporting to PDF format conditionally
     */
    #[Test]
    public function isExportsQrcodeToPdfIfAvailable(): void
    {
        $this->prepareQRCodeGenerator();

        if (class_exists('FPDF')) {
            $this->qrCodeGenerator->generate('pdf');
            $this->qrCodeGenerator->saveTo($this->outputPath);

            $file = $this->outputPath . '.pdf';

            $this->assertFileExists($file);
            $this->assertNotEmpty(file_get_contents($file));

            $this->deleteFile($file);
        } else {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Unable to find FPDF: check your installation');
            $this->qrCodeGenerator->generate('pdf');
        }
    }

    /**
     * Test exporting with invalid format
     */
    #[Test]
    public function isFailsExportingWithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->prepareQRCodeGenerator();
        $this->qrCodeGenerator->generate('invalid-format');
    }

    /**
     * Test export without calling generate() first
     */
    #[Test]
    public function isFailsExportingWithoutGenerateCall(): void
    {
        $this->expectException(\Error::class);
        $this->qrCodeGenerator->saveTo($this->outputPath);
    }

    /**
     * Set up common QR code generator settings
     */
    private function prepareQRCodeGenerator(): void
    {
        $this->qrCodeGenerator->setData('https://example.com', DataType::Url);
        $this->qrCodeGenerator->setSize(300);
        $this->qrCodeGenerator->setMargin(20);
        $this->qrCodeGenerator->setColor(10, 40, 100, 1.0);
        $this->qrCodeGenerator->setBackgroundColor(23, 49, 150, 0.5);
    }

    /**
     * Delete generated file
     */
    private function deleteFile(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}