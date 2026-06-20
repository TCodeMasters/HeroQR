<?php

declare(strict_types=1);

namespace HeroQR\Managers;

use HeroQR\Contracts\Managers\LogoManagerInterface;

/**
 * Manages logo settings for QR codes, including file path, size, and background options
 */
class LogoManager implements LogoManagerInterface
{
    private string $logoPath = '';
    private int $logoSize = 80;
    private bool $logoBackground = false;

    /**
     * Set the logo file path
     *
     * @param string $logoPath File path to the logo
     * @throws \InvalidArgumentException If the file does not exist or is not readable
     */
    public function setLogo(string $logoPath): void
    {
        if (!file_exists($logoPath) || !is_readable($logoPath)) {
            throw new \InvalidArgumentException("Logo Path '{$logoPath}' Does Not Exist Or Is Not Readable");
        }

        $this->logoPath = $logoPath;
    }

    /**
     * Get the current logo path
     * 
     * @return string The logo file path
     */
    public function getLogoPath(): string
    {
        return $this->logoPath;
    }

    /**
     * Enable or disable logo background
     *
     * @param bool $logoBackground True to enable background, false to disable
     */
    public function setLogoBackground(bool $logoBackground): void
    {
        $this->logoBackground = $logoBackground;
    }

    /**
     * Check if logo background is enabled
     *
     * @return bool True if background is enabled, false otherwise
     */
    public function getLogoBackground(): bool
    {
        return $this->logoBackground;
    }

    /**
     * Set the logo size
     *
     * @param int $size Positive integer for logo size
     * @throws \InvalidArgumentException If size is not positive
     */
    public function setLogoSize(int $size): void
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Logo Size Must Be A Positive Integer');
        }

        $this->logoSize = $size;
    }

    /**
     * Get the current logo size
     * 
     * @return int The size of the logo
     */
    public function getLogoSize(): int
    {
        return $this->logoSize;
    }
}
