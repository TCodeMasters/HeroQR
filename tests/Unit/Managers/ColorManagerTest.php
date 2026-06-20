<?php

namespace HeroQR\Tests\Unit\Managers;

use PHPUnit\Framework\{Attributes\Test, TestCase};
use HeroQR\{Contracts\Managers\ColorManagerInterface, Managers\ColorManager};

/**
 * Class ColorManagerTest
 * Tests the ColorManager class.
 */
class ColorManagerTest extends TestCase
{
    private ColorManager $colorManager;

    /**
     * Initializes the ColorManager instance
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->colorManager = new ColorManager();

        $this->assertInstanceOf(ColorManagerInterface::class, $this->colorManager, 'The colorManager must implement ColorInterface');
    }

    /**
     * Test that the default colors are set correctly in the ColorManager
     */
    #[Test]
    public function isGetdefaultColors(): void
    {
        $colorManager = $this->colorManager;

        $defaultColor = $colorManager->getColor();
        $this->assertEquals([0, 0, 0], [$defaultColor->getRed(), $defaultColor->getGreen(), $defaultColor->getBlue()], 'The default QR code color should be black');

        $defaultBackgroundColor = $colorManager->getBackgroundColor();
        $this->assertEquals([255, 255, 255], [$defaultBackgroundColor->getRed(), $defaultBackgroundColor->getGreen(), $defaultBackgroundColor->getBlue()], 'The default background color should be white.');

        $defaultLabelColor = $colorManager->getLabelColor();
        $this->assertEquals([0, 0, 0], [$defaultLabelColor->getRed(), $defaultLabelColor->getGreen(), $defaultLabelColor->getBlue()], 'The default label color should be black.');
    }

    /**
     * Test setting and retrieving a custom QR code color
     */
    #[Test]
    public function isSetAndGetColor(): void
    {
        $colorManager = $this->colorManager;

        $colorManager->setColor(['Red' => 255, 'Green' => 87 , 'Blue' => 51, 'Alpha' => 0.8]);
        $customColor = $colorManager->getColor();

        $this->assertEqualsWithDelta([255, 87, 51, 0.8], [
            $customColor->getRed(),
            $customColor->getGreen(),
            $customColor->getBlue(),
            $customColor->getOpacity()
        ],0.01, 'The custom QR code color should match the provided hex value');
    }

    /**
     * Test setting and retrieving a custom background color
     */
    #[Test]
    public function isSetAndGetBackgroundColor(): void
    {
        $colorManager = $this->colorManager;

        $colorManager->setBackgroundColor(['Red' => 255, 'Green' => 87 , 'Blue' => 51]);
        $backgroundColor = $colorManager->getBackgroundColor();

        $this->assertEqualsWithDelta([255, 87, 51, 1], [
            $backgroundColor->getRed(),
            $backgroundColor->getGreen(),
            $backgroundColor->getBlue(),
            $backgroundColor->getOpacity()
        ],0.01, 'The custom background color should match the provided RGBA value');
    }

    /**
     * Test setting and retrieving a custom label color
     */
    #[Test]
    public function isSetAndGetLabelColor(): void
    {
        $colorManager = $this->colorManager;

        $colorManager->setLabelColor(['Red' => 100, 'Green' => 87 , 'Blue' => 51]);
        $labelColor = $colorManager->getLabelColor();

        $this->assertEqualsWithDelta([100, 87, 51, 1], [
            $labelColor->getRed(),
            $labelColor->getGreen(),
            $labelColor->getBlue(),
            $labelColor->getOpacity()
        ],0.01, 'The custom label color should match the provided RGBA value');
    }

    /**
     * Test Invalid background color
     */
    #[Test]
    public function isInvalidBackgroundColor(): void
    {
        $colorManager = $this->colorManager;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Blue must be integer (0-255). Input: 290');
        $colorManager->setBackgroundColor(['Red' => 255, 'Green' => 87 , 'Blue' => 290]);
    }
}
