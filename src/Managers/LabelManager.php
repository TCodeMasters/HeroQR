<?php

declare(strict_types=1);

namespace HeroQR\Managers;

use Endroid\QrCode\{Color\ColorInterface, Label\LabelAlignment};
use Endroid\QrCode\Label\{Font\FontInterface, Font\OpenSans,Margin\Margin, Margin\MarginInterface};
use HeroQR\Contracts\Managers\LabelManagerInterface;

/**
 * Manages QR code label settings, including text, font, color, margin, and alignment
 * Provides methods to customize the appearance and positioning of labels
 */
class LabelManager implements LabelManagerInterface
{
    private string $label = '';

    /**
     * LabelManager constructor
     *
     * @param ColorManager $labelColor Color manager to handle label colors
     * @param MarginInterface $labelMargin Margin for the label [top, right, bottom, left] (default: [0,10,10,10])
     * @param FontInterface $labelFont Font used for the label (default: OpenSans size 20)
     * @param LabelAlignment $labelAlign Alignment of the label (default: center)
     */
    public function __construct(
        private readonly ColorManager $labelColor,
        private MarginInterface       $labelMargin = new Margin(0, 10, 10, 10),
        private FontInterface         $labelFont = new OpenSans(20),
        private LabelAlignment        $labelAlign = LabelAlignment::Center
    ){}

    /**
     * Set the label text
     *
     * @param string $label Text to display
     * @throws \InvalidArgumentException If text is empty or exceeds 200 characters
     */
    public function setLabel(string $label): void
    {
        $labelLength = strlen($label);
        if ($labelLength === 0) {
            throw new \InvalidArgumentException('Label Text Cannot Be Empty');
        }

        if ($labelLength > 200) {
            throw new \InvalidArgumentException('Label Text Cannot Exceed 200 Characters');
        }

        $this->label = htmlspecialchars($label);
    }

    /**
     * Get the current label text
     *
     * @return string The label text
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the font used for the label
     *
     * @return FontInterface The font used for the label text
     */
    public function getLabelFont(): FontInterface
    {
        return $this->labelFont;
    }

    /**
     * Set the font size for the label
     *
     * @param int $size Font size (must be positive)
     * @throws \InvalidArgumentException If the size is not a positive integer
     */
    public function setLabelSize(int $size): void
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Font Size Must Be A Positive Integer');
        }

        $this->labelFont = new OpenSans($size);
    }

    /**
     * Set the alignment for the label
     *
     * @param string $labelAlign The alignment (left, center, or right)
     * @throws \InvalidArgumentException If an invalid alignment is provided
     */
    public function setLabelAlign(string $labelAlign): void
    {
        $labelAlign = strtolower($labelAlign);

        if (!in_array($labelAlign, ['left', 'center', 'right'], true)) {
            throw new \InvalidArgumentException('Invalid Label Alignment. Allowed Values Are "left", "center", or "right"');
        }

        $this->labelAlign = LabelAlignment::from($labelAlign);
    }

    /**
     * Get the current label alignment
     *
     * @return LabelAlignment The current label alignment
     */
    public function getLabelAlign(): LabelAlignment
    {
        return $this->labelAlign;
    }

    /**
     * Set the label color (hex format)
     *
     * @param array $color Hex color string (e.g., '#000000')
     * @throws \InvalidArgumentException If color format is invalid
     */
    public function setLabelColor(array $color): void
    {
        $this->labelColor->setLabelColor($color);
    }

    /**
     * Get the current label color
     *
     * @return ColorInterface The current label color
     */
    public function getLabelColor(): ColorInterface
    {
        return $this->labelColor->getLabelColor();
    }

    /**
     * Set the label margin.
     *
     * @param array $margin Array of 4 values [top, right, bottom, left]
     * @throws \InvalidArgumentException If array count is not 4 or values are invalid
     */
    public function setLabelMargin(array $margin): void
    {
        if (count($margin) !== 4) {
            throw new \InvalidArgumentException('Margin Array Must Contain Exactly 4 Values [top, right, bottom, left]');
        }

        foreach ($margin as $key => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("Margin Value At Index {$key} Must Be Numeric Value");
            }

            if ($value < -250 || $value > 250) {
                throw new \InvalidArgumentException("Margin Value At Index {$key} Must Be Between -250 And 250");
            }
        }

        $this->labelMargin = new Margin($margin[0], $margin[1], $margin[2], $margin[3]);
    }

    /**
     * Get the current label margin
     *
     * @return MarginInterface The current label margin
     */
    public function getLabelMargin(): MarginInterface
    {
        return $this->labelMargin;
    }
}
