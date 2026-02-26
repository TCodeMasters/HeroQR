<?php

namespace HeroQR\Tests\Unit\Managers;

use PHPUnit\Framework\{Attributes\DataProvider, Attributes\Test, TestCase};
use Endroid\QrCode\Writer\{PngWriter, WebPWriter, SvgWriter, PdfWriter, GifWriter, EpsWriter, BinaryWriter};
use HeroQR\{Contracts\Managers\AbstractWriterManager,
    Core\Writers\CustomPngWriter,
    Core\Writers\CustomSvgWriter,
    Managers\WriterManager};

/**
 * Class WriterManagerTest
 * Tests the WriterManager class
 */
class WriterManagerTest extends TestCase
{
    private WriterManager $writerManager;

    /**
     * Initializes the WriterManager instance
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->writerManager = new WriterManager();

        $this->assertInstanceOf(AbstractWriterManager::class, $this->writerManager);
    }

    /**
     * Provides a mapping of file formats to their respective writer class implementations
     */
    public static function writerFormatProvider() : array
    {
        return [
            'PNG Writer' => ['png', PngWriter::class],
            'SVG Writer' => ['svg', SvgWriter::class],
            'PDF Writer' => ['pdf', PdfWriter::class],
            'GIF Writer' => ['gif', GifWriter::class],
            'EPS Writer' => ['eps', EpsWriter::class],
            'WEBP Writer' => ['webp', WebPWriter::class],
            'BINARY Writer' => ['binary', BinaryWriter::class],
        ];
    }
    
    /** 
     * Test that the getWriter method returns a standard Writer
     */
    #[Test]
    #[DataProvider('writerFormatProvider')]
    public function isGetWriterStandard(string $format, string $expectedClass): void
    {
        $writer = $this->writerManager->getWriter($format);

        $this->assertInstanceOf($expectedClass, $writer, "Writer for format '{$format}' is not an instance of expected class.");
    }

    /** 
     * Test that the getWriter method returns a custom Png Writer
     */
    #[Test]
    public function isGetWriterWithCustomPngParameters()
    {
        $getWriter = $this->writerManager->getWriter('png', [
            'Marker' => 'M1',
            'Cursor' => 'C1',
            'Shape' => 'S1'
        ]);

        $this->assertIsObject($getWriter);
        $this->assertInstanceOf(CustomPngWriter::class, $getWriter);
    }

    /**
     * Test that the getWriter method returns a custom Svg Writer
     */
    #[Test]
    public function isGetWriterWithCustomSvgParameters()
    {
        $getWriter = $this->writerManager->getWriter('svg', [
            'Marker' => 'M1',
            'Cursor' => 'C1',
            'Shape' => 'S1'
        ]);

        $this->assertIsObject($getWriter);
        $this->assertInstanceOf(CustomSvgWriter::class, $getWriter);
    }

    /**
     * Test that the getWriter method throws an exception when unsupported custom format
     */
    #[Test]
    public function isGetWriterWithInvalidCustomParameters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Custom writers not supported for 'pdf'");

        $customs = ['Marker' => 'M1'];
        $this->writerManager->getWriter('pdf', $customs);
    }

    /**
     * Test that the getWriter method throws an exception when an unsupported standard format
     */
    #[Test]
    public function isGetWriterWithInvalidFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported format 'txt'. Supported formats: png, svg, eps, pdf, binary, webp, gif");

        $this->writerManager->getWriter('txt');
    }
}