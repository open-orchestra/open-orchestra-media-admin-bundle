<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use Imagick;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ImagickImageManager
 */
class ImagickImageManager implements ImageManagerInterface
{
    protected $formats;
    protected $imagickFactory;

    /**
     * @param array                    $formats
     * @param ImagickFactory           $imagickFactory
     */
    public function __construct(
        array $formats,
        ImagickFactory $imagickFactory
    ) {
        $this->formats = $formats;
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

        if ($maxWidth + $maxHeight != -2) {
            $image->setimagebackgroundcolor('#000000');
            $refRatio = $maxWidth / $maxHeight;
            $imageRatio = $image->getImageWidth() / $image->getImageHeight();

            if ($refRatio > $imageRatio || $maxWidth == -1) {
                $image = $this->resizeOnHeight($image, $maxHeight);
            } else {
                $image = $this->resizeOnWidth($image, $maxWidth);
            }
        }

        return $image;
    }

    /**
     * Resize an image keeping its ratio to the height $height
     * 
     * @param Imagick $image
     * @param int     $height
     * 
     * @return Imagick
     */
    protected function resizeOnHeight(Imagick $image, $height)
    {
        $image->resizeImage(0, $height, Imagick::FILTER_LANCZOS, 1);

        return $image;
    }

    /**
     * Resize an image keeping its ratio to the width $width
     * 
     * @param Imagick $image
     * @param int     $width
     * 
     * @return Imagick
     */
    protected function resizeOnWidth(Imagick $image, $width)
    {
        $image->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);

        return $image;
    }

    /**
     * Compress and save $image
     * 
     * @param MediaInterface $media
     * @param Imagick        $image
     * @param int            $compression_quality
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
     * @param $filePath
     * @param $x
     * @param $y
     * @param $h
     * @param $w
     * @param $formatName
     */
    public function cropAndResize($filePath, $x, $y, $h, $w, $formatName)
    {
        $image = $this->imagickFactory->create($filePath);
        $image->cropImage($w, $h, $x, $y);
        $image = $this->resizeImage($this->formats[$formatName], $image);

        $pathInfo = pathinfo($filePath);
        $croppedFilePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . time() . $pathInfo['basename'];

        $this->saveImage($croppedFilePath, $image, $this->formats[$formatName]['compression_quality']);

        return $croppedFilePath;
    }
}
