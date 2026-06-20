<?php

declare(strict_types=1);

namespace HeroQR\Managers;

use Endroid\QrCode\{Matrix\Matrix,Writer\Result\ResultInterface};
use HeroQR\Contracts\Managers\OutputManagerInterface;

/**
 * Handles QR code output operations: saving to files, generating data URIs, retrieving matrices, and converting to strings
 */
class OutputManager implements OutputManagerInterface
{
    /**
     * Save the QR code output to a file
     *
     * @param ResultInterface $builder QR code result builder
     * @param string $path File path without extension
     * @return bool True on success
     * @throws \InvalidArgumentException If the format is unsupported
     */
    public function saveTo(ResultInterface $builder, string $path): bool
    {
        $mimeMap = [
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
            'image/eps' => 'eps',
            'application/pdf' => 'pdf',
            'application/postscript' => 'eps',
            'application/octet-stream' => 'bin',
            'text/plain' => 'bin',
        ];

        $format = strtolower($builder->getMimeType());
        if (!isset($mimeMap[$format])) {
            throw new \InvalidArgumentException('Unsupported format');
        }

        $fullPath = $path . '.' . $mimeMap[$format];
        $builder->saveToFile($fullPath);

        return true;
    }

    /**
     * Return the data URI for the QR Code
     *
     * @param ResultInterface $builder
     * @return string
     */
    public function getDataUri(ResultInterface $builder): string
    {
        return $builder->getDataUri();
    }

    /**
     * Convert the QR Code matrix to a two-dimensional array
     *
     * @param ResultInterface $builder
     * @return array
     */
    public function getMatrixAsArray(ResultInterface $builder): array
    {
        $matrix = $builder->getMatrix();
        $blockCount = $matrix->getBlockCount();
        $matrixArray = array_fill(0, $blockCount, array_fill(0, $blockCount, 0));

        for ($row = 0; $row < $blockCount; $row++) {
            for ($col = 0; $col < $blockCount; $col++) {
                $matrixArray[$row][$col] = $matrix->getBlockValue($row, $col);
            }
        }

        return $matrixArray;
    }

    /**
     * Return the QR Code matrix as a Matrix object
     *
     * @param ResultInterface $builder
     * @return Matrix
     */
    public function getMatrix(ResultInterface $builder): Matrix
    {
        return $builder->getMatrix();
    }

    /**
     * Return the QR Code output as a string
     *
     * @param ResultInterface $builder
     * @return string
     */
    public function getString(ResultInterface $builder): string
    {
        return $builder->getString();
    }
}
