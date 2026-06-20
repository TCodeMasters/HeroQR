<?php

namespace HeroQR\Tests\Unit\Core\Writers;

use Endroid\QrCode\Matrix\Matrix;
use HeroQR\Core\QRCodeGenerator;
use PHPUnit\Framework\{Attributes\Test, TestCase};

/**
 * Class CustomPngWriterTest
 * Tests the CustomPngWriter class
 */
class CustomSvgWriterTest extends TestCase
{
    private QRCodeGenerator $qrCodeGenerator;

    /**
     * Initializes the QRCodeGenerator instance
     */
    protected function setUp(): void
    {
        $this->qrCodeGenerator = new QRCodeGenerator();
    }

    /**
     * Set up the QRCodeGenerator instance before each test
     */
    #[Test]
    public function isGetMatrixValid(): void
    {
        $matrix = $this->qrCodeGenerator->setSize(250)
            ->generate('svg', [
                'Shape' => "S2",
                'Marker' => "M3",
                'Cursor' => "C4",
            ])->getMatrix();

        $this->assertIsObject($matrix);
        $this->assertInstanceOf(Matrix::class, $matrix);
    }

    /**
     * Test generating QR code matrix validity
     */
    #[Test]
    public function isInvalidMarkerAndCursor(): void
    {
        for ($i = 0; $i <= 2; $i++) {

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessageMatches('/Invalid key \'.+\' provided. Valid keys are : .+/');

            $this->qrCodeGenerator->setSize(100)
                ->generate('svg', [
                    'Shape' => 'S5',
                    'Marker' => 'M5',
                    'Cursor' => 'C5'
                ]);
        }
    }

    /**
     * Test invalid custom QR code format
     */
    #[Test]
    public function isInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Custom writers not supported for 'webp'");

        $this->qrCodeGenerator->setSize(100)
            ->generate('webp', [
                'Shape' => 'S' . random_int(1, 4),
                'Marker' => 'M' . random_int(1, 6),
                'Cursor' => 'C' . random_int(1, 6),
            ]);
    }

    /**
     * Test generating QR code with logo
     */
    #[Test]
    public function isWriteWithLogoAndLabel(): void
    {
        $tempDir = sys_get_temp_dir();
        $logoPath = $tempDir . DIRECTORY_SEPARATOR . 'testLogo_' . uniqid() . '.png';

        $image = imagecreatetruecolor(100, 100);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $backgroundColor);
        imagepng($image, $logoPath);
        imagedestroy($image);

        try {
            $this->qrCodeGenerator->setLogo($logoPath, 50);

            $result = $this->qrCodeGenerator->generate('svg', [
                'Shape' => "S2",
                'Marker' => "M2",
                'Cursor' => "C3",
            ])->getDataUri();

            $this->assertStringStartsWith('data:image/svg+xml;base64', $result, 'Data URI should start with the correct prefix.');
            $this->assertNotEmpty($result);

        } finally {
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }
    }
}
