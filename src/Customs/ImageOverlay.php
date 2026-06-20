<?php

namespace HeroQR\Customs;

use Endroid\QrCode\Color\ColorInterface;

/**
 * Handles overlaying a cursor image on a background image
 * Provides methods to save, output, or get the image as a Base64 string
 */
class ImageOverlay
{
    private ?string $backgroundPath;
    private ?string $overlayPath;

    /**
     * Constructor to initialize background and overlay paths
     */
    public function __construct($background, $overlay)
    {
        $this->overlayPath = CursorPaths::getValueByKey($overlay);
        $this->backgroundPath = MarkerPaths::getValueByKey($background);
    }

    /**
     * Saves the generated image to the specified output path
     *
     * @param string $outputPath
     * @param ColorInterface|null $overlayColor
     * @return void
     * @throws \Exception
     */
    public function saveImage(string $outputPath, ?ColorInterface $overlayColor = null): void
    {
        $result = $this->createCenteredImage($overlayColor);

        imagepng($result, $outputPath);
        imagedestroy($result);
    }

    /**
     * Returns the image as a base64-encoded URI
     *
     * @param ColorInterface|null $overlayColor
     * @return string
     * @throws \Exception
     */
    public function getUriImage(?ColorInterface $overlayColor = null): string
    {
        return $this->getImageAsBase64(true, $overlayColor);
    }

    /**
     * Returns the image as a string
     *
     * @param ColorInterface|null $overlayColor
     * @return string
     * @throws \Exception
     */
    public function getImageAsString(?ColorInterface $overlayColor = null): string
    {
        return $this->getImageAsBase64(false, $overlayColor);
    }

    /**
     * Returns the generated image as a GdImage instance
     *
     * @param ColorInterface|null $overlayColor
     * @return \GdImage
     * @throws \Exception
     */
    public function getImage(?ColorInterface $overlayColor = null): \GdImage
    {
        return $this->createCenteredImage($overlayColor);
    }

    /**
     * Outputs the image directly to the browser
     *
     * @param ColorInterface|null $overlayColor
     * @return void
     * @throws \Exception
     */
    public function outputImage(?ColorInterface $overlayColor = null): void
    {
        $result = $this->createCenteredImage($overlayColor);

        header('Content-Type: image/png');
        imagepng($result);
        imagedestroy($result);
    }

    /**
     * Creates a centered image by overlaying one image on another
     */
    private function createCenteredImage(?ColorInterface $color = null): \GdImage
    {
        $this->validatePaths();

        $background = $this->createImageResource($this->backgroundPath);
        $overlay = $this->createImageResource($this->overlayPath);

        if ($color !== null) {
            $background = $this->recolorImage($background, $color);
            $overlay = $this->recolorImage($overlay, $color);
        }

        $bgWidth = imagesx($background);
        $bgHeight = imagesy($background);
        $overlayWidth = (int)(imagesx($overlay) / 3.1);
        $overlayHeight = (int)(imagesy($overlay) / 3.1);

        $x = (int)(($bgWidth - $overlayWidth) / 2);
        $y = (int)(($bgHeight - $overlayHeight) / 2);

        imagealphablending($background, true);
        imagesavealpha($background, true);

        imagecopyresampled(
            $background, $overlay,
            $x, $y, 0, 0,
            $overlayWidth, $overlayHeight,
            imagesx($overlay), imagesy($overlay)
        );

        imagedestroy($overlay);

        return $background;
    }

    /**
     * Helper method to generate base64 image output
     */
    private function getImageAsBase64(bool $uri = true, ?ColorInterface $overlayColor = null): string
    {
        $image = $this->createCenteredImage($overlayColor);

        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);

        return $uri ? 'data:image/png;base64,' . base64_encode($data) : $data;
    }

    /**
     * Tints an image with a specific color while preserving transparency
     */
    private function recolorImage(\GdImage $image, ColorInterface $color): \GdImage
    {
        [$width, $height] = [imagesx($image), imagesy($image)];

        $tinted = imagecreatetruecolor($width, $height);

        imagealphablending($tinted, false);
        imagesavealpha($tinted, true);

        $newColorIndex = imagecolorallocatealpha(
            $tinted,
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue(),
            $color->getAlpha()
        );

        $transparentColor = imagecolorallocatealpha($tinted, 0, 0, 0, 127);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $pixelIndex = imagecolorat($image, $x, $y);
                $pixelColor = imagecolorsforindex($image, $pixelIndex);

                if ($pixelColor['alpha'] < 127) {
                    imagesetpixel($tinted, $x, $y, $newColorIndex);
                } else {
                    imagesetpixel($tinted, $x, $y, $transparentColor);
                }
            }
        }

        return $tinted;
    }

    /**
     * Creates an image resource from a file path
     */
    private function createImageResource(string $path): \GdImage
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $image = imagecreatefrompng($path);

        if (!$image) {
            throw new \Exception("Failed to load image from: {$path}");
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Validates the paths for background and overlay
     *
     */
    private function validatePaths(): void
    {
        if (!file_exists($this->backgroundPath) || !file_exists($this->overlayPath)) {
            throw new \Exception('Invalid file paths for background or overlay images.');
        }
    }
}
