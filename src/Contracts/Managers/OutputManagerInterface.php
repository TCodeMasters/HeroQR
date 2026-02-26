<?php

namespace HeroQR\Contracts\Managers;

use Endroid\QrCode\{Matrix\Matrix,Writer\Result\ResultInterface};

/**
 * This interface defines methods for managing QR Code output operations, including
 * saving the QR code to a file, retrieving its data URI, and getting the matrix
 * representation of the QR code.
 */
interface OutputManagerInterface
{
    /**
     * Save the QR code output to a file
     *
     * @param ResultInterface $builder QR code result builder
     * @param string $path File path without extension
     * @return bool True on success
     * @throws \InvalidArgumentException If the format is unsupported
     */
    public function saveTo(ResultInterface $builder, string $path): bool;

    /**
     * Return the data URI for the QR Code
     *
     * @param ResultInterface $builder
     * @return string
     */
    public function getDataUri(ResultInterface $builder): string;

    /**
     * Convert the QR Code matrix to a two-dimensional array
     *
     * @param ResultInterface $builder
     * @return array
     */
    public function getMatrixAsArray(ResultInterface $builder): array;

    /**
     * Return the QR Code matrix as a Matrix object
     *
     * @param ResultInterface $builder
     * @return Matrix
     */
    public function getMatrix(ResultInterface $builder): Matrix;

    /**
     * Return the QR Code output as a string
     *
     * @param ResultInterface $builder
     * @return string
     */
    public function getString(ResultInterface $builder): string;
}
