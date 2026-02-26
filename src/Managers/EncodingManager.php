<?php

declare(strict_types=1);

namespace HeroQR\Managers;

use Endroid\QrCode\Encoding\{EncodingInterface,Encoding};
use HeroQR\Contracts\Managers\EncodingManagerInterface;

/**
 * Manages the character encoding for QR codes
 * Ensures proper encoding for different data types and provides methods
 * to set or retrieve the current encoding
 */
class EncodingManager implements EncodingManagerInterface
{
    private EncodingInterface $encoding;

    /**
     * EncodingManager constructor
     * Initializes the encoding with a default value of 'UTF-8'
     */
    public function __construct()
    {
        $this->encoding = new Encoding('UTF-8');
    }

    /**
     * Get the current encoding
     *
     * @return EncodingInterface The current encoding object
     */
    public function getEncoding(): EncodingInterface
    {
        return $this->encoding;
    }

    /**
     * Set a new encoding for QR code generation
     *
     * @param string $encoding The desired encoding (e.g., 'UTF-8', 'UTF-16', 'ASCII', 'ISO-8859-1', etc.)
     * @throws \InvalidArgumentException If the encoding string is empty
     * @throws \Exception If the encoding is invalid or unsupported by the library
     */
    public function setEncoding(string $encoding): void
    {
        if (empty($encoding)) {
            throw new \InvalidArgumentException('Encoding Cannot Be Empty');
        }

        $this->encoding = new Encoding($encoding);
    }
}
