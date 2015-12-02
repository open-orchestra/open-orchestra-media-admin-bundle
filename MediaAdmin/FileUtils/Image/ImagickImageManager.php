<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use Imagick;
use OpenOrchestra\MediaAdmin\Event\ImagickEvent;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ImagickImageManager
 */
class ImagickImageManager implements ImageManagerInterface
{
    protected $compressionQuality;
    protected $tmpDir;
    protected $formats;
    protected $imagickFactory;

    /**
     * @param string                   $tmpDir
     * @param array                    $formats
     * @param int                      $compressionQuality
     * @param EventDispatcherInterface $dispatcher
     * @param ImagickFactory           $imagickFactory
     */
    public function __construct(
        $tmpDir,
        array $formats,
        $compressionQuality,
        $dispatcher,
        ImagickFactory $imagickFactory
    ) {
        $this->compressionQuality = $compressionQuality;
        $this->tmpDir = $tmpDir;
        $this->formats = $formats;
        $this->imagickFactory = $imagickFactory;
    }

    /**
     * @param MediaInterface $media
     * @param int            $x
     * @param int            $y
     * @param int            $h
     * @param int            $w
     * @param string         $format
     */
    public function crop(MediaInterface $media, $x, $y, $h, $w, $format)
    {
        $image = $this->imagickFactory->create($this->tmpDir . '/' . $media->getFilesystemName());
        $image->cropImage($w, $h, $x, $y);
        $this->resizeImage($this->formats[$format], $image);

        $this->saveImage($media, $image, $format);
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     */
    public function override(MediaInterface $media, $format)
    {
        $filename = $format . '-' . $media->getFilesystemName();
        $filePath = $this->tmpDir . '/' . $filename;
        $this->resizeAndSaveImage($media, $format, $filePath);
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param string         $filePath
     */
    public function resizeAndSaveImage(MediaInterface $media, $format, $filePath)
    {
        $image = $this->imagickFactory->create($filePath);
        $this->resizeImage($this->formats[$format], $image);

        $this->saveImage($media, $image, $format);
    }




////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $filePath
     * @param array  $format
     */
    public function generateAlternative($filePath, $format)
    {
        $image = $this->imagickFactory->create($filePath);
        $image = $this->resizeImage($format, $image);

        $pathInfo = pathinfo($filePath);
        $alternativePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . time() . $pathInfo['basename'];
        $this->saveImage($alternativePath, $image);

        return $alternativePath;
    }

    /**
     * Resize an image keeping its ratio
     *
     * @param array   $format
     * @param Imagick $image
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
     * @param MediaInterface $media
     * @param Imagick        $image
     * 
     * @return Imagick
     */
    protected function saveImage($filePath, Imagick $image)
    {
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($this->compressionQuality);
        $image->stripImage();
        $image->writeImage($filePath);

        return $image;
    }
}