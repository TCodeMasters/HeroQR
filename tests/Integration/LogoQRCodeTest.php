<?php

namespace HeroQR\Tests\Integration;

use HeroQR\{Core\QRCodeGenerator, DataTypes\DataType};
use PHPUnit\Framework\{Attributes\Test, TestCase};

/**
 * Class LogoQRCodeTest
 * Tests the logo functionality of QRCodeGenerator for both PNG and SVG formats
 */
class LogoQRCodeTest extends TestCase
{
    private QRCodeGenerator $qrCodeGenerator;
    private string $outputPath;

    /**
     * Initializes the QRCodeGenerator instance
     */
    protected function setUp(): void
    {
        $this->qrCodeGenerator = new QRCodeGenerator();
        $this->outputPath = './testQrcodeLogo-' . uniqid();
    }

    /**
     * Test generating QR code with a logo for both PNG and SVG formats
     */
    #[Test]
    public function isGeneratesQrcodeWithLogo(): void
    {
        $logoPath = $this->createLogo();
        $this->configureQrCodeGenerator($logoPath);

        foreach (['png', 'svg'] as $format) {
            $this->qrCodeGenerator->generate($format);
            $this->qrCodeGenerator->saveTo($this->outputPath);

            $fullPath = $this->outputPath . '.' . $format;
            $this->assertFileExists($fullPath);

            $content = file_get_contents($fullPath);
            $this->assertNotEmpty($content);

            // Specific check for SVG format to ensure logo reference exists
            if ($format === 'svg') {
                $this->assertStringContainsString('<image', $content);
            }

            unlink($fullPath);
        }

        $this->cleanUp([$logoPath]);
    }

    /**
     * Test that providing an invalid logo path
     */
    #[Test]
    public function isFailsWithInvalidLogoPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->configureQrCodeGenerator('./path/to/nonexistent/logo.png');
        $this->qrCodeGenerator->generate('png');
    }

    /**
     * Test generating an PNG QR code without a logo
     */
    #[Test]
    public function isGeneratesPngWithoutLogo(): void
    {
        $this->configureQrCodeGenerator();

        $this->qrCodeGenerator->generate('png');
        $this->qrCodeGenerator->saveTo($this->outputPath);

        $this->assertFileExists($this->outputPath . '.png');
        $this->assertNotEmpty(file_get_contents($this->outputPath . '.png'));

        unlink($this->outputPath . '.png');
    }

    /**
     * Test generating an SVG QR code without a logo
     */
    public function isGeneratesSvgWithoutLogo(): void
    {
        $this->configureQrCodeGenerator();

        $this->qrCodeGenerator->generate('svg');
        $this->qrCodeGenerator->saveTo($this->outputPath);

        $fullPath = $this->outputPath . '.svg';
        $this->assertFileExists($fullPath);

        $content = file_get_contents($fullPath);
        $this->assertStringNotContainsString('<image', $content);

        unlink($fullPath);
    }

    /**
     * Helper method to configure QRCodeGenerator with common settings
     */
    private function configureQrCodeGenerator(string $logoPath = ''): void
    {
        $this->qrCodeGenerator->setData('https://example.com', DataType::Url);
        $this->qrCodeGenerator->setSize(300);
        $this->qrCodeGenerator->setMargin(20);
        $this->qrCodeGenerator->setColor(12, 20, 200);
        $this->qrCodeGenerator->setBackgroundColor(0, 0, 0);

        if ($logoPath) {
            $this->qrCodeGenerator->setLogo($logoPath);
        }
    }

    /**
     * Create a simple logo image in memory for testing purposes
     */
    private function createLogo(): string
    {
        $image = imagecreatetruecolor(100, 100);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $circleColor = imagecolorallocate($image, 255, 87, 51);

        imagefill($image, 0, 0, $bgColor);
        imagefilledellipse($image, 50, 50, 80, 80, $circleColor);

        $logoPath = './test_logo.png';
        imagepng($image, $logoPath);
        imagedestroy($image);

        return $logoPath;
    }

    /**
     * Helper method to clean up generated files
     */
    private function cleanUp(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
