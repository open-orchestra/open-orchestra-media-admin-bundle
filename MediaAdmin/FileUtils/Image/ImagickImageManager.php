<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use Imagick;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory;

/**
 * Class ImagickImageManager
 */
class ImagickImageManager implements ImageManagerInterface
{
    protected $imagickFactory;

    /**
     * @param ImagickFactory $imagickFactory
     */
    public function __construct(ImagickFactory $imagickFactory)
    {
        $this->imagickFactory = $imagickFactory;
    }

    /**
     * @param string $filePath
     * @param array  $format
     *
     * @return string
     */
    public function generateAlternative($filePath, array $format)
    {
        $image = $this->imagickFactory->create($filePath);
        $image = $this->resizeImage($format, $image);

        $pathInfo = pathinfo($filePath);
        $alternativePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . time() . $pathInfo['basename'];
        $this->saveImage($alternativePath, $image, $format['compression_quality']);

        return $alternativePath;
    }

    /**
     * Resize an image keeping its ratio
     *
     * @param array   $format
     * @param Imagick $image
     *
     * @return Imagick
     */
    protected function resizeImage(array $format, Imagick $image)
    {
        $maxWidth = array_key_exists('max_width', $format)? $format['max_width']: -1;
        $maxHeight = array_key_exists('max_height', $format)? $format['max_height']: -1;

        if (-2 != $maxWidth + $maxHeight) {
            $image->setimagebackgroundcolor('#000000');
            $refRatio = $maxWidth / $maxHeight;
            $imageRatio = $image->getImageWidth() / $image->getImageHeight();

            $proportional = 0;
            if ($imageRatio < 1) {
                $proportional = 1;
            }

            if ($refRatio > $imageRatio || $maxWidth == -1) {
                $image = $this->resize($image, $proportional, $maxHeight);
            } else {
                $image = $this->resize($image, $maxWidth, $proportional);
            }
        }

        return $image;
    }

    /**
     * Resize an image keeping
     *
     * @param Imagick $image
     * @param int     $height
     * @param int     $width
     *
     * @return Imagick
     */
    protected function resize(Imagick $image, $width, $height)
    {
        $image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

        return $image;
    }

    /**
     * Compress and save $image
     *
     * @param string  $filePath
     * @param Imagick $image
     * @param int     $compression_quality
     *
     * @return Imagick
     */
    protected function saveImage($filePath, Imagick $image, $compression_quality)
    {
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($compression_quality);
        $image->stripImage();
        $image->writeImage($filePath);

        return $image;
    }

    /**
     * Extract an image from the $page of $filePath
     *
     * @param string $filePath
     * @param int    $page
     *
     * @return string
     */
    public function extractImageFromPdf($filePath, $page = 0)
    {
        $pathInfo = pathinfo($filePath);
        $extractedImagePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR
            . time() . $pathInfo['basename'] . '-' . $page . '.jpg';

        $image = $this->imagickFactory->create($filePath);
        $image->setIteratorIndex($page);
        $image->writeImage($extractedImagePath);

        return $extractedImagePath;
    }

    /**
     * Crop $filePath with ($x, $y, $h, $w) and resize it to the $formatName
     *
     * @param string $filePath
     * @param int    $x
     * @param int    $y
     * @param int    $h
     * @param int    $w
     * @param array  $format
     *
     * @return string
     */
    public function cropAndResize($filePath, $x, $y, $h, $w, array $format)
    {
        $image = $this->imagickFactory->create($filePath);
        $image->cropImage($w, $h, $x, $y);
        $image = $this->resizeImage($format, $image);

        $pathInfo = pathinfo($filePath);
        $croppedFilePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . time() . $pathInfo['basename'];

        $this->saveImage($croppedFilePath, $image, $format['compression_quality']);

        return $croppedFilePath;
    }
}
