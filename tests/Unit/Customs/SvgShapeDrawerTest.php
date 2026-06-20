<?php

namespace HeroQR\Tests\Unit\Customs;

use HeroQR\Customs\ShapeDrawers\SvgShapeDrawer;
use Endroid\QrCode\Color\Color;
use PHPUnit\Framework\{Attributes\Test, TestCase};

/**
 * Class SvgShapeDrawerTest
 * Tests the SvgShapeDrawer class.
 */
class SvgShapeDrawerTest extends TestCase
{
    /**
     * Test drawing a square in SVG
     */
    #[Test]
    public function isCanDrawSquare(): void
    {
        $defs = new \SimpleXMLElement('<defs/>');
        $color = new Color(255, 0, 0);

        SvgShapeDrawer::drawSquare($defs, 0, 0, 20, $color);

        $this->assertNotNull($defs->rect);
        $this->assertEquals('shape_drawSquare', (string)$defs->rect[0]['id']);
        $this->assertEquals('#ff0000', (string)$defs->rect[0]['fill']);
    }

    /**
     * Test drawing a circle in SVG
     */
    #[Test]
    public function isCanDrawCircle(): void
    {
        $defs = new \SimpleXMLElement('<defs/>');
        $color = new Color(0, 0, 0);

        SvgShapeDrawer::drawCircle($defs, 0, 0, 20, $color);

        $this->assertNotNull($defs->circle);
        $this->assertEquals('0', (string)$defs->circle[0]['cx']);
        $this->assertEquals('8', (string)$defs->circle[0]['r']);
    }

    /**
     * Test drawing a star in SVG
     */
    #[Test]
    public function isCanDrawStar(): void
    {
        $defs = new \SimpleXMLElement('<defs/>');
        $color = new Color(0, 0, 0);

        SvgShapeDrawer::drawStar($defs, 0, 0, 20, $color);

        $this->assertNotNull($defs->polygon);
        $this->assertStringContainsString(',', (string)$defs->polygon[0]['points']);
    }

    /**
     * Test drawing a diamond in SVG
     */
    #[Test]
    public function isCanDrawDiamond(): void
    {
        $defs = new \SimpleXMLElement('<defs/>');
        $color = new Color(0, 0, 0);

        SvgShapeDrawer::drawDiamond($defs, 0, 0, 20, $color);

        $this->assertNotNull($defs->polygon);
        $this->assertEquals('shape_drawDiamond', (string)$defs->polygon[0]['id']);
    }

    /**
     * Test the <use> reference positioning
     */
    #[Test]
    public function isCanRenderUseReference(): void
    {
        $svg = new \SimpleXMLElement('<svg/>');
        $color = new Color(0, 0, 0);

        SvgShapeDrawer::drawSquare($svg, 5, 10, 10, $color);

        $this->assertNotNull($svg->use);
        $this->assertEquals('105', (string)$svg->use[0]['x']);
        $this->assertEquals('55', (string)$svg->use[0]['y']);
        $this->assertEquals('#shape_drawSquare', (string)$svg->use[0]['href']);
    }

    /**
     * Test alpha transparency conversion
     */
    #[Test]
    public function isCanApplyAlphaTransparency(): void
    {
        $defs = new \SimpleXMLElement('<defs/>');
        $color = new Color(0, 0, 0, 64);

        SvgShapeDrawer::drawSquare($defs, 0, 0, 20, $color);

        $alpha = (float)$defs->rect[0]['fill-opacity'];
        $this->assertEqualsWithDelta(0.5, $alpha, 0.01);
    }
}